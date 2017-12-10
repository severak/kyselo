<?php
if (!file_exists(__DIR__ . '/config.php')) {
	die("ERROR: Kyselo not installed.");
}
$config = require __DIR__ . '/config.php';

require 'lib/flight/Flight.php';
require "lib/flight/autoload.php";

Flight::init();
Flight::set('flight.handle_errors', false);
Flight::set('flight.views.path', __DIR__ . '/lib/views');
require "lib/tracy/src/tracy.php";
use \Tracy\Debugger;
Debugger::enable();

flight\core\Loader::addDirectory("lib/flourish");

// start session
session_start();

Flight::map('rootpath', function() {
	return __DIR__;
});

Flight::map('config', function() {
	global $config;
	return $config;
});

Flight::map('user', function($property=null) {
	if (!empty($_SESSION['user'])) {
		if ($property) {
			return $_SESSION['user'][$property];
		}
		return $_SESSION['user'];
	}
	return null;
});

Flight::map('requireLogin', function() {
	if (!Flight::user()) Flight::redirect('/act/login');
});

Flight::register('db', 'sparrow', [], function($db) use($config) {
	$db->setDb('pdosqlite://localhost/' . __DIR__ . '/' . $config['database']);
	$db->show_sql = true;
});

function kyselo_upload_image($form, $name)
{
	$uploader = new fUpload();
	$uploader->setMIMETypes(
		array(
			'image/gif',
			'image/jpeg',
			'image/pjpeg',
			'image/png'
		),
		'The file uploaded is not an image.'
	);
	$uploader->setMaxSize('2MB');
	$uploader->setOptional();
		
	$uploaderError = $uploader->validate($name, true);
	if ($uploaderError) {
		$form->error($name, $uploaderError);
	} elseif (!empty($_FILES[$name]['tmp_name'])) {
		$md5 = md5_file($_FILES[$name]['tmp_name']);
		$image = new fImage($_FILES[$name]['tmp_name']);
		$md5_path = '/pub/' . substr($md5, 0, 2) . '/' . substr($md5, 2, 2) . '/' . substr($md5, 4, 2) . '/' . $md5 . '.'. $image->getType();
		$prefix = Flight::rootpath();
		$dirname = pathinfo($md5_path, PATHINFO_DIRNAME);
		if (!is_dir($prefix. $dirname)) {
			mkdir($prefix . $dirname, 0777, true);
		}
		if (move_uploaded_file($_FILES[$name]['tmp_name'], $prefix . $md5_path)) {
			return $md5_path;
		}
	}
	return null;
}

require __DIR__ . '/lib/routes/blogs.php';
require __DIR__ . '/lib/routes/authorization.php';
require __DIR__ . '/lib/routes/posting.php';
require __DIR__ . '/lib/routes/other.php';

Flight::start();