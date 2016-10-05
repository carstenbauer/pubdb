<?php 

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser;

class Listener implements ListenerInterface
{
    /**
     * @var array
     */
    private $entries = [];

    /**
     * Current key name.
     * Indicates where to save values.
     *
     * @var string
     */
    private $key;

    /**
     * @var bool
     */
    private $processed = false;

    public function export()
    {
        if (!$this->processed) {
            $this->processCitationKey();
            $this->processed = true;
        }
        return $this->entries;
    }

    public function bibTexUnitFound(string $text, array $context)
    {
        switch ($context['state']) {
            case Parser::TYPE:
                $this->entries[] = ['type' => $text];
                break;

            case PARSER::KEY:
                // save key into last entry
                end($this->entries);
                $position = key($this->entries);
                $this->key = $text;
                $this->entries[$position][$this->key] = null;
                break;

            case PARSER::RAW_VALUE:
                $text = $this->processRawValue($text);
                // break;

            case PARSER::BRACED_VALUE:
            case PARSER::QUOTED_VALUE:
                if (null !== $text) {
                    // append value into current key of last entry
                    end($this->entries);
                    $position = key($this->entries);
                    $this->entries[$position][$this->key] .= $text;
                }
        }
    }

    private function processCitationKey()
    {
        foreach ($this->entries as $position => $entry) {
            // the first key is always the "type"
            // the second key MAY be actually a "citation-key" value, but only if its value is null
            if (count($entry) > 1) {
                $second = array_slice($entry, 1, 1, true);
                list($key, $value) = each($second);
                if (null === $value) {
                    // once the second key value is empty, it flips the key name
                    // as value of "citation-key"
                    $this->entries[$position]['citation-key'] = $key;
                    unset($this->entries[$position][$key]);
                }
            }
        }
    }

    private function processRawValue(string $value)
    {
        // find for an abbreviation
        foreach ($this->entries as $entry) {
            if ('string' == $entry['type'] && array_key_exists($value, $entry)) {
                return $entry[$value];
            }
        }
        return $value;
    }
}
