<?php
# Authors: Carsten Bauer
include_once("config/config.php");
include_once(SIMPLEPIE_AUTOLOADER_LOCATION.'/autoloader.php');


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


    private static function extractJournal($str){
        $str = strtolower($str);
        if(natureParser::contains($str,"nphys") || natureParser::contains($str,"phys")){
            return "nphys";
        } elseif(natureParser::contains($str,"ncomms") || natureParser::contains($str,"comm")){
            return "ncomms";
        } elseif(natureParser::contains($str,"nature")) {
            return "nature";
        } else {
            return False;
        }
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

    
    public static function parse($natureStr){
        $id = natureParser::extractPureID($natureStr);

        $paper = array();
        $paper["journal"] = natureParser::extractJournal($natureStr);
        $paper["identifier"] = $id;

        echo "Journal: ".$paper["journal"];
        echo "Nature ID: ".$id;

        return $paper;
    }

}
?>
