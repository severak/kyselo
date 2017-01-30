<?php
if (empty($argv[1]) || empty($argv[2])) {
	die("ERROR \nBad import parameters. \nPlease specifiy: \n- RSS file \n- destination user ID\n");
}

if (!file_exists($argv[1])) {
	die("ERROR \nCannot open file: " . $argv[1] . "\n");
}

require dirname(__FILE__) . "/lib/brickyard.php";

$fw = new brickyard();
$fw->init();
$fw->inDevelMode = true;
$fw->throwTheseErrors = E_ALL ^ (E_WARNING | E_NOTICE); // nojo, php


$db = new medoo(array(
	'database_type' => 'sqlite',
	'database_file' => dirname(__FILE__) . '/data/kyselo.sqlite'
));

$import = new kyselo_mirror_soup($db);
echo "importing your feed...\n";
$import->importFeed($argv[1], $argv[2]);
echo "OK\n";
