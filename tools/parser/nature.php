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


    # this function handles special chars in the bibtex file by replacing them (to prevent errors in the bibtex parser)
    private static function handleSpecialChars($bibtex){

        $bibtex = str_replace("ä", "SpecialChar001", $bibtex);
        $bibtex = str_replace("Ä", "SpecialChar002", $bibtex);
        $bibtex = str_replace("ö", "SpecialChar003", $bibtex);
        $bibtex = str_replace("Ö", "SpecialChar004", $bibtex);
        $bibtex = str_replace("ü", "SpecialChar005", $bibtex);
        $bibtex = str_replace("Ü", "SpecialChar006", $bibtex);
        $bibtex = str_replace("ė", "SpecialChar007", $bibtex);
        $bibtex = str_replace("ñ", "SpecialChar008", $bibtex);
        $bibtex = str_replace("é", "SpecialChar009", $bibtex);
        $bibtex = str_replace("á", "SpecialChar010", $bibtex);
        $bibtex = str_replace("ý", "SpecialChar011", $bibtex);
        $bibtex = str_replace("ó", "SpecialChar012", $bibtex);
        $bibtex = str_replace("ú", "SpecialChar013", $bibtex);
        $bibtex = str_replace("ç", "SpecialChar014", $bibtex);
        $bibtex = str_replace("í", "SpecialChar015", $bibtex);
        $bibtex = str_replace("ć", "SpecialChar016", $bibtex);
        $bibtex = str_replace("è", "SpecialChar017", $bibtex);
        $bibtex = str_replace("č", "SpecialChar018", $bibtex);
        $bibtex = str_replace("Č", "SpecialChar019", $bibtex);
        $bibtex = str_replace("ž", "SpecialChar020", $bibtex);
        $bibtex = str_replace("ň", "SpecialChar021", $bibtex);
        $bibtex = str_replace("å", "SpecialChar022", $bibtex);
        $bibtex = str_replace("š", "SpecialChar023", $bibtex);
        $bibtex = str_replace("ø", "SpecialChar024", $bibtex);
        $bibtex = str_replace("ã", "SpecialChar025", $bibtex);
        $bibtex = str_replace("ř", "SpecialChar026", $bibtex);
        $bibtex = str_replace("ë", "SpecialChar027", $bibtex);
        $bibtex = str_replace("Ž", "SpecialChar028", $bibtex);
        $bibtex = str_replace("ń", "SpecialChar029", $bibtex);
        $bibtex = str_replace("Å", "SpecialChar030", $bibtex);
        $bibtex = str_replace("ą", "SpecialChar031", $bibtex);
        $bibtex = str_replace("ż", "SpecialChar032", $bibtex);
        $bibtex = str_replace("Ł", "SpecialChar033", $bibtex);
        $bibtex = str_replace("ł", "SpecialChar034", $bibtex);
        $bibtex = str_replace("ŕ", "SpecialChar035", $bibtex);
        $bibtex = str_replace("ï", "SpecialChar036", $bibtex);
        $bibtex = str_replace("Á", "SpecialChar037", $bibtex);
        $bibtex = str_replace("ę", "SpecialChar038", $bibtex);

        return $bibtex;
    }
    # this function is the inverse of handleSpecialChars and is called after running the bibtex parser
    private static function inverseHandleSpecialChars($bibtex){

        $bibtex = str_replace("SpecialChar001", "ä", $bibtex);
        $bibtex = str_replace("SpecialChar002", "Ä", $bibtex);
        $bibtex = str_replace("SpecialChar003", "ö", $bibtex);
        $bibtex = str_replace("SpecialChar004", "Ö", $bibtex);
        $bibtex = str_replace("SpecialChar005", "ü", $bibtex);
        $bibtex = str_replace("SpecialChar006", "Ü", $bibtex);
        $bibtex = str_replace("SpecialChar007", "ė", $bibtex);
        $bibtex = str_replace("SpecialChar008", "ñ", $bibtex);
        $bibtex = str_replace("SpecialChar009", "é", $bibtex);
        $bibtex = str_replace("SpecialChar010", "á", $bibtex);
        $bibtex = str_replace("SpecialChar011", "ý", $bibtex);
        $bibtex = str_replace("SpecialChar012", "ó", $bibtex);
        $bibtex = str_replace("SpecialChar013", "ú", $bibtex);
        $bibtex = str_replace("SpecialChar014", "ç", $bibtex);
        $bibtex = str_replace("SpecialChar015", "í", $bibtex);
        $bibtex = str_replace("SpecialChar016", "ć", $bibtex);
        $bibtex = str_replace("SpecialChar017", "è", $bibtex);
        $bibtex = str_replace("SpecialChar018", "č", $bibtex);
        $bibtex = str_replace("SpecialChar019", "Č", $bibtex);
        $bibtex = str_replace("SpecialChar020", "ž", $bibtex);
        $bibtex = str_replace("SpecialChar021", "ň", $bibtex);
        $bibtex = str_replace("SpecialChar022", "å", $bibtex);
        $bibtex = str_replace("SpecialChar023", "š", $bibtex);
        $bibtex = str_replace("SpecialChar024", "ø", $bibtex);
        $bibtex = str_replace("SpecialChar025", "ã", $bibtex);
        $bibtex = str_replace("SpecialChar026", "ř", $bibtex);
        $bibtex = str_replace("SpecialChar027", "ë", $bibtex);
        $bibtex = str_replace("SpecialChar028", "Ž", $bibtex);
        $bibtex = str_replace("SpecialChar029", "ń", $bibtex);
        $bibtex = str_replace("SpecialChar030", "Å", $bibtex);
        $bibtex = str_replace("SpecialChar031", "ą", $bibtex);
        $bibtex = str_replace("SpecialChar032", "ż", $bibtex);
        $bibtex = str_replace("SpecialChar033", "Ł", $bibtex);
        $bibtex = str_replace("SpecialChar034", "ł", $bibtex);
        $bibtex = str_replace("SpecialChar035", "ŕ", $bibtex);
        $bibtex = str_replace("SpecialChar036", "ï", $bibtex);
        $bibtex = str_replace("SpecialChar037", "Á", $bibtex);
        $bibtex = str_replace("SpecialChar038", "ę", $bibtex);

        return $bibtex;

    }

    

    private static function RIStoBibTeX($id){

        # Note: This function is technically not using RIS files anymore even though its still called RIStoBibTeX

        $bibtex_url = "https://citation-needed.springer.com/v2/references/10.1038/" . $id . "?format=bibtex&flavour=citation";
        $bibtex = file_get_contents($bibtex_url);

        # Get rid of strange characters at the beginning (before @)!?! 
        # update 2022/10: not needed anymore (?)
        //$bib = strstr($bib, "@");
        
        $bibtex = natureParser::handleSpecialChars($bibtex); 

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
        
        $paper["title"] = handleBibTeXSpecialSymbols($entries[0]["title"]);
        $paper["journal"] = $entries[0]["journal"];
        $paper["volume"] = $entries[0]["volume"];
        $paper["number"] = $entries[0]["pages"];

        // Change "Name, Prename" to "Prename Name"
        $bibtexauthorstring = $entries[0]["author"];
        $bibtexauthorstring = str_replace('{', '', $bibtexauthorstring);
        $bibtexauthorstring = str_replace('}', '', $bibtexauthorstring);
        $authors = explode("and ",handleBibTeXSpecialSymbols($bibtexauthorstring));
        $paper["authors"] = array();
        foreach($authors as $author){
            $name = explode(", ",$author);
            array_push($paper["authors"],$name[1]." ".$name[0]);
        }
        $paper["year"] = $entries[0]["year"];
        $paper["month"] = monthStrToInt($entries[0]["month"]);
        $paper["url"] = "https://www.nature.com/articles/".$id;

        $bibtex = natureParser::inverseHandleSpecialChars($bibtex);
        
        $paper["bibtex"] = $bibtex;
        
        $paper["identifier"] = $id;

        return $paper;
    }

}
?>
