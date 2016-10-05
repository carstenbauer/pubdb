<?php declare (strict_types = 1);

/*
 * This file is part of the BibTex Parser.
 *
 * (c) Renan de Lima Barbosa <renandelima@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RenanBr\BibTexParser;

interface ListenerInterface
{
    public function bibTexUnitFound(string $text, array $context);
}
