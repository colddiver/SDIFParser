#!/usr/bin/php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use SdifParser\Model\Parser;

$action = $argv[1];
$filePath = $argv[2];
$defaultCountry = '';
if (isset($argv[3])) {
    $defaultCountry = $argv[3];
}

$allowedActions = [
    '-h', '--help',
    '-v', '--validate',
    '-p', '--parse',
    '-s', '--swimmers',
    '-e', '--events',
    '-t', '--teams',
    '-i', '--info',
    '-r', '--rename'
];

if ($action == '-h' || $action == '--help' || !in_array($action, $allowedActions)) {
    print "sd3parser, a script to parse and extract data from SDIF swimming meet data files.\n";
    print "Usage ./sd3parser [ACTION]... [FILE]... [DEFAULT COUNTRY]...\n\n";
    print "Actions:\n";
    print "  -h,  --help \t\t print this help\n";
    print "  -p,  --parse \t\t parse and output (json) the entire sdif file\n";
    print "  -s,  --swimmers \t parse and output (json) meet swimmers\n";
    print "  -e,  --events \t parse and output (json) meet events\n";
    print "  -t,  --teams \t\t parse and output (json) meet teams\n";
    print "  -i,  --info \t\t parse and output (text) meet basic info\n";
    print "  -r,  --rename \t rename the sdif file based on its contents '[DATE] [NAME] [COURSE].sd3'\n";
    print "  -v,  --validate \t parse and output any validation error\n";
    exit;
}

//Validating file path
if (!file_exists($filePath)) {
    exit(printf('Invalid file path - file %s does not exist.',$filePath));
}

$parser = new Parser();

if ($action == '-v' || $action == '--validate') {
    
    set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    });
        
        try {
            $pathParts = pathinfo($filePath);
            print sprintf("\n\nValidating: %s", $pathParts['basename']);
            $file = $parser->parseMeetFile($filePath, $defaultCountry);

        } catch (ErrorException $e) {
            print sprintf("\n\t***INVALID: %s\n", $e->getMessage());
            print "\n\t File: " . $e->getFile();
            print "\n\t Line: " . $e->getLine();
            //print "\n\t" . $e->getTraceAsString();
        }
    
    exit;
}

//Parsing and outputting
$file = $parser->parseMeetFile($filePath, $defaultCountry);

if ($action == '-p' || $action == '--parse') {
    print json_encode($file, JSON_PRETTY_PRINT);
    exit;
}

if ($action == '-s' || $action == '--swimmers') {
    print json_encode($file->getMeet()->getSwimmers(), JSON_PRETTY_PRINT);
    exit;
}

if ($action == '-e' || $action == '--events') {
    print json_encode($file->getMeet()->getEvents(), JSON_PRETTY_PRINT);
    exit;
}

if ($action == '-t' || $action == '--teams') {
    print json_encode($file->getMeet()->getTeams(), JSON_PRETTY_PRINT);
    exit;
}

if ($action == '-i' || $action == '--info') {
    print sprintf("Meet: \t\t%s\n", $file->getMeet()->getName());
    print sprintf("Location: \t%s %s %s\n", $file->getMeet()->getCity(), $file->getMeet()->getState(), $file->getMeet()->getCountry());
    print sprintf("Start Date: \t%s\n", $file->getMeet()->getStartDate());
    print sprintf("End Date: \t%s\n", $file->getMeet()->getEndDate());
    
    print "Events:\n";
    $i = 1;
    foreach ($file->getMeet()->getEvents() as $event) {
        print sprintf("\t%d. %s\n", $i, $event->getName());
        $i++;
    }
    
    print "Teams:\n";
    $i = 1;
    $j = 1;
    foreach ($file->getMeet()->getTeams() as $countries) {
        
        foreach ($countries as $team) {
            print sprintf("\t%d. [%s] %s (%s)\n", $i, $team->getCountry(), $team->getName(), $team->getCode());
            $i++;
            
            foreach ($team->getSwimmers() as $swimmer) {
                print sprintf("\t\t%d. %s (%s%d)\n", $j, $swimmer->getFullName(), $swimmer->getGender(), $swimmer->getAge());
                $j++;
            }
        }
    }
    exit;
}

if ($action == '-r' || $action == '--rename') {
    $pathParts = pathinfo($filePath);
    
    //print $file->getRecommendedFileName(). "\n";
    rename($filePath, $pathParts['dirname'] . '/' . $file->getRecommendedFileName());
    exit;
}