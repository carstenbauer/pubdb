<?php

class Output {

    private static function extractNumbers($str){
        preg_match_all('!\d+!', $str, $m);
        return $m[0];
    }

    private static function LaTeX2MathJax($str){
        return str_replace('$','%!',$str);
    }

    private static function idToStringAPS($identifier){
        $numbers = Output::extractNumbers($identifier); 
        return $numbers[0].", ".$numbers[1];
    }

    private static function idToStringArxiv($identifier){
        $numbers = Output::extractNumbers($identifier); 
        return $numbers[0].".".$numbers[1];
    }

    private static function idToStringNcomms($identifier){
        $numbers = Output::extractNumbers($identifier); 
        return $numbers[0];
    }

    public static function journalToString($journal, $identifier){
        switch($journal){
            case "pra": return "Phys. Rev. A. ".Output::idToStringAPS($identifier);
            case "prb": return "Phys. Rev. B. ".Output::idToStringAPS($identifier);
            case "prc": return "Phys. Rev. C. ".Output::idToStringAPS($identifier);
            case "prd": return "Phys. Rev. D. ".Output::idToStringAPS($identifier);
            case "pre": return "Phys. Rev. E. ".Output::idToStringAPS($identifier);
            case "prl": return "Phys. Rev. Lett. ".Output::idToStringAPS($identifier);
            case "rmp": return "Rev. Mod. Phys. ".Output::idToStringAPS($identifier);
            case "arxiv": return "arXiv e-print ".Output::idToStringArxiv($identifier);
            case "ncomms": return "Nature Comm. ".Output::idToStringNcomms($identifier);
            default: return $journal;
        }
    }

    public static function PaperToHTMLString($paper){
        $authors_str = join(', ',$paper["authors"]);
        $project_str = "";
        if (isset($paper["projects"])) {
            $project_str = "(".join(", ",$paper["projects"]).")";
        }
        $paper_str = $project_str." ".$authors_str.", <a href=".$paper["url"]."><i>".Output::LaTeX2MathJax($paper["title"])."</i></a>, ".Output::journalToString($paper["journal"],$paper["identifier"]).", ".$paper["year"];
        return $paper_str;
    }


}

?>
