<?php
include_once('tools/parser/arxiv.php');
include_once('tools/parser/aps.php');
include_once('tools/parser/nature.php');
include_once('tools/parser/quantum.php');
include_once('tools/parser/scipost.php');
include_once('tools/parser/science.php');
include_once('tools/parser/iop.php');

function contains($string, $array, $caseSensitive = false)
{
    $stripedString = $caseSensitive ? str_replace($array, '', $string) : str_ireplace($array, '', $string);
    return strlen($stripedString) !== strlen($string);
}


function checkAPS($pubidstr){
    $apsarray = array("PRL","PRB","PRE", "PRMATERIALS", "PRRESEARCH", "PRA", "PRC", "PRD", "RMP", "Rev", "Physical", "10.1103/", "PRX");
    if (contains($pubidstr,$apsarray)) {
        # check for mix-up with nature
        if (stripos($pubidstr, "Nat") !== False){
            return False;
        }        
        return True;
    }
    
    return False;
}

function checkNature($pubidstr){
    $naturearray = array("Nature", "10.1038/", "ncomms", "nphys", "Nat", "Report", "srep", "Scientific", "Com", "npj");
    if (contains($pubidstr,$naturearray))
        return True;
    
    return False;

}

function checkQuantum($pubidstr){
    $quantumarray = array("quantum-journal", "10.22331/", "Quantum");
    if (contains($pubidstr,$quantumarray)) {
        # check for mix-up with iop
        if (stripos($pubidstr, "Tec") !== False){
            return False;
        }
        return True;
    }
    return False;

}


function checkSciPost($pubidstr){
    $scipostarray = array("SciPost", "10.21468/");
    if (contains($pubidstr,$scipostarray))
        return True;
    
    return False;
}

function checkScience($pubidstr){
    $sciencearray = array("Sci", "10.1126/", "Adv");
    if (contains($pubidstr,$sciencearray)) {
        # check for mix-up with iop
        if (stripos($pubidstr, "Tec") !== False){
            return False;
        }
        return True;
    }
    
    return False;
}

function checkIOP($pubidstr){
    $ioparray = array("iopscience", "10.1088/", "New", "Phys", "Tec");
    if (contains($pubidstr,$ioparray))
        return True;
    
    return False;
}


function identifierToPaper($pubidstr){
    # Check if we have arxiv or aps identifier
    # TODO Make "waterproof"!
    if (checkAPS($pubidstr)){
        // Assume aps identifier
        $paper = apsParser::parse($pubidstr);
    } elseif (checkNature($pubidstr)) {
        $paper = natureParser::parse($pubidstr);
    } elseif (checkQuantum($pubidstr)) {
        $paper = quantumParser::parse($pubidstr);
    } elseif (checkSciPost($pubidstr)) {
        $paper = scipostParser::parse($pubidstr);
    } elseif (checkScience($pubidstr)) {
        $paper = scienceParser::parse($pubidstr);
    } elseif (checkIOP($pubidstr)) {
        $paper = iopParser::parse($pubidstr);
    } else {
        // Assume arxiv
        $paper = arxivParser::parse($pubidstr);
    }

    return isset($paper)?$paper:False;
}
?>