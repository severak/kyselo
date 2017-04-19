<?php
use severak\rules as rulez;

// registration process

Flight::route('/act/register', function() {
	if (!fAuthorization::checkLoggedIn()) {
		// todo redirect na kamarády
	}
	
	$db = Flight::db();
	$request = Flight::request();
	
	$form = new severak\form;
	
	$form->rule('username', rulez::required(), 'Field is required.');
	$form->rule('email', rulez::required(), 'Field is required.');
	$form->rule('password', rulez::required(), 'Field is required.');
	
	$form->rule('username', function($name) {
		$db = Flight::db();
		return $db->from('blogs')->where('name', $name)->count() == 0;
	}, 'Name already in use.');
	
	// todo check if email is email
	
	if ($request->method=='POST') {
		$post = $request->data->getData();
		$form->values = $post;
		
		if  ($form->validate()) {
			// todo samotná registrace
		}
	}
	
	Flight::render('header', ['title' => 'registration' ]);
	Flight::render('registration', [
		'form' => $form,
	]);
	Flight::render('footer', []);
});

// login

// logout

// todo