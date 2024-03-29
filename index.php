<?php
if (!file_exists(__DIR__ . '/config.php')) {
	die("ERROR: Kyselo not installed.");
}
$config = require __DIR__ . '/config.php';

if (!empty($config['maintance_mode'])) {
    header("HTTP/1.0 500 Server Error");
    echo file_get_contents(__DIR__ . '/lib/views/maintance.html');
    exit;
}

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

function kyselo_start_session()
{
    if (session_status() != PHP_SESSION_ACTIVE) {
        session_start();
    }
}

// start session
session_name('kyselo');
session_set_cookie_params(14 * 24 * 60 * 60); // 14 days
ini_set('session.gc_maxlifetime', 14 * 24 * 60 * 60);
if (isset($_COOKIE[session_name()])) {
    session_start();
}

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
    kyselo_start_session();
	$_SESSION['flash'][] = ['msg'=>$msg, 'class'=>$success ? 'success' : 'error'];
});

Flight::map('requireLogin', function() {
	if (!Flight::user()) Flight::redirect('/act/login');
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
            ->status(403)
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
			'image/png',
            'image/webp'
		),
		'The file uploaded is not an image.'
	);
	$uploader->setMaxSize('5MB');
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
        chmod($prefix . $md5_path, 0755);
		return $md5_path;
	}
	$form->error($name, 'File download error!');
	return null;
}

function kyselo_mirror_image($url)
{
    $tmpDir = Flight::rootpath() . '/tmp';
    if (empty($url)) {
        return null; // empty download throws no error
    }
    $tmpName = tempnam($tmpDir, 'download');
    $content = @file_get_contents($url);
    $bytes = file_put_contents($tmpName, $content);
    if ($bytes===false || $bytes==0) {
        return null;
    }
    $md5 = md5_file($tmpName);
    try {
        $image = new fImage($tmpName);
    } catch (fValidationException $e) {
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
        chmod($prefix . $md5_path, 0755);
        return $md5_path;
    }
    return null;
}

function kyselo_small_image($path, $size, $square=false)
{
    if (empty($path)) return '';
    $prefix = Flight::rootpath();
    $smallPath = str_replace('.', '_'.($square ? 'q' : 'w'). $size . '.', $path);
    if (file_exists($prefix . $smallPath)) return $smallPath;

    copy($prefix . $path, $prefix . $smallPath);
    $smallImage = new fImage($prefix . $smallPath);

    if ($square) {
        $smallerSize = min($smallImage->getWidth(), $smallImage->getHeight());
        $smallImage->crop($smallerSize, $smallerSize, 'center', 'center');
    }

    $smallImage->resize($size, 0);
    $smallImage->saveChanges();
    return $smallPath;
}

function kyselo_csrf($form)
{
    return; // vypneme, nefunguje
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

function kyselo_url($path='/', $args=[], $query=[])
{
    return
        rtrim(Flight::config('site_url'), '/') .
        ($args ? vsprintf($path, $args) : $path).
        ($query ? ('?' . http_build_query($query)) : '');
}

function make_links_clickable($text)
{
    return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1">$1</a>', $text);
}

function make_usernames_clickable($text)
{
    return preg_replace('~@([a-z][a-z0-9]{2,})([ ])~', '<a href="$1">@$1</a>$2', $text. ' ');
}

function find_usernames($text)
{
    $matches = [];
    preg_match_all('~@([a-z][a-z0-9]{2,})([ ])~', $text. ' ', $matches);
    return $matches[1];
}

function kyselo_markup($text)
{
    $html = esc($text);
    $html = make_links_clickable($html);
    $html = make_usernames_clickable($html);
    return nl2br($html);
}

function esc($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function kyselo_email()
{
    if (empty($_ENV['HOST'])) $_ENV['HOST'] = parse_url(Flight::config('site_url'), PHP_URL_HOST); // fixes some ENV bugs
    if ($_ENV['HOST']==='localhost') $_ENV['HOST'] = 'localhost.example.org';
    $email = new fEmail();
    $email->setFromEmail('noreply@' . $_ENV['HOST']);
    return $email;
}

function can_edit_post($post)
{
    $user = Flight::user();
    if (!$user) return false;
    if ($user['id']==1) return true; // admin can edit everything
    return $post['author_id']==$user['blog_id'];
}

function detect_xss($html)
{
    $html = strtolower($html);
    if (strpos($html, '</style')!==false) return true;
    if (strpos($html,'<script')!==false) return true;
    // if (strpos($html,'javascript:')!==false) return true;
    return false; // probably not XSS
}

function get_info($url)
{
    $info = kyselo\embed::embed($url);
    if ($info) return $info;

    $cookieJar = str_replace('//', '/', Flight::rootpath().'/tmp/embed-cookies.'.uniqid());
    $CURL = new Embed\Http\CurlDispatcher([CURLOPT_COOKIEJAR=>$cookieJar]);
    return Embed\Embed::create($url, null, $CURL);
}

function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function count_pph($posts)
{
    $postCount = count($posts);
    if ($postCount==0) {
        return '0 posts per hour';
    }

    $startTime = date_create('@' . $posts[0]['datetime']);
    $endTime = date_create('@' . $posts[count($posts)-1]['datetime']);

    if ($startTime && $endTime) {
        $timeDiff = $startTime->diff($endTime);

        if ($timeDiff->days < 1) {
            return '<i class="fa fa-rocket"></i> ' . number_format($postCount / max(1, $timeDiff->h), 2) . ' posts per hour';
        } else if ($timeDiff->days < 30) {
            return '<i class="fa fa-car"></i> ' . number_format($postCount / $timeDiff->days, 2) . ' posts per day';
        } else {
            return '<i class="fa fa-bicycle"></i> ' . number_format($postCount / ($timeDiff->days/30), 2) . ' posts per month';
        }
    }

    return '? posts per hour';
}

// routes:

require __DIR__ . '/lib/routes/blogs.php';
require __DIR__ . '/lib/routes/authorization.php';
require __DIR__ . '/lib/routes/posting.php';
require __DIR__ . '/lib/routes/other.php';
require __DIR__ . '/lib/routes/messages.php';
require __DIR__ . '/lib/routes/comments.php';
require __DIR__ . '/lib/routes/groups.php';
require __DIR__ . '/lib/routes/interop.php';
require __DIR__ . '/lib/routes/backup.php';
require __DIR__ . '/lib/routes/test.php';

Flight::start();
