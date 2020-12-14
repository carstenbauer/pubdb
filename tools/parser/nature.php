<?php
# Authors: Carsten Bauer
include_once("config/config.php");
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

    private static function extractPureID($str){
        
        if (strpos($str, "nature.com") !== False) {
            $str = substr(strrchr($str,'/'),1); 
            return str_replace(".html","",$str);
        }

        # Allow for DOI
        if (strpos($str, "10.1038/") !== False) {
            $str = substr(strrchr($str,'/'),1);
            return $str;
        }        
     
        return False;
    }


    private static function RIStoBibTeX($id){
        $url = "https://www.nature.com/articles/".$id;
        $risurl = $url.".ris";

        $ris = PROJECT_ROOT."tmp/".$id.".ris";
        $res = @file_put_contents($ris, file_get_contents_curl($risurl));
        if ($res !== False) {
            $cmd = "cat ".$ris." | ".BIBUTILS_BIN_FOLDER."/ris2xml | ".BIBUTILS_BIN_FOLDER."/xml2bib -b -sd";

            $locale='de_DE.UTF-8';
            setlocale(LC_ALL,$locale);
            putenv('LC_ALL='.$locale);
            $bib = shell_exec($cmd);
            unlink($ris);
        } else
            $bib = "";

        # Get rid of strange characters at the beginning (before @)!?!
        $bib = strstr($bib, "@");
        $bib = str_replace("ä", "ae", $bib);
        $bib = str_replace("Ä", "Ae", $bib);
        $bib = str_replace("ö", "oe", $bib);
        $bib = str_replace("Ö", "Oe", $bib);
        $bib = str_replace("ü", "ue", $bib);
        $bib = str_replace("Ü", "Ue", $bib);

        # write bib file to tmp folder
        // @file_put_contents($ris.".bib", $bib);

        return $bib;
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
        // print($bibtex);
        
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
        $authors = explode("and",handleBibTeXSpecialSymbols($bibtexauthorstring));
        $paper["authors"] = array();
        foreach($authors as $author){
            $name = explode(", ",$author);
            array_push($paper["authors"],$name[1]." ".$name[0]);
        }
        $paper["year"] = $entries[0]["year"];
        $paper["month"] = monthStrToInt($entries[0]["month"]);
        $paper["url"] = "https://www.nature.com/articles/".$id;
        $paper["bibtex"] = $bibtex;
        $paper["identifier"] = $id;

        return $paper;
    }

}
?>
