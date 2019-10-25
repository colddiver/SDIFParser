#!/usr/bin/php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use SdifParser\Model\Parser;

$parser = new Parser();
$file = $parser->parse_file($argv[1]);
print json_encode($file);
