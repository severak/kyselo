<?php
require 'lib/flight/Flight.php';
require "lib/flight/autoload.php";

Flight::init();
Flight::set('flight.handle_errors', false);
Flight::set('flight.views.path', __DIR__ . '/lib/views');
require "lib/tracy/src/tracy.php";
use \Tracy\Debugger;
Debugger::enable();

flight\core\Loader::addDirectory("lib/flourish");

Flight::register('db', 'sparrow', [], function($db) {
	$db->setDb('pdosqlite://localhost/'.__DIR__.'/data/kyselo.sqlite');
	$db->show_sql = true;
});

Flight::route('/', function(){
	Flight::render('header', array('title' => 'resoUp'), 'header');
	Flight::render('header', [], 'footer');
	Flight::render('homepage');
});


Flight::start();