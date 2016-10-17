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

        $journal = natureParser::extractJournal($str);
        $numbers = natureParser::extractNumbers($str);

        if ($journal!=False&&count($numbers)==1)
            return $journal.$numbers[0];

        switch($journal){
            case "ncomms":
                if (count($numbers)==1)
                    return "ncomms".$numbers[0];
                elseif (count($numbers)>=2)
                    return "ncomms".$numbers[1];
                break;
            case "nphys":
            case "nature":
                if(count($numbers)<2)
                    return False;
                $url = "http://www.nature.com/opensearch/request?interface=sru&query=prism.productCode+%3D+%22".$journal."%22+AND+prism.startingPage+%3D+%22".$numbers[1]."%22+AND+prism.volume+%3D+%22".$numbers[0]."%22&httpAccept=application/json";
                $jsonraw = file_get_contents($url);
                $json = json_decode($jsonraw, true);
                $obj = $json["feed"]["entry"][0]["sru:recordData"]["pam:message"]["pam:article"]["xhtml:head"];
                $id = substr(strrchr($obj["prism:doi"],'/'),1);
                return $id==""?False:$id;
                break;

        }     
     
        return False;
    }

    private static function RIStoBibTeX($journal,$id,$obj){

        switch ($journal) {
            case "nphys":
                $volume = $obj["prism:volume"];
                $number = $obj["prism:number"];
                $url = "http://www.nature.com/nphys/journal/v".$volume."/n".$number."/ris/".$id.".ris";
                break;
            
            case "ncomms":
                $url = "http://www.nature.com/articles/".$id.".ris";
                break;

            case "nature":
                $volume = $obj["prism:volume"];
                $number = $obj["prism:number"];
                $url = "http://www.nature.com/nature/journal/v".$volume."/n".$number."/ris/".$id.".ris";
                break;
        }
        
        $ris = PROJECT_ROOT."/tmp/".$id.".ris";
        $res = @file_put_contents($ris, file_get_contents($url));
        if ($res !== False) {
            $cmd = "cat ".$ris." | ".BIBUTILS_BIN_FOLDER."/ris2xml | ".BIBUTILS_BIN_FOLDER."/xml2bib";
            $bib = shell_exec($cmd);
            unlink($ris);
        } else
            $bib = "";
        return $bib;
    }

    
    public static function parse($natureStr){
        $id = natureParser::extractPureID($natureStr);
        # Check if valid ID (journalNUMBER format) with regexp
        if ($id==False) return False;

        $paper = array();
        $paper["journal"] = natureParser::extractJournal($natureStr);
        $paper["identifier"] = $id;

        $url = "http://www.nature.com/opensearch/request?queryType=cql&query=".$id."&httpAccept=application/json";
        $jsonraw = file_get_contents($url);
        $json = json_decode($jsonraw, true);
        $obj = $json["feed"]["entry"][0]["sru:recordData"]["pam:message"]["pam:article"]["xhtml:head"];
        $paper["title"] = $obj["dc:title"];
        $paper["url"] = $obj["prism:url"];
        $paper["authors"] = $obj["dc:creator"];
        $paper["year"] = substr($obj["prism:publicationDate"],0,4);
        $paper["bibtex"] = natureParser::RIStoBibTeX($paper["journal"],$id,$obj);
        $paper["volume"] = $obj["prism:volume"];
        if ($paper["journal"]=="nphys")
            $paper["number"] = $obj["prism:startingPage"];
        else if ($paper["journal"]=="ncomms")
            $paper["number"] = natureParser::extractNumbers($id)[0];
        else // journal is nature
            $paper["number"] = $obj["prism:startingPage"];

        return $paper;
    }

}
?>
