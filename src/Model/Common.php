<?php

namespace SdifParser\Model;

use DateTime;

Trait Common
{
    public function decodeCourse($s): string {
        if ($s == 1 || $s == 'S') return 'SCM';
        if ($s == 2 || $s == 'Y') return 'SCY';
        if ($s == 3 || $s == 'L') return 'LCM';
        if ($s == 'X') return 'DQ';
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
            return sprintf('%u:%05.2f', $min, $sec);
            else
                return sprintf('%u:%u:%05.2f', $hour, $min, $sec);
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
            return sprintf('%u:%05.2f', $min, $sec);
            else
                return sprintf('%u:%u:%05.2f', $hour, $min, $sec);
    }
    
    public function decodeStroke($code): string {
        switch ($code) {
            // SD3
            case 1:
                return 'Free';
            case 2:
                return 'Back';
            case 3:
                return 'Breast';
            case 4:
                return 'Fly';
            case 5:
                return 'IM';
            case 6:
                return 'Free Relay';
            case 7:
                return 'Medley Relay';
            // HY3
            case 'A':
                return 'Free';
            case 'B':
                return 'Back';
            case 'C':
                return 'Breast';
            case 'D':
                return 'Fly';
            case 'E':               //FIXME - does this work for relays?!?
                return 'IM';
                
            default:
                return sprintf('Unknown stroke code: %s', $code);
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
    
    public function inferAgeGroup(int $age): string {
        if ($age >= 18 && $age <= 24) return '18-24';
        if ($age >= 25 && $age <= 29) return '25-29';
        if ($age >= 30 && $age <= 34) return '30-34';
        if ($age >= 35 && $age <= 39) return '35-39';
        if ($age >= 40 && $age <= 44) return '40-44';
        if ($age >= 45 && $age <= 49) return '45-49';
        if ($age >= 50 && $age <= 54) return '50-54';
        if ($age >= 55 && $age <= 59) return '55-59';
        if ($age >= 60 && $age <= 64) return '60-64';
        if ($age >= 65 && $age <= 69) return '65-69';
        if ($age >= 70 && $age <= 74) return '70-74';
        if ($age >= 75 && $age <= 79) return '75-79';
        if ($age >= 80 && $age <= 84) return '80-84';
        if ($age >= 85 && $age <= 89) return '85-89';
        if ($age >= 90 && $age <= 94) return '90-94';
        if ($age >= 95 && $age <= 99) return '95-99';
        if ($age >= 100 && $age <= 104) return '100-104';
        if ($age >= 105 && $age <= 109) return '105-109';
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
