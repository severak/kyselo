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

Flight::register('db', 'sparrow', [], function($db) use($config) {
	$db->setDb('pdosqlite://localhost/' . __DIR__ . '/' . $config['database']);
	$db->show_sql = true;
});

require __DIR__ . '/lib/routes/blogs.php';
require __DIR__ . '/lib/routes/authorization.php';

Flight::start();