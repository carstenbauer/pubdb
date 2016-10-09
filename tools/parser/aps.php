<?php
# Authors: Carsten Bauer
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
            case "prl": $journal_str = "PhysRevLett"; break;
            case "rmp": $journal_str = "RevModPhys"; break;
        } 

        return $journal_str.".".$numbers[0].".".$numbers[1];
    }


    private static function extractJournal($apsStr){
        if (strpos($apsStr, 'B') !== False || strpos($apsStr, 'prb') !== False){
            return "prb";
        } elseif (strpos($apsStr, 'Lett') !== False || strpos($apsStr, 'L') !== False || strpos($apsStr, 'prl') !== False) {
            return "prl";
        } elseif (strpos($apsStr, 'A') !== False || strpos($apsStr, 'pra') !== False){
            return "pra";
        } elseif (strpos($apsStr, 'D') !== False || strpos($apsStr, 'prd') !== False){
            return "prd";
        } elseif (strpos($apsStr, 'E') !== False || strpos($apsStr, 'pre') !== False){
            return "pre";
        } elseif (strpos($apsStr, 'C') !== False || strpos($apsStr, 'prc') !== False){
            return "pre";
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

        $bibtex = file_get_contents($bibtex_url);
        
        $listener = new RenanBr\BibTexParser\Listener;
        $parser = new RenanBr\BibTexParser\Parser;
        $parser->addListener($listener);
        // $parser->parseFile('physrev.bib');
        $parser->parseString($bibtex);
        $entries = $listener->export();

        $paper["title"] = $entries[0]["title"];
        $paper["journal"] = $journal;
        // Change "Name, Prename" to "Prename Name"
        $authors = explode(" and ",$entries[0]["author"]);
        $paper["authors"] = array();
        foreach($authors as $author){
            $name = explode(", ",$author);
            array_push($paper["authors"],$name[1]." ".$name[0]);
        }
        $paper["year"] = $entries[0]["year"];
        $paper["url"] = str_replace("export","abstract",$bibtex_url);
        $paper["bibtex"] = $bibtex;
        $paper["identifier"] = $id;
        // TODO: add time information to "timestamp".
        // construct from month and year field of bibtex

        return $paper;
    }
}

?>
