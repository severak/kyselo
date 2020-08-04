<?php
if (php_sapi_name()!='cli') {
	die('ERROR: This script is only for command line.');
}

if (file_exists(__DIR__ . '/config.php')) {
	die("ERROR \nKyselo already installed.\n");
}

require dirname(__FILE__) . "/lib/flight/autoload.php";
\flight\core\Loader::addDirectory(dirname(__FILE__) . '/lib' );
\flight\core\Loader::addDirectory(dirname(__FILE__) . '/lib/flourish' );

echo 'KYSELO SETUP ' . PHP_EOL;
echo '=== ' . PHP_EOL;

echo 'This script will set-up kyselo social network.' . PHP_EOL; 
echo 'Please, provide some info about your instance. ' . PHP_EOL;

echo '--- ' . PHP_EOL;

$conf['site_name'] = _readline('site name');
$conf['site_url'] = _readline('site url (including protocol)');
$adminEmail = _readline('admin e-mail');
$adminPw  = _readline('password');
$conf['secret'] = _readline('secret token');

$conf['adminer_password'] = md5($adminPw);
$conf['database'] = 'db/kyselo.sqlite';

echo '--- ' . PHP_EOL;

echo 'creating config file...' . PHP_EOL;
file_put_contents(__DIR__ . '/config.php',  '<?php return ' . var_export($conf, true) . ';');


$db = new medoo(array(
	'database_type' => 'sqlite',
	'database_file' => dirname(__FILE__) . '/' . $conf['database']
));

echo 'creating database...' . PHP_EOL;
$schema = file_get_contents(__DIR__  . '/doc/schema.sql');
$tables = explode(';', $schema);
foreach ($tables as $create) {
	$db->query($create);
}

echo 'creating admin account...' . PHP_EOL;

$userId = $db->insert('users', [
	'blog_id' => 0,
	'email' => $adminEmail,
	'password' => password_hash($adminPw, PASSWORD_DEFAULT),
	'is_active' => 1
]);

$blogId = $db->insert('blogs', [
	'name' => 'admin',
	'title' => 'admin\'s soup',
	'about' => '(owner of this site)',
	'avatar_url'=> '/st/johnny-automatic-horse-head-50px.png',
	'user_id' => $userId,
	'since' => date('Y-m-d H:i:s')
]);

$db->update('users', ['blog_id'=>$blogId], ['id'=> $userId]);

echo 'making first blogpost...' . PHP_EOL;

$db->insert('posts', [
	'blog_id' => $blogId,
	'author_id' => $blogId,
	'datetime' => strtotime('now'),
	'type' => 1,
	'title' => 'Congratulations',
	'body' => 'your own <em>Kyselo</em> is up and running'
]);

echo 'OK. Complete.' . PHP_EOL;

// TODO: here starting php server + browser opening??? we want it???

function _readline($prompt)
{
	echo $prompt . ': ' . PHP_EOL;
	return rtrim( fread(STDIN, 512) );
}