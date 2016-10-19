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
}

?>