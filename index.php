<?php
if (!file_exists(__DIR__ . '/config.php')) {
	die("ERROR: Kyselo not installed.");
}
$config = require __DIR__ . '/config.php';

// init framework
require 'lib/flight/Flight.php';
require "lib/flight/autoload.php";

Flight::init();
Flight::set('flight.handle_errors', false);
Flight::set('flight.views.path', __DIR__ . '/lib/views');

// init debugger
require "lib/tracy/src/tracy.php";
use \Tracy\Debugger;
Debugger::enable(!empty($config['show_debug']) ? Debugger::DEVELOPMENT : Debugger::DETECT);
Debugger::$showBar = false;
Debugger::$errorTemplate = __DIR__ . '/lib/views/500.htm';

// init flourish
flight\core\Loader::addDirectory("lib/flourish");

// start session
fSession::setPath(__DIR__ . '/tmp/session');
fSession::setLength('30 minutes', '3 days');
fSession::open();

// global helpers:
Flight::map('rootpath', function() {
	return __DIR__;
});

Flight::map('config', function($property=null) {
	global $config;
	if ($property) {
		return isset($config[$property]) ? $config[$property] : null;
	}
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

Flight::map('flash', function($msg, $success=true) {
	$_SESSION['flash'][] = ['msg'=>$msg, 'class'=>$success ? 'success' : 'error'];
});

Flight::map('requireLogin', function() {
	if (!Flight::user()) Flight::redirect('/act/login');
});

Flight::register('db', 'sparrow', [], function($db) use($config) {
	$db->setDb('pdosqlite://localhost/' . __DIR__ . '/' . $config['database']);
	$db->show_sql = true;
});

Flight::map('rows', function() use($config) {
	static $rows = null;
	if (!$rows) {
		$pdo = new PDO('sqlite:' . __DIR__ . '/' .  $config['database'], null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
		$rows = new severak\database\rows($pdo);
	}
	return $rows;
});

Flight::map('notFound', function(){
	Flight::response(false)
            ->status(404)
            ->write(
                file_get_contents(__DIR__ . '/lib/views/404.htm')
            )
            ->send();
});

Flight::map('forbidden', function(){
	Flight::response(false)
            ->status(404)
            ->write(
                file_get_contents(__DIR__ . '/lib/views/403.htm')
            )
            ->send();
});

function kyselo_upload_image($form, $name)
{
	$uploader = new fUpload();
	$uploader->setMIMETypes(
		array(
			'image/gif',
			'image/jpeg',
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
		if (file_exists($prefix . $md5_path)) {
			return $md5_path; // file exists already, no need to rewrite it
		}
		$dirname = pathinfo($md5_path, PATHINFO_DIRNAME);
		if (!is_dir($prefix. $dirname)) {
			mkdir($prefix . $dirname, 0777, true);
		}
		if (move_uploaded_file($_FILES[$name]['tmp_name'], $prefix . $md5_path)) {
			return $md5_path;
		}
		$form->error($name, 'File upload error!');
	}
	return null;
}

function kyselo_download_image($form, $name)
{
	$tmpDir = Flight::rootpath() . '/tmp';
	$url = $form->values[$name];
	if (empty($url)) {
		return null; // empty download throws no error
	}
	$tmpName = tempnam($tmpDir, 'download');
	$content = @file_get_contents($url);
	$bytes = file_put_contents($tmpName, $content);
	if ($bytes===false || $bytes==0) {
		$form->error($name, 'Cannot download this file.');
		return null;
	}
	$md5 = md5_file($tmpName);
	try {
		$image = new fImage($tmpName);
	} catch (fValidationException $e) {
		$form->error($name, 'This is not valid image');
		return null;
	}
	$md5_path = '/pub/' . substr($md5, 0, 2) . '/' . substr($md5, 2, 2) . '/' . substr($md5, 4, 2) . '/' . $md5 . '.'. $image->getType();
	$prefix = Flight::rootpath();
	if (file_exists($prefix . $md5_path)) {
		return $md5_path; // file exists already, no need to rewrite it
	}
	$dirname = pathinfo($md5_path, PATHINFO_DIRNAME);
	if (!is_dir($prefix. $dirname)) {
		mkdir($prefix . $dirname, 0777, true);
	}
	if (rename($tmpName, $prefix. $md5_path)) {
		return $md5_path;
	}
	$form->error($name, 'File download error!');
	return null;
}

function kyselo_csrf($form)
{	
	$form->field('csrf_token', ['type'=>'hidden', 'value'=>fRequest::generateCSRFToken()]);
	
	$form->rule('csrf_token', function($token) use ($form) {
		try {
			fRequest::validateCSRFToken($token);
		} catch (fExpectedException $e) {
			return false;
		}
		return true;
	}, 'Invalid CSRF token!');
}

// routes:

require __DIR__ . '/lib/routes/blogs.php';
require __DIR__ . '/lib/routes/authorization.php';
require __DIR__ . '/lib/routes/posting.php';
require __DIR__ . '/lib/routes/other.php';
require __DIR__ . '/lib/routes/messages.php';
require __DIR__ . '/lib/routes/test.php';

Flight::start();