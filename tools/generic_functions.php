<?php

function monthStrToInt($monthstr){
    switch ($monthstr) {
        case 'January':
        case 'Januar':
        case 'Jan':
        case '1':
            return 1;

        case 'February':
        case 'Februar':
        case 'Feb':
        case '2':
            return 2;

        case 'March':
        case 'März':
        case 'Mar':
        case '3':
            return 3;

        case 'April':
        case 'April':
        case 'Apr':
        case '4':
            return 4;

        case 'May':
        case 'Mai':
        case 'May':
        case '5':
            return 5;

        case 'June':
        case 'Juni':
        case 'Jun':
        case '6':
            return 6;

        case 'July':
        case 'Juli':
        case 'Jul':
        case '7':
            return 7;

        case 'August':
        case 'August':
        case 'Aug':
        case '8':
            return 8;

        case 'September':
        case 'September':
        case 'Sep':
        case '9':
            return 9;

        case 'October':
        case 'Oktober':
        case 'Oct':
        case '10':
            return 10;

        case 'November':
        case 'November':
        case 'Nov':
        case '11':
            return 11;

        case 'Dezember':
        case 'Dezember':
        case 'Dez':
        case '12':
            return 12;
        
        default:
            return 13;
    }
};


function handleBibTeXSpecialSymbols($bibtexstr){
    $str = str_replace("\\\"a", "ä", $bibtexstr);
    $str = str_replace("\\ifmmode \\check{c}\\else \\v{c}\\fi{}", "\\v{c}", $str);
    $str = str_replace("\\ifmmode \\check{C}\\else \\v{C}\\fi{}", "\\v{C}", $str);
    $str = str_replace("\\ifmmode \\check{s}\\else \\v{s}\\fi{}", "\\v{s}", $str);
    $str = str_replace("\\ifmmode \\check{S}\\else \\v{S}\\fi{}", "\\v{S}", $str);
    $str = str_replace("\\\"A", "Ä", $str);
    $str = str_replace("\\\"o", "ö", $str);
    $str = str_replace("\\\"u", "ü", $str);
    $str = str_replace("\\\"O", "Ö", $str);
    $str = str_replace("\\\"U", "Ü", $str);
    $str = str_replace("\\ss", "ß", $str);
    $str = str_replace("\\^e", "ê", $str);
    $str = str_replace("\\'e", "é", $str);
    $str = str_replace("\\`e", "è", $str);
    $str = str_replace("\\\"e", "ë", $str);
    $str = str_replace("\\`i", "ì", $str);
    $str = str_replace("\\o{}", "ø", $str);
    $str = str_replace("\\o", "ø", $str);
    $str = str_replace("\\'u", "ú", $str);
    $str = str_replace("\\aa", "å", $str);
    $str = str_replace("\\c", "ç", $str);
    $str = str_replace("\\~n", "ñ", $str);
    $str = str_replace("\\v{c}", "č", $str);
    $str = str_replace("\\v{C}", "Č", $str);
    $str = str_replace("\\v{s}", "š", $str);
    $str = str_replace("\\v{S}", "Š", $str);
    $str = str_replace("\\'{\\i}", "í", $str);
    $str = str_replace("\\'y", "ý", $str);
    $str = str_replace("\\'o", "ó", $str);
    $str = str_replace("\\'a", "á", $str);
    $str = str_replace("\\ensuremath{-}", "-", $str);

    return $str;
};


function isYear($nr){
    if (!is_numeric($nr)) $nr = intval($nr);

    if ($nr>1000 && $nr<2100)
        return true;

    return false;
};



function file_get_contents_curl($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);       

    $data = curl_exec($ch);
    curl_close($ch);

    return $data;
};


?>