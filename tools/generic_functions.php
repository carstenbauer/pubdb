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
    $str = str_replace("\\.e", "ė", $str);  #
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
    $str = str_replace("\&\#x3b1;", "α", $str);
    $str = str_replace("{\'{e}}", "é", $str);


    # from quantum.php
    $str = str_replace("{Quantum}", "Quantum", $str);
    $str = str_replace("{A}", "A", $str);
    $str = str_replace("{B}", "B", $str);
    $str = str_replace("{C}", "C", $str);
    $str = str_replace("{D}", "D", $str);
    $str = str_replace("{E}", "E", $str);
    $str = str_replace("{F}", "F", $str);
    $str = str_replace("{G}", "G", $str);
    $str = str_replace("{H}", "H", $str);
    $str = str_replace("{I}", "I", $str);
    $str = str_replace("{J}", "J", $str);
    $str = str_replace("{K}", "K", $str);
    $str = str_replace("{L}", "L", $str);
    $str = str_replace("{M}", "M", $str);
    $str = str_replace("{N}", "N", $str);
    $str = str_replace("{O}", "O", $str);
    $str = str_replace("{P}", "P", $str);
    $str = str_replace("{Q}", "Q", $str);
    $str = str_replace("{R}", "R", $str);
    $str = str_replace("{S}", "S", $str);
    $str = str_replace("{T}", "T", $str);
    $str = str_replace("{U}", "U", $str);
    $str = str_replace("{V}", "V", $str);
    $str = str_replace("{W}", "W", $str);
    $str = str_replace("{X}", "X", $str);
    $str = str_replace("{Y}", "Y", $str);
    $str = str_replace("{Z}", "Z", $str);

    $str = str_replace("\"a", "ä", $str);
    $str = str_replace("\"A", "Ä", $str);
    $str = str_replace("\"o", "ö", $str);
    $str = str_replace("\"u", "ü", $str);
    $str = str_replace("\"O", "Ö", $str);
    $str = str_replace("\"U", "Ü", $str);
    $str = str_replace("\"e", "ë", $str);

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




### replace all special characters inside a bibtex string and reverse it

