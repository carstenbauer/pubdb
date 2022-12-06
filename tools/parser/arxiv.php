<?php
# Authors: Carsten Bauer
include_once("config/config.php");
include_once('tools/generic_functions.php');
include_once(SIMPLEPIE_AUTOLOADER_LOCATION.'/autoloader.php');


class arxivParser {
    
    
    private static function extractNumbers($str){
        preg_match_all('!\d+!', $str, $m);
        return $m[0];
    }


    # Removes prefix "arxiv:" in $arxivStr if it exists
    private static function extractPureID($str){
        $str = strtolower($str);
        if (strpos($str, 'arxiv:') !== false) {
            $str = str_replace('arxiv:','',$str);
        }

        # Allow for whole arxiv URL
        if (strpos($str,"arxiv.org") !== false) {
            $str = substr(strrchr($str,'/'),1);
        }
        
        # Should contain exactly two numbers
        $numbers = arxivParser::extractNumbers($str);
        if (count($numbers) < 2) {
            return False;
        }

        return $numbers[0].".".$numbers[1];
    }


    public static function generateArXivBibTeX($paper){
        // $arr = explode("/", $paper["authors"], 2);
        // $first = $arr[0];
        $firstauthor = $paper["authors"][0];
        $names = explode(' ', $firstauthor);
        $lastname = end($names);
        $authorstring = join(' and ',$paper["authors"]);

        return handleBibTeXSpecialSymbols("@article{".$lastname.$paper["year"].",
      title                    = {{".$paper["title"]."}},
      author                = {".$authorstring."},
      eprint                 = {arXiv:".$paper["identifier"]."},
      year                 = {".$paper["year"]."}
    }");
        // archivePrefix = \"arXiv\"
    }

    
    public static function parse($arxivStr){
        # Remove prefix "arxiv:" if it exists
        $arxivID = arxivParser::extractPureID($arxivStr);

        $paper = array();

        # Construct the query
        $base_url = 'http://export.arxiv.org/api/query?';
        $query = "search_query=".$arxivID."&start=0&max_results=1";

        // echo($base_url.$query);

        # SimplePie will automatically sort the entries by date
        # unless we explicitly turn this off
        $feed = new SimplePie();
        $feed->set_cache_location(SIMPLEPIE_CACHE_LOCATION);
        $feed->set_feed_url($base_url.$query);
        $feed->enable_order_by_date(false);
        $feed->init();
        $feed->handle_content_type();

        # Use these namespaces to retrieve tags
        $atom_ns = 'http://www.w3.org/2005/Atom';
        $opensearch_ns = 'http://a9.com/-/spec/opensearch/1.1/';
        $arxiv_ns = 'http://arxiv.org/schemas/atom';

        # Check if paper with $arxivID exists
        $totalResults = $feed->get_feed_tags($opensearch_ns,'totalResults')[0]['data'];
        if ($totalResults==0) {
            return False;
        }

        # Get paper information
        $entry = $feed->get_item();

        // $paper["identifier"] = explode('/abs/',$entry->get_id())[1];
        $paper["identifier"] = $arxivID;
        $paper["title"] = $entry->get_title();
        $paper["timestamp"] = $entry->get_item_tags($atom_ns,'published')[0]['data'];
        $numbers = arxivParser::extractNumbers($paper["timestamp"]);
        $paper["year"] = $numbers[0];
        $paper["month"] = intval($numbers[1]);
        
        # Gather list of authors (without affiliation!)
        $authors = array();
        foreach ($entry->get_item_tags($atom_ns,'author') as $author) {
            $name = $author['child'][$atom_ns]['name'][0]['data'];
            array_push($authors,$name);
        }
        $paper["authors"] = $authors;

        # Get the url of the paper on arxiv.org
        $paper["url"] = "";
        foreach ($entry->get_item_tags($atom_ns,'link') as $link) {
            if ($link['attribs']['']['rel'] == 'alternate') {
                $paper["url"] = $link['attribs']['']['href'];
            } 
        }

        # # Optional: Read journal reference if available 
        # $journal_ref_raw = $entry->get_item_tags($arxiv_ns,'journal_ref');
        # $journal_ref = "";
        # if ($journal_ref_raw) {
        #     $journal_ref = $journal_ref_raw[0]['data'];
        # } 

        $paper["journal"] = "arxiv";
        $paper["bibtex"] = arxivParser::generateArXivBibTeX($paper);

        return $paper;
    }

}
?>
