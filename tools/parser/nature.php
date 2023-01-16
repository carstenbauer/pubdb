<?php
# Authors: Carsten Bauer
include_once('tools/generic_functions.php');
include_once("tools/parser/BibTexParser/ListenerInterface.php");
include_once("tools/parser/BibTexParser/Listener.php");
include_once("tools/parser/BibTexParser/Parser.php");
include_once("tools/parser/BibTexParser/ParseException.php");


class natureParser {
    
    
    private static function contains($haystack, $needle){
        if(strpos($haystack, $needle) !== False)
            return True;
        else
            return False;
    }

    private static function extractNumbers($str){
        preg_match_all('!\d+!', $str, $m);
        return $m[0];
    }


    # this function returns the id of the first result of nature.com advanced search
    private static function getIdFromAdvancedSearch($volumeNum, $pageNum, $journalStr){

        # get the advanced search URL
        $url = "https://www.nature.com/search?volume=".$volumeNum."&spage=".$pageNum."&order=relevance&journal=".$journalStr;

        # get the html code of the advanced search page
        $html = file_get_contents($url);
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $xpath = new DOMXPath($dom);

        # extract the id of the first result
        $id = $xpath->evaluate('//a[@class="c-card__link u-link-inherit"][1]/@href')[0]->textContent.PHP_EOL;
        $id = substr(strrchr($id,'/'),1); 
        $id = trim($id);

        return $id;
    }

    # this function extracts the journal from a reference string
    private static function extractJournal($str){
        if ((stripos($str, "Comp") !== False) && (stripos($str, "Mat") !== False)) {     # assume Nature Computational Materials
            return "npjcompumats";
        }


        if (stripos($str, "Mat") !== False) {      # assume Nature Reviews Materials
            return "natrevmats";
        }
        if (stripos($str, "Rev") !== False) {      # assume Nature Reviews Physics
            return "natrevphys";
        }
        if ((stripos($str, "Phy") !== False) && (stripos($str, "Com") !== False)) {     # assume (Nature) Communications Physics
            return "commsphys";
        }
        if (stripos($str, "Com") !== False) {      # assume Nature Communications
            return "ncomms";
        }
        if (stripos($str, "Phy") !== False) {      # assume Nature Physics
            return "nphys";
        }
        if ((stripos($str, "Sci") !== False) && (stripos($str, "Rep") !== False)) {     # assume (Nature) Scientific Reports
            return "srep";
        }

        # assume Nature
        return "nature";
    }


    private static function extractPureID($str){
        
        ### Allow for whole nature.com URL
        if (strpos($str, "nature.com") !== False) {
            $str = substr(strrchr($str,'/'),1); 
            return str_replace(".html","",$str);
        }

        ### Allow for DOI
        if (strpos($str, "10.1038/") !== False) {
            $str = substr(strrchr($str,'/'),1);
            return $str;
        }

        ### Allow for reference string

        # extract journal
        $journalStr = natureParser::extractJournal($str);

        # extract volume and page
        $numbers = natureParser::extractNumbers($str);

        # get id from advanced search
        $id = natureParser::getIdFromAdvancedSearch($numbers[0], $numbers[1], $journalStr);

        return $id;
    }

    

    private static function RIStoBibTeX($id){

        # Note: This function is technically not using RIS files anymore even though its still called RIStoBibTeX

        $bibtex_url = "https://citation-needed.springer.com/v2/references/10.1038/" . $id . "?format=bibtex&flavour=citation";
        $bibtex = file_get_contents($bibtex_url);

        # Get rid of strange characters at the beginning (before @)!?! 
        # update 2022/10: not needed anymore (?)
        //$bib = strstr($bib, "@");
        
        $bibtex = handleSpecialChars($bibtex); 

        return $bibtex;
    }

    
    public static function parse($natureStr){
        # check if URL otherwise return false and inform the user.
        # https://doi.org/10.1038/id
        # https://www.nature.com/articles/id

        $id = natureParser::extractPureID($natureStr);

        # Check if valid ID (journalNUMBER format) with regexp
        if ($id==False) return False;
        
        $paper = array();
        $bibtex = natureParser::RIStoBibTeX($id);

        $listener = new RenanBr\BibTexParser\Listener;
        $parser = new RenanBr\BibTexParser\Parser;
        $parser->addListener($listener);

        try {
            $parser->parseString($bibtex);
        } catch(Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            return False;
        }

        $entries = $listener->export();
        
        $paper["title"] = inverseHandleSpecialChars(handleBibTeXSpecialSymbols($entries[0]["title"]));
        $paper["journal"] = $entries[0]["journal"];
        $paper["volume"] = $entries[0]["volume"];
        $paper["number"] = $entries[0]["pages"];

        // Change "Name, Prename" to "Prename Name"
        $bibtexauthorstring = inverseHandleSpecialChars($entries[0]["author"]);
        $bibtexauthorstring = str_replace('{', '', $bibtexauthorstring);
        $bibtexauthorstring = str_replace('}', '', $bibtexauthorstring);
        $authors = explode("\nand ",handleBibTeXSpecialSymbols($bibtexauthorstring));
        $paper["authors"] = array();
        foreach($authors as $author){
            $name = explode(", ",$author);
            array_push($paper["authors"],$name[1]." ".$name[0]);
        }
        $paper["year"] = $entries[0]["year"];
        $paper["month"] = monthStrToInt($entries[0]["month"]);
        $paper["url"] = "https://www.nature.com/articles/".$id;

        $bibtex = inverseHandleSpecialChars($bibtex);
        
        $paper["bibtex"] = $bibtex;
        
        $paper["identifier"] = $id;

        return $paper;
    }

}
?>
