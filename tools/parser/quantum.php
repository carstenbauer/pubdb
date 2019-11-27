<?php
# Authors: Carsten Bauer
include_once("tools/parser/BibTexParser/ListenerInterface.php");
include_once("tools/parser/BibTexParser/Listener.php");
include_once("tools/parser/BibTexParser/Parser.php");
include_once("tools/parser/BibTexParser/ParseException.php");

class quantumParser {


    public static function extractNumbers($str){
        preg_match_all('!\d+!', $str, $m);
        return $m[0];
    }


    private static function extractPureID($quantumStr){
        # Allow for whole quantum-journal.org URL
        if (strpos($quantumStr,"quantum-journal.org") !== False || strpos($quantumStr,"10.1103/") !== False) {
            $quantumStr = substr(strrchr($quantumStr,'/'),1);
        }


        $journal = quantumParser::extractJournal($quantumStr);
        $numbers = quantumParser::extractNumbers($quantumStr);
        if (count($numbers)<2 || $journal===False){
            return False;
        }

        $journal_str = '';
        switch ($journal) {
            case "pra": $journal_str = "PhysRevA"; break;
            case "prb": $journal_str = "PhysRevB"; break;
            case "prc": $journal_str = "PhysRevC"; break;
            case "prd": $journal_str = "PhysRevD"; break;
            case "pre": $journal_str = "PhysRevE"; break;
            case "prx": $journal_str = "PhysRevX"; break;
            case "prresearch": $journal_str = "PhysRevResearch"; break;
            case "prl": $journal_str = "PhysRevLett"; break;
            case "prmaterials": $journal_str = "PhysRevMaterials"; break;
            case "rmp": $journal_str = "RevModPhys"; break;
        } 

        return $journal_str.".".$numbers[0].".".$numbers[1];
    }


    private static function extractJournal($quantumStr){
        if (strpos($quantumStr, 'B') !== False || strpos($quantumStr, 'prb') !== False){
            return "prb";
        } elseif (strpos($quantumStr, 'Lett') !== False || strpos($quantumStr, 'L') !== False || strpos($quantumStr, 'prl') !== False) {
            return "prl";
        } elseif (strpos($quantumStr, 'A') !== False || strpos($quantumStr, 'pra') !== False){
            return "pra";
        } elseif (strpos($quantumStr, 'D') !== False || strpos($quantumStr, 'prd') !== False){
            return "prd";
        } elseif (strpos($quantumStr, 'E') !== False || strpos($quantumStr, 'pre') !== False){
            return "pre";
        } elseif (strpos($quantumStr, 'C') !== False || strpos($quantumStr, 'prc') !== False){
            return "prc";
        } elseif (strpos($quantumStr, 'X') !== False || strpos($quantumStr, 'prx') !== False){
            return "prx";
        } elseif (strpos($quantumStr, 'Research') !== False || strpos($quantumStr, 'prresearch') !== False){
            return "prresearch";
        } elseif (strpos($quantumStr, 'Materials') !== False || strpos($quantumStr, 'prmaterials') !== False){
            return "prmaterials";
        } elseif (strpos($quantumStr, 'Mod') !== False || strpos($quantumStr, 'M') !== False || strpos($quantumStr, 'rmp') !== False){
            return "rmp";
        } else {
            return False;
        }
    }

    private static function handleBibTeXSpecialSymbols($bibtexstr){
        $str = str_replace("\\\"a", "ä", $bibtexstr);
        $str = str_replace("\\ifmmode \\check{c}\\else \\v{c}\\fi{}", "\\v{c}", $str);
        $str = str_replace("\\ifmmode \\check{C}\\else \\v{C}\\fi{}", "\\v{C}", $str);
        $str = str_replace("\\ifmmode \\check{s}\\else \\v{s}\\fi{}", "\\v{s}", $str);
        $str = str_replace("\\ifmmode \\check{S}\\else \\v{S}\\fi{}", "\\v{S}", $str);
        $str = str_replace("\\\"A", "Ä", $str);
        $str = str_replace("\\\"o", "ö", $str);
        $str = str_replace("\\\"u", "ü", $str);
        $str = str_replace("\\\"O", "Ö", $str);
        $str = str_replace("\\\"U", "Ü", $str);
        $str = str_replace("\\ss", "ß", $str);
        $str = str_replace("\\^e", "ê", $str);
        $str = str_replace("\\'e", "é", $str);
        $str = str_replace("\\`e", "è", $str);
        $str = str_replace("\\\"e", "ë", $str);
        $str = str_replace("\\`i", "ì", $str);
        $str = str_replace("\\o{}", "ø", $str);
        $str = str_replace("\\o", "ø", $str);
        $str = str_replace("\\'u", "ú", $str);
        $str = str_replace("\\aa", "å", $str);
        $str = str_replace("\\c", "ç", $str);
        $str = str_replace("\\~n", "ñ", $str);
        $str = str_replace("\\v{c}", "č", $str);
        $str = str_replace("\\v{C}", "Č", $str);
        $str = str_replace("\\v{s}", "š", $str);
        $str = str_replace("\\v{S}", "Š", $str);
        $str = str_replace("\\'{\\i}", "í", $str);
        $str = str_replace("\\'y", "ý", $str);
        $str = str_replace("\\'o", "ó", $str);
        $str = str_replace("\\'a", "á", $str);
        $str = str_replace("\\ensuremath{-}", "-", $str);
        $str = str_replace("{Quantum}", "Quantum", $str);
        $str = str_replace("{A}", "A", $str);
        $str = str_replace("{B}", "B", $str);
        $str = str_replace("{C}", "C", $str);
        $str = str_replace("{D}", "D", $str);
        $str = str_replace("{E}", "E", $str);
        $str = str_replace("{F}", "F", $str);
        $str = str_replace("{G}", "G", $str);
        $str = str_replace("{H}", "H", $str);
        $str = str_replace("{I}", "I", $str);
        $str = str_replace("{J}", "J", $str);
        $str = str_replace("{K}", "K", $str);
        $str = str_replace("{L}", "L", $str);
        $str = str_replace("{M}", "M", $str);
        $str = str_replace("{N}", "N", $str);
        $str = str_replace("{O}", "O", $str);
        $str = str_replace("{P}", "P", $str);
        $str = str_replace("{Q}", "Q", $str);
        $str = str_replace("{R}", "R", $str);
        $str = str_replace("{S}", "S", $str);
        $str = str_replace("{T}", "T", $str);
        $str = str_replace("{U}", "U", $str);
        $str = str_replace("{V}", "V", $str);
        $str = str_replace("{W}", "W", $str);
        $str = str_replace("{X}", "X", $str);
        $str = str_replace("{Y}", "Y", $str);
        $str = str_replace("{Z}", "Z", $str);

        return $str;
    }

    private static function monthStrToInt($monthstr){
        switch ($monthstr) {
            case 'January':
            case 'Januar':
            case 'Jan':
                return 1;

            case 'February':
            case 'Februar':
            case 'Feb':
                return 2;

            case 'March':
            case 'März':
            case 'Mar':
                return 3;

            case 'April':
            case 'April':
            case 'Apr':
                return 4;

            case 'May':
            case 'Mai':
            case 'May':
                return 5;

            case 'June':
            case 'Juni':
            case 'Jun':
                return 6;

            case 'July':
            case 'Juli':
            case 'Jul':
                return 7;

            case 'August':
            case 'August':
            case 'Aug':
                return 8;

            case 'September':
            case 'September':
            case 'Sep':
                return 9;

            case 'October':
            case 'Oktober':
            case 'Oct':
                return 10;

            case 'November':
            case 'November':
            case 'Nov':
                return 11;

            case 'Dezember':
            case 'Dezember':
            case 'Dez':
                return 12;
            
            default:
                return 13;
        }
    }

    public static function startsWith($haystack, $needle)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }

    public static function prependHTTP($str)
    {
        if(quantumParser::startsWith($str, "http://") == false && quantumParser::startsWith($str, "https://") == false){
            return "https://".$str;
        }
        return $str;
    }


    public static function parse($quantumStr){
        // TODO catch ParseException

      
        # Get bibtex
        //Load the HTML page
        $quantumURL = quantumParser::prependHTTP($quantumStr);
        // print($quantumURL);
        $html = file_get_contents($quantumURL);
        //Create a new DOM document
        $dom = new DOMDocument;
         
        //Parse the HTML. The @ is used to suppress any parsing errors
        //that will be thrown if the $html string isn't valid XHTML.
        @$dom->loadHTML($html);
         
        $finder = new DomXPath($dom);
        $classname="bibtex";
        $nodes = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        $bibtex = $nodes[0]->nodeValue;
        
        $listener = new RenanBr\BibTexParser\Listener;
        $parser = new RenanBr\BibTexParser\Parser;
        $parser->addListener($listener);
        $parser->parseString($bibtex);
        $entries = $listener->export();

        $paper["title"] = quantumParser::handleBibTeXSpecialSymbols($entries[0]["title"]);

        // $paper["journal"] = quantumParser::handleBibTeXSpecialSymbols($entries[0]["journal"]);
        $paper["journal"] = "Quantum";
        $paper["volume"] = $entries[0]["volume"];
        $paper["number"] = $entries[0]["pages"];
        // Change "Name, Prename" to "Prename Name"
        $bibtexauthorstring = $entries[0]["author"];
        $authors = explode(" and ",quantumParser::handleBibTeXSpecialSymbols($bibtexauthorstring));
        $paper["authors"] = array();
        foreach($authors as $author){
            $name = explode(", ",$author);
            array_push($paper["authors"],$name[1]." ".$name[0]);
        }
        $paper["year"] = $entries[0]["year"];
        $paper["month"] = quantumParser::monthStrToInt($entries[0]["month"]);
        $paper["url"] = $quantumURL;
        $paper["bibtex"] = $bibtex;
        $paper["identifier"] = $quantumURL;

        // print_r($paper);
        return $paper;
    }
}

?>
