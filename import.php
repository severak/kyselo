<?php
if (php_sapi_name()!='cli') {
	die('ERROR: This script is only for command line.');
}

if (empty($argv[1]) || empty($argv[2])) {
	die("ERROR \nBad import parameters. \nPlease specifiy: \n- RSS file \n- destination username\n");
}

if (!file_exists($argv[1])) {
	die("ERROR \nCannot open file: " . $argv[1] . "\n");
}

require dirname(__FILE__) . "/lib/flight/autoload.php";

\flight\core\Loader::addDirectory(dirname(__FILE__) . '/lib' );

$db = new medoo(array(
	'database_type' => 'sqlite',
	'database_file' => dirname(__FILE__) . '/data/kyselo.sqlite'
));

$blog = $db->get('blogs', '*', ['name'=>$argv[2]]);
if (!$blog) {
	echo 'Blog not found, creating it...' . PHP_EOL;
	$blogId = $db->insert('blogs', [
		'name' => $argv[2],
		'title' => $argv[2] . '\'s soup',
		'about' => '(imported from backup)',
		'avatar_url'=> '/st/johnny-automatic-horse-head-50px.png',
		'user_id' => 0
	]);

	if (!$blogId) {
		die("ERROR \nBlog not created.\n");
	}

	$blog = $db->get('blogs', '*', ['id'=>$blogId]);
}

$import = new kyselo_mirror_soup($db);
echo "importing your feed...\n";

$import->importFeed($argv[1], $blog['id']);
echo "OK\n";
