#!/usr/bin/php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use SdifParser\Model\Parser;

$parser = new Parser();
$file = $parser->parse_file($argv[1], $argv[2]);
print json_encode($file, JSON_PRETTY_PRINT);
//print var_dump($file);

//print json_encode($file);

//print json_encode($file->getMeet()->getEvents(), JSON_PRETTY_PRINT);
print json_last_error();
print json_last_error_msg();

/*
print 'Test';

$clubs = [];
$clubs['CAN']['MH2O'] = 'MH2O';
$clubs['CAN']['HALD'] = 'HALD';
$clubs['CAN']['CCGC'] = 'MH2O';
print var_dump($clubs);
*/