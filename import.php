<?php
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
$import->importFeed($argv[1], $argv[2]);
echo 'OK';
