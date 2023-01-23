<?php
# Authors: Malte Pütz, based on code by Carsten Bauer
include_once("tools/parser/BibTexParser/ListenerInterface.php");
include_once("tools/parser/BibTexParser/Listener.php");
include_once("tools/parser/BibTexParser/Parser.php");
include_once("tools/parser/BibTexParser/ParseException.php");


class iopParser {

    private static function extractNumbers($str){
        preg_match_all('!\d+!', $str, $m);
        return $m[0];
    }

    private static function extractJournal($str){
        if (stripos($str, "new") !== False){            # assume New Journal of Physics
            return "1367-2630";
        }
        return "2058-9565";                             # assume Quantum Sci. Technol.
    }

    # this function cuts the doi from the given url
    private static function getDoiFromUrl($url) {
        $doi = substr($url, strpos($url, "10."));

        if (strpos($doi, "?") !== False) {
            $doi = substr($doi, 0, strpos($doi, "?"));
        }
        return $doi;
    }

    private static function getSearchUrl($journal, $volume, $page) {
        return "https://iopscience.iop.org/findcontent?CF_JOURNAL=" . $journal . "&CF_VOLUME=" . $volume . "&CF_ISSUE=&CF_PAGE=" . $page . "&submit=Lookup";
    }

    # this function returns redirected url of the given url
    private static function getRedirectedUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/109.0",
            "Accept-Language: en-US,en;q=0.5",
        ));
        $data = curl_exec($ch);
        curl_close($ch);
        if (preg_match('/Location: (.*)\r/', $data, $matches)) {
            return trim($matches[1]);
        } else {
            return $url;
        }
    }

    private static function extractDoi($str) {

        ### allow for URL
        if (stripos($str, "iopscience.iop.org") !== False) {
            return iopParser::getDoiFromUrl($str);
        }

        ### allow for DOI
        if (stripos($str, "10.1088") !== False) {
            return $str;
        }

        ### allow for reference string

        # extract journal
        $journal = iopParser::extractJournal($str);

        # extract volume and page
        $numbers = iopParser::extractNumbers($str);

        # get DOI from search
        $url = iopParser::getSearchUrl($journal, $numbers[0], $numbers[1]);
        $url = iopParser::getRedirectedUrl($url);

        # check if url is valid
        if (stripos($url, "article/10.") === False) {
            return False;
        }

        return iopParser::getDoiFromUrl($url);
    }

    private static function DOItoBibTeX($doi) {
        $url = "https://iopscience.iop.org/export?type=article&doi=" . $doi . "&exportFormat=iopexport_bib&exportType=abs&navsubmit=Export+abstract";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/109.0",
            "Accept-Language: en-US,en;q=0.5",
        ));

        $bibtex = curl_exec($ch);
        $bibtex = handleSpecialChars($bibtex);
    
        return $bibtex;
    }

    public static function parse($str) {
        $doi = iopParser::extractDoi($str);
        
        # check if doi is valid
        if ($doi === False) {
            return False;
        }

        $paper = array();
        $bibtex = iopParser::DOItoBibTeX($doi);

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
        $authors = explode(" and ",handleBibTeXSpecialSymbols($bibtexauthorstring));
        $paper["authors"] = array();
        foreach($authors as $author){
            $name = explode(", ", trim($author));
            array_push($paper["authors"],$name[1]." ".$name[0]);
        }
        $paper["year"] = $entries[0]["year"];
        $paper["month"] = monthStrToInt($entries[0]["month"]);
        $paper["url"] = "https://iopscience.iop.org/article/$doi";

        $bibtex = inverseHandleSpecialChars($bibtex);
        
        $paper["bibtex"] = $bibtex;
        
        $paper["identifier"] = $doi;

        return $paper;
    }






}


?>