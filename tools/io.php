<?php

class Output {

    private static function LaTeX2MathJax($str){
        return str_replace('$','%!',$str);
    }

    public static function PaperToHTMLString($paper){
        $authors_str = join(', ',$paper["authors"]);
        $project_str = "";
        if (isset($paper["projects"])) {
            $project_str = "(".join(", ",$paper["projects"]).")";
        }
        $paper_str = $project_str." ".$authors_str.", <a href=".$paper["url"]."><i>".Output::LaTeX2MathJax($paper["title"])."</i></a>, ".$paper["journal"].", ".$paper["identifier"];
        return $paper_str;
    }


}

?>
