<?php
# Authors: Malte Pütz, based on code by Carsten Bauer
include_once("tools/parser/BibTexParser/ListenerInterface.php");
include_once("tools/parser/BibTexParser/Listener.php");
include_once("tools/parser/BibTexParser/Parser.php");
include_once("tools/parser/BibTexParser/ParseException.php");


class scienceParser {

    
    #private static function contains($haystack, $needle){
    #    if(strpos($haystack, $needle) !== False)
    #        return True;
    #    else
    #        return False;
    #}
    #
    private static function extractNumbers($str){
        preg_match_all('!\d+!', $str, $m);
        return $m[0];
    }

    private static function extractJournal($str){
        if (stripos($str, "adv") !== False){            # assume Science Advances
            return "sciadv";
        }
        
        return "science";                               # assume Science
    }

    # this function cuts the doi from the given url
    private static function getDoiFromUrl($url) {
        $doi = substr($url, strpos($url, "10."));

        if (strpos($doi, "?") !== False) {
            $doi = substr($doi, 0, strpos($doi, "?"));
        }

        return $doi;
    }

    # this function returns advanced search url of the given journal, volume and page
    private static function getSearchURL($journal, $volume, $page) {
        $url = "https://www.science.org/action/quickLink?quickLinkJournal=$journal&quickLinkVolume=$volume&quickLinkIssue=&quickLinkPage=$page&quickLink=true&quickLinkYear=";
        return $url;
    }

    # this function returns redirected url of the given url
    private static function getRedirectedUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
        # set cookie
        curl_setopt($ch, CURLOPT_COOKIE, "cookie_name=cookie_value");
    
        $data = curl_exec($ch);
        curl_close($ch);
        if (preg_match('/Location: (.*)\r/', $data, $matches)) {
            return trim($matches[1]);
        } else {
            return $url;
        }
    }

    # this function is basically file_get_contents, but it sets the cookie
    private static function file_get_contents_curl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    
        # set cookie
        curl_setopt($ch, CURLOPT_COOKIE, "cookie_name=cookie_value");
    
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }


    # this function returns the doi for a given input string (doi, url or reference string)
    private static function extractDoi($str){

        ### allow for URL
        if (stripos($str, "science.org") !== False){
            return scienceParser::getDoiFromUrl($str);

        }
        
        ### allow for DOI
        if (stripos($str, "10.1126") !== False){
            return $str;
        }

        ### allow for reference string

        # extract journal
        $journal = scienceParser::extractJournal($str);

        if ($journal == "sciadv") {
            return False;                            // ! not implemented yet
        }

        # extract volume and page
        $numbers = scienceParser::extractNumbers($str);

        # get DOI from advanced search
        $url = scienceParser::getSearchURL($journal, $numbers[0], $numbers[1]);
        $url = scienceParser::getRedirectedUrl($url);

        # catch error when there is more than one result
        if ((strpos($url, $journal) !== False) && (strpos($url, $numbers[0]) !== False) && (strpos($url, $numbers[1]) !== False)) {

            # try increasing page number by one to get rid of the article ending on the starting page of the article of interest
            $url = scienceParser::getSearchURL($journal, $numbers[0], strval(intval($numbers[1] + 1))); 
            $url = scienceParser::getRedirectedUrl($url);
        }

        return scienceParser::getDoiFromUrl($url);
    }

    # this function returns the bibtex for a given doi
    private static function DOItoBibTeX($doi){
        $doi = str_replace("/", "%2F", $doi);
        $url = "https://www.science.org/action/downloadCitation?doi=$doi&downloadFileName=csp_378_&include=abs&format=bibtex&submit=EXPORT+CITATION";
        $bibtex = scienceParser::file_get_contents_curl($url);
        $bibtex = strstr($bibtex, "@");
        $bibtex = handleSpecialChars($bibtex); 

        return $bibtex;
    }

    public static function parse($scienceStr){

        # get doi
        $doi = scienceParser::extractDoi($scienceStr);
        if ($doi == False){
            return False;
        }

        $paper = array();
        $bibtex = scienceParser::DOItoBibTeX($doi);

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
        $authors = explode("and ",handleBibTeXSpecialSymbols($bibtexauthorstring));
        $paper["authors"] = array();
        foreach($authors as $author){
            $name = explode(", ", trim($author));
            array_push($paper["authors"],$name[1]." ".$name[0]);
        }
        $paper["year"] = $entries[0]["year"];
        $paper["month"] = monthStrToInt($entries[0]["month"]);
        $paper["url"] = "https://www.science.org/doi/$doi";

        $bibtex = inverseHandleSpecialChars($bibtex);
        
        $paper["bibtex"] = $bibtex;
        
        $paper["identifier"] = $doi;

        return $paper;

    }
}