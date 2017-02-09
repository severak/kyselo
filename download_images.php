<?php
if (php_sapi_name()!='cli') {
	die('ERROR: This script is only for command line.');
}

if (!file_exists(__DIR__ . '/config.php')) {
	die("ERROR \nKyselo not installed.\n");
}
$config = require __DIR__ . '/config.php';

if (empty($argv[1])) {
	die("ERROR \nBad import parameters. \nPlease specify: \n- destination username\n");
}

require dirname(__FILE__) . "/lib/flight/autoload.php";

\flight\core\Loader::addDirectory(dirname(__FILE__) . '/lib' );

$db = new medoo(array(
	'database_type' => 'sqlite',
	'database_file' => dirname(__FILE__) . '/' . $config['database']
));

$blog = $db->get('blogs', '*', ['name'=>$argv[1]]);

if (!$blog) {
	die("ERROR \nUser not found. ");
}

$postIds = $db->select('posts', 'id', ['blog_id'=>$blog['id'] ]);

echo 'Downloading your photos...' . PHP_EOL;

$downloaded = 0;

foreach ($postIds as $postId) {
	$post = $db->get('posts', '*', ['id'=>$postId]);
	if (!empty($post['url']) && in_array($post['type'], [4, 6, 7, 8])) {

		if (($downloaded % 15) == 0) {
			echo 'Taking a 3s pause to not grill soupcdn...' . PHP_EOL;
			sleep(3);
			echo 'Downloading more...' . PHP_EOL;
		}

		$relocated = relocate_image($post['url'], $blog['id']);
		if ($relocated) {
			$db->update('posts', ['url'=>$relocated], ['id'=>$postId]);
			$downloaded++;
		}

	}
}

echo 'OK. Complete. Downloaded ' . $downloaded . ' files.' . PHP_EOL;

function relocate_image($url, $blogId)
{
	if (substr($url, 0, 4)!='http') {
		return false;
	}
	$image = file_get_contents($url);
	if (!$image) {
		return false;
	}
	$url_md5 = md5($url);
	$newUri = '/pub/u'.$blogId.'/backup/' . substr($url_md5, 0, 2) . '/' . substr($url_md5, 2, 2) . '/'. $url_md5 . '.'. pathinfo($url, PATHINFO_EXTENSION);
	$dirPrefix = __DIR__;
	if (!is_dir($dirPrefix . dirname($newUri))) {
		mkdir($dirPrefix . dirname($newUri), 0777, true);
	}

	if (file_put_contents($dirPrefix.$newUri, $image)) {
		return $newUri;
	}
	return false;
}