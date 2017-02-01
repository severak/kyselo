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

Flight::route('/@name', function($name){
	$db = Flight::db();
	
	$blog = $db->from('blog')->where('name', $name)->select()->one();
	
	if (empty($blog)) {
		Flight::notFound();
	}
	
	$sel = $db
		->from('post')
		->where('blog_id', $blog['id']);
	
	if (!empty($_GET['since'])) {
		// dump( strtotime($_GET['since']));
		$sel->where('datetime <= ', $_GET['since'] );
	}
	
	$sel->limit(31)
		->sortDesc('datetime')
		->select();
	
	// dump($sel->sql());
	
	$posts = $sel->many();

	Flight::render('header', ['title' => $blog["title"] ], 'header');
	Flight::render('footer', [], 'footer');
	Flight::render('blog', ['blog_name'=>$name, 'blog_title' => $blog["title"], 'blog_about'=>$blog['about'], 'blog_image'=>$blog['photo_url'], 'posts'=>$posts ]);
});

Flight::start();