<?php
# Authors: Carsten Bauer
include_once('tools/generic_functions.php');
include_once("tools/parser/BibTexParser/ListenerInterface.php");
include_once("tools/parser/BibTexParser/Listener.php");
include_once("tools/parser/BibTexParser/Parser.php");
include_once("tools/parser/BibTexParser/ParseException.php");

class scipostParser {


    public static function extractNumbers($str){
        preg_match_all('!\d+!', $str, $m);
        return $m[0];
    }


    private static function getID($scipostStr){
        # Allow for whole scipost.org URL or DOI
        if (strpos($scipostStr,"scipost.org") !== False || strpos($scipostStr,"10.21468/") !== False) {
            $scipostStr = substr(strrchr($scipostStr,'/'),1);
            $journal = scipostParser::extractJournal($scipostStr);
            $numbers = scipostParser::extractNumbers($scipostStr);
            if (count($numbers)<3 || $journal===False){
                if ($journal === "SciPostPhysCodeb") {
                    return "SciPostPhysCodeb.".$numbers[0];
                }
                return False;
            }
            return $journal.".".$numbers[0].".".$numbers[1].".".$numbers[2];
        }


        # allow for reference string
        $journal = scipostParser::extractJournal($scipostStr);
        $numbers = scipostParser::extractNumbers($scipostStr);

        # get ID from api
        $apiURL = "https://scipost.org/api/search/publications/?search=".$journal.".".$numbers[0].".+.".$numbers[1];
        $str = file_get_contents($apiURL);
        $pos = strpos($str, "doi_label");
        $str = substr($str, $pos + 12);
        $pos = strpos($str, '"');
        return substr($str, 0, $pos);
    }


    private static function extractJournal($scipostStr){

        if (stripos($scipostStr,"Code") !== False) {
            return "SciPostPhysCodeb";
        } else
        if (stripos($scipostStr,"core") !== False) {
            return "SciPostPhysCore";
        } else {
            return "SciPostPhys";
        }
    }



    public static function startsWith($haystack, $needle)
    {
         $length = strlen($needle);
         return (substr($haystack, 0, $length) === $needle);
    }


    public static function str_replace_first($from, $to, $content)
    {
        $from = '/'.preg_quote($from, '/').'/';

        return preg_replace($from, $to, $content, 1);
    }


    public static function parse($scipostStr){
        // TODO catch ParseException

        $scipostid = scipostParser::getID($scipostStr);

        if($scipostid == False){
            return False;
        }


        # Get bibtex
        //Load the HTML page
        // $scipostURL = scipostParser::prependHTTP($scipostStr);
        $scipostURL = "https://scipost.org/".$scipostid;


        // print($scipostURL);
        $html = file_get_contents($scipostURL);
        //Create a new DOM document
        $dom = new DOMDocument;
         
        //Parse the HTML. The @ is used to suppress any parsing errors
        //that will be thrown if the $html string isn't valid XHTML.
        @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
         
        $finder = new DomXPath($dom);
        $classname="bibtex";
        $nodes = $finder->query("//*[@id='bibtextmodal']");

        $bibtex = substr($nodes[0]->nodeValue,79);
        $bibtex = scipostParser::str_replace_first("10.21468/", "", $bibtex);
        $bibtex = str_replace(",}", "}", $bibtex);
        
        $listener = new RenanBr\BibTexParser\Listener;
        $parser = new RenanBr\BibTexParser\Parser;
        $parser->addListener($listener);
        $parser->parseString($bibtex);
        $entries = $listener->export();

        $title = handleBibTeXSpecialSymbols($entries[0]["title"]);
        $paper["title"] = $title;
        if(scipostParser::startsWith($title, "{") == True) {
            $title = substr(substr($title, 1), 0, -1);
            $paper["title"] = $title;
        }


        // $paper["journal"] = handleBibTeXSpecialSymbols($entries[0]["journal"]);
        $paper["journal"] = $entries[0]["journal"];
        $paper["volume"] = $entries[0]["volume"];
        $paper["number"] = $entries[0]["pages"];
        // Change "Name, Prename" to "Prename Name"
        $bibtexauthorstring = $entries[0]["author"];
        $authors = explode(" and ",handleBibTeXSpecialSymbols($bibtexauthorstring));
        // var_dump($authors);
        $paper["authors"] = $authors;
        // $paper["authors"] = array();
        // foreach($authors as $author){
        //     $name = explode(", ",$author);
        //     var_dump($name);
        //     array_push($paper["authors"],$name[1]." ".$name[0]);
        // }
        $paper["year"] = $entries[0]["year"];
        // $paper["month"] = monthStrToInt($entries[0]["month"]);
        $paper["url"] = $scipostURL;
        $paper["bibtex"] = $bibtex;
        $paper["identifier"] = $scipostURL;

        // print_r($paper);
        return $paper;
    }
}

?>