# this function handles special chars in the bibtex file by replacing them (to prevent errors in the bibtex parser)
function handleSpecialChars($bibtex){

    $bibtex = str_replace("ä", "SpecialChar001", $bibtex);
    $bibtex = str_replace("Ä", "SpecialChar002", $bibtex);
    $bibtex = str_replace("ö", "SpecialChar003", $bibtex);
    $bibtex = str_replace("Ö", "SpecialChar004", $bibtex);
    $bibtex = str_replace("ü", "SpecialChar005", $bibtex);
    $bibtex = str_replace("Ü", "SpecialChar006", $bibtex);
    $bibtex = str_replace("ė", "SpecialChar007", $bibtex);
    $bibtex = str_replace("ñ", "SpecialChar008", $bibtex);
    $bibtex = str_replace("é", "SpecialChar009", $bibtex);
    $bibtex = str_replace("á", "SpecialChar010", $bibtex);
    $bibtex = str_replace("ý", "SpecialChar011", $bibtex);
    $bibtex = str_replace("ó", "SpecialChar012", $bibtex);
    $bibtex = str_replace("ú", "SpecialChar013", $bibtex);
    $bibtex = str_replace("ç", "SpecialChar014", $bibtex);
    $bibtex = str_replace("í", "SpecialChar015", $bibtex);
    $bibtex = str_replace("ć", "SpecialChar016", $bibtex);
    $bibtex = str_replace("è", "SpecialChar017", $bibtex);
    $bibtex = str_replace("č", "SpecialChar018", $bibtex);
    $bibtex = str_replace("Č", "SpecialChar019", $bibtex);
    $bibtex = str_replace("ž", "SpecialChar020", $bibtex);
    $bibtex = str_replace("ň", "SpecialChar021", $bibtex);
    $bibtex = str_replace("å", "SpecialChar022", $bibtex);
    $bibtex = str_replace("š", "SpecialChar023", $bibtex);
    $bibtex = str_replace("ø", "SpecialChar024", $bibtex);
    $bibtex = str_replace("ã", "SpecialChar025", $bibtex);
    $bibtex = str_replace("ř", "SpecialChar026", $bibtex);
    $bibtex = str_replace("ë", "SpecialChar027", $bibtex);
    $bibtex = str_replace("Ž", "SpecialChar028", $bibtex);
    $bibtex = str_replace("ń", "SpecialChar029", $bibtex);
    $bibtex = str_replace("Å", "SpecialChar030", $bibtex);
    $bibtex = str_replace("ą", "SpecialChar031", $bibtex);
    $bibtex = str_replace("ż", "SpecialChar032", $bibtex);
    $bibtex = str_replace("Ł", "SpecialChar033", $bibtex);
    $bibtex = str_replace("ł", "SpecialChar034", $bibtex);
    $bibtex = str_replace("ŕ", "SpecialChar035", $bibtex);
    $bibtex = str_replace("ï", "SpecialChar036", $bibtex);
    $bibtex = str_replace("Á", "SpecialChar037", $bibtex);
    $bibtex = str_replace("ę", "SpecialChar038", $bibtex);
    $bibtex = str_replace("/", "SpecialChar039", $bibtex);

    return $bibtex;
}
# this function is the inverse of handleSpecialChars and is called after running the bibtex parser
function inverseHandleSpecialChars($bibtex){

    $bibtex = str_replace("SpecialChar001", "ä", $bibtex);
    $bibtex = str_replace("SpecialChar002", "Ä", $bibtex);
    $bibtex = str_replace("SpecialChar003", "ö", $bibtex);
    $bibtex = str_replace("SpecialChar004", "Ö", $bibtex);
    $bibtex = str_replace("SpecialChar005", "ü", $bibtex);
    $bibtex = str_replace("SpecialChar006", "Ü", $bibtex);
    $bibtex = str_replace("SpecialChar007", "ė", $bibtex);
    $bibtex = str_replace("SpecialChar008", "ñ", $bibtex);
    $bibtex = str_replace("SpecialChar009", "é", $bibtex);
    $bibtex = str_replace("SpecialChar010", "á", $bibtex);
    $bibtex = str_replace("SpecialChar011", "ý", $bibtex);
    $bibtex = str_replace("SpecialChar012", "ó", $bibtex);
    $bibtex = str_replace("SpecialChar013", "ú", $bibtex);
    $bibtex = str_replace("SpecialChar014", "ç", $bibtex);
    $bibtex = str_replace("SpecialChar015", "í", $bibtex);
    $bibtex = str_replace("SpecialChar016", "ć", $bibtex);
    $bibtex = str_replace("SpecialChar017", "è", $bibtex);
    $bibtex = str_replace("SpecialChar018", "č", $bibtex);
    $bibtex = str_replace("SpecialChar019", "Č", $bibtex);
    $bibtex = str_replace("SpecialChar020", "ž", $bibtex);
    $bibtex = str_replace("SpecialChar021", "ň", $bibtex);
    $bibtex = str_replace("SpecialChar022", "å", $bibtex);
    $bibtex = str_replace("SpecialChar023", "š", $bibtex);
    $bibtex = str_replace("SpecialChar024", "ø", $bibtex);
    $bibtex = str_replace("SpecialChar025", "ã", $bibtex);
    $bibtex = str_replace("SpecialChar026", "ř", $bibtex);
    $bibtex = str_replace("SpecialChar027", "ë", $bibtex);
    $bibtex = str_replace("SpecialChar028", "Ž", $bibtex);
    $bibtex = str_replace("SpecialChar029", "ń", $bibtex);
    $bibtex = str_replace("SpecialChar030", "Å", $bibtex);
    $bibtex = str_replace("SpecialChar031", "ą", $bibtex);
    $bibtex = str_replace("SpecialChar032", "ż", $bibtex);
    $bibtex = str_replace("SpecialChar033", "Ł", $bibtex);
    $bibtex = str_replace("SpecialChar034", "ł", $bibtex);
    $bibtex = str_replace("SpecialChar035", "ŕ", $bibtex);
    $bibtex = str_replace("SpecialChar036", "ï", $bibtex);
    $bibtex = str_replace("SpecialChar037", "Á", $bibtex);
    $bibtex = str_replace("SpecialChar038", "ę", $bibtex);
    $bibtex = str_replace("SpecialChar039", "/", $bibtex);

    return $bibtex;

}



?>