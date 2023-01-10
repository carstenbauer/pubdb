<?php
# Authors: Carsten Bauer
include_once('tools/generic_functions.php');
include_once("tools/parser/BibTexParser/ListenerInterface.php");
include_once("tools/parser/BibTexParser/Listener.php");
include_once("tools/parser/BibTexParser/Parser.php");
include_once("tools/parser/BibTexParser/ParseException.php");

class apsParser {


    public static function extractNumbers($str){
        preg_match_all('!\d+!', $str, $m);
        return $m[0];
    }


    private static function extractPureID($apsStr){
        # Allow for whole aps.org URL
        if (strpos($apsStr,"aps.org") !== False || strpos($apsStr,"10.1103/") !== False) {
            $apsStr = substr(strrchr($apsStr,'/'),1);
        }


        $journal = apsParser::extractJournal($apsStr);
        $numbers = apsParser::extractNumbers($apsStr);
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
            case "prxquantum": $journal_str = "PRXQuantum"; break;
            case "prapplied": $journal_str = "PhysRevApplied"; break;
        }

        # catch Letter case
        if (strpos($apsStr, "L".$numbers[1]) !== False){
            return $journal_str.".".$numbers[0].".L".$numbers[1];
        }

        return $journal_str.".".$numbers[0].".".$numbers[1];
    }


    private static function extractJournal($apsStr){
        if (strpos($apsStr, 'B') !== False || strpos($apsStr, 'prb') !== False){
            return "prb";
        } elseif (strpos($apsStr, 'Lett') !== False || strpos($apsStr, 'prl') !== False) {
            return "prl";
        } elseif (stripos($apsStr, 'App') !== False) {
            return "prapplied";
        } elseif (strpos($apsStr, 'A') !== False || strpos($apsStr, 'pra') !== False){
            return "pra";
        } elseif (strpos($apsStr, 'D') !== False || strpos($apsStr, 'prd') !== False){
            return "prd";
        } elseif (strpos($apsStr, 'E') !== False || strpos($apsStr, 'pre') !== False){
            return "pre";
        } elseif (strpos($apsStr, 'C') !== False || strpos($apsStr, 'prc') !== False){
            return "prc";
        } elseif (strpos($apsStr, 'X') !== False || strpos($apsStr, 'prx') !== False){
            # distinguish between prx and prx quantum
            if (strpos($apsStr, 'Q') !== False){
                return "prxquantum";
            } else {
                return "prx";
            }
        } elseif (strpos($apsStr, 'Research') !== False || strpos($apsStr, 'prresearch') !== False){
            return "prresearch";
        } elseif (strpos($apsStr, 'Materials') !== False || strpos($apsStr, 'prmaterials') !== False){
            return "prmaterials";
        } elseif (strpos($apsStr, 'Mod') !== False || strpos($apsStr, 'M') !== False || strpos($apsStr, 'rmp') !== False){
            return "rmp";
        } else {
            return False;
        }
    }


    public static function parse($apsStr){
        // TODO catch ParseException

        $journal = apsParser::extractJournal($apsStr);
        $id = apsParser::extractPureID($apsStr);
        
        # Get url to bibtex
        $bibtex_url = "http://journals.aps.org/".$journal."/export/10.1103/".$id;
        $abstract_url = "http://journals.aps.org/".$journal."/abstract/10.1103/".$id;

        // set the User-Agent in the HTTP request
        $context_chrome = stream_context_create(
            array(
                "http" => array(
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                )
            )
        );
        $bibtex = file_get_contents($bibtex_url, false, $context_chrome);
        $abstractpage = file_get_contents($abstract_url, false, $context_chrome);
        if (strpos($abstractpage, 'Rapid Communication') !== false) {
            $id = $id."(R)";
        }
        if (strpos($abstractpage, 'Editors&#39; Suggestion') !== false) {
            $id = $id."(E)";
        }
        $listener = new RenanBr\BibTexParser\Listener;
        $parser = new RenanBr\BibTexParser\Parser;        
        $parser->addListener($listener);

        // $parser->parseFile('physrev.bib');
        $parser->parseString($bibtex);
        $entries = $listener->export();

        $paper["title"] = handleBibTeXSpecialSymbols($entries[0]["title"]);
        $paper["journal"] = $journal;
        $paper["volume"] = $entries[0]["volume"];
        $paper["number"] = $entries[0]["pages"];

        // Change "Name, Prename" to "Prename Name"
        $bibtexauthorstring = $entries[0]["author"];
        $authors = explode(" and ",handleBibTeXSpecialSymbols($bibtexauthorstring));
        $paper["authors"] = array();

        foreach($authors as $author){
            $name = explode(", ",$author);
            array_push($paper["authors"],$name[1]." ".$name[0]);
        }
        
        $paper["year"] = $entries[0]["year"];
        $paper["month"] = monthStrToInt($entries[0]["month"]);
        $paper["url"] = str_replace("export","abstract",$bibtex_url);
        $paper["bibtex"] = $bibtex;
        $paper["identifier"] = $id;

        return $paper;
    }
}

?>
