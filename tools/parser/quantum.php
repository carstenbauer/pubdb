<?php
# Authors: Carsten Bauer
include_once('tools/generic_functions.php');
include_once("tools/parser/BibTexParser/ListenerInterface.php");
include_once("tools/parser/BibTexParser/Listener.php");
include_once("tools/parser/BibTexParser/Parser.php");
include_once("tools/parser/BibTexParser/ParseException.php");

class quantumParser {


    private static function extractNumbers($str){
        preg_match_all('!\d+!', $str, $m);
        return $m[0];
    }

    private static function getURLfromID($doi){
        $url = "https://quantum-journal.org/papers/".$doi;
        return $url;
    }

    private static function getID($str){

        # allow for URL
        if (stripos($str, "quantum-journal.org/papers/") !== False){
            $str = substr(strrchr($str,'papers/'),4);
            $str = str_replace("/", "", $str);
            return $str;
        }
        
        # allow for DOI
        if (stripos($str, "10.22331") !== False){
            $str = substr(strrchr($str,'/'),1);
            $str = str_replace("/", "", $str);
            return $str;
        }

        # allow for reference string
        // TODO: implement this
        if (strpos($str, "Quantum") !== False){

            # extract volume, page number (and year)
            $numbers = quantumParser::extractNumbers($str);

            # search for the paper
            $url = "https://quantum-journal.org/volumes/".$numbers[0]."/";
            $html = file_get_contents($url);
            $dom = new DOMDocument;
            $dom->loadHTML($html);
            $xpath = new DOMXPath($dom);

            $str = "Quantum ".$numbers[0].", ".$numbers[1];
            $href = $xpath->evaluate('//a[contains(text(),"'.$str.'")]//@href')[0]->textContent.PHP_EOL;
            $href = substr(strrchr($href,'/'),1);
            $href = str_replace("/", "", $href);
            $href = trim($href);

            return $href;
        }

        return False;
    }

    public static function parse($quantumStr){
        // TODO catch ParseException

        # Get ID
        $quantumID = quantumParser::getID($quantumStr);

        # Get URL
        $quantumURL = quantumParser::getURLfromID($quantumID);
      
        # Get bibtex
        //Load the HTML page
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

        $paper["title"] = handleBibTeXSpecialSymbols($entries[0]["title"]);

        // $paper["journal"] = handleBibTeXSpecialSymbols($entries[0]["journal"]);
        $paper["journal"] = "Quantum";
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
        $paper["url"] = $quantumURL;
        $paper["bibtex"] = $bibtex;
        $paper["identifier"] = $quantumURL;

        // print_r($paper);
        return $paper;
    }
}

?>
