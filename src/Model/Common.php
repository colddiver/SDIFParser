<?php

namespace SdifParser\Model;

use DateTime;

Trait Common
{
    public function decodeCourse($s): string {
        if ($s == 1 || $s == 'S') return 'SCM';
        if ($s == 2 || $s == 'Y') return 'SCY';
        if ($s == 3 || $s == 'L') return 'LCM';
        if ($s == 'X') return "DQ";
        return '';
    }
    
    public function output_time($t): string {
        $min = floor($t / 60);
        $sec = $t - $min * 60;
        $hour = 0;
        if ($min > 60) {
            $hour = floor($min / 60);
            $min = ($min - $hour * 60);
        }
        if ($hour == 0)
            return sprintf("%u:%05.2f", $min, $sec);
            else
                return sprintf("%u:%u:%05.2f", $hour, $min, $sec);
    }
    
    public function parseTime($t): float {
        
        /*
         Format is either:
         - mm:ss.ss or mm ss.ss
         - NT   No Time
         - NS   No Swim (or No Show)
         - DNF  Did Not Finish
         - DQ   Disqualified
         - SCR  Scratch
         */
        
        //Handling non-numeric time codes
        $codes = ['NT', 'NS', 'DNF', 'DQ', 'SCR'];
        if (in_array($t, $codes)) {
            return  0.0;
        }
        
        //Handling mm:ss.ss and mm ss.ss formats
        $separator = ':';
        if (strpos($t, ' ') !== false) $separator = ' ';
        
        //Parsing
        $parts = explode($separator, $t);
        $count = count($parts);
        
        switch ($count) {
            case 1:                                             //21.56
                return (float) $t;
            case 2:                                             //1:21.56
                return (float) $parts[0] * 60 + $parts[1];
        }
    }
    
    public function extractTimeCode($t): string {
        //Handling non-numeric time codes
        $codes = ['NT', 'NS', 'DNF', 'DQ', 'SCR'];
        if (in_array($t, $codes)) {
            return  $t;
        } else {
            return 'OK';
        }
    }
    
    public function formatTime($t): string {
        $min = floor($t / 60);
        $sec = $t - $min * 60;
        $hour = 0;
        if ($min > 60) {
            $hour = floor($min / 60);
            $min = ($min - $hour * 60);
        }
        if ($hour == 0)
            return sprintf("%u:%05.2f", $min, $sec);
            else
                return sprintf("%u:%u:%05.2f", $hour, $min, $sec);
    }
    
    public function decodeStroke($code): string {
        switch ($code) {
            case 1:
                return "Free";
            case 2:
                return "Back";
            case 3:
                return "Breast";
            case 4:
                return "Fly";
            case 5:
                return "IM";
            case 6:
                return "Free Relay";
            case 7:
                return "Medley Relay";
        }
    }
    
    public function extractDate($s): \DateTime {
        return DateTime::createFromFormat('mdY', $s);
    }
    
    /*
    public function formatDate(string $format = 'Y-m-d'): string {
        return date_format($this->date, $format);
    }
    */
    
    public function parseAgeGroup($code): string {
        return substr($code, 0, 2) . '-' . substr($code, 2, 2);
    }
    
    public function decodeSplitType($code): string {
        switch($code) {
            case 'P':
                return 'Prelims';
                break;
            case 'F':
                return 'Finals';
                break;
            case 'S':
                return 'Swim-offs';
                break;
            default:
                return 'Unknown';
                break;
        }
    }

}
