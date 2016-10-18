<?php

function monthStrToInt($monthstr){
    switch ($monthstr) {
        case 'January':
        case 'Januar':
        case 'Jan':
            return 1;

        case 'February':
        case 'Februar':
        case 'Feb':
            return 2;

        case 'March':
        case 'März':
        case 'Mar':
            return 3;

        case 'April':
        case 'April':
        case 'Apr':
            return 4;

        case 'May':
        case 'Mai':
        case 'May':
            return 5;

        case 'June':
        case 'Juni':
        case 'Jun':
            return 6;

        case 'July':
        case 'Juli':
        case 'Jul':
            return 7;

        case 'August':
        case 'August':
        case 'Aug':
            return 8;

        case 'September':
        case 'September':
        case 'Sep':
            return 9;

        case 'October':
        case 'Oktober':
        case 'Oct':
            return 10;

        case 'November':
        case 'November':
        case 'Nov':
            return 11;

        case 'Dezember':
        case 'Dezember':
        case 'Dez':
            return 12;
        
        default:
            return 13;
    }
}

?>