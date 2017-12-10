<?php
// /act/post
Flight::route('/act/post', function() {
	Flight::requireLogin();
	$db = Flight::db();
	$request = Flight::request();
	$user = Flight::user();
	
	$postTypes = [
		1 => 'text',
		'link',
		'quote',
		'image',
		'video',
		'file',
		'rating',
		'event'
	];
	
	$canPostAs = [];
	$canPostAs[$user['blog_id']] = $user['name'];
	// todo: groups
	
	$form = new severak\forms\form(['method'=>'post']);
	$form->field('post_to', ['type'=>'select', 'options'=>$canPostAs, 'label'=>'Post to']);
	$form->field('type', ['type'=>'select', 'options'=>$postTypes]);
	$form->field('title', []);
	$form->field('body', ['type'=>'textarea', 'rows'=>5, 'cols'=>30, 'label'=>'Text']);
	$form->field('url', ['label'=>'URL']);
	$form->field('upload', ['type'=>'file', 'label'=>'Attachment']);
	$form->field('tags', ['label'=>'Tags']);
	$form->field('is_nsfw', ['type'=>'checkbox', 'label'=>'is NSFW']);
	$form->field('post', ['type'=>'submit', 'label'=>'Post it!']);
	
	if ($request->method=='POST') {
		$form->fill($_POST);
		
		if (!isset($canPostAs[$_POST['post_to']])) {
			$form->error('post_to', 'Hacking attempt!'); // log it?
		}
		
		$newPost = [];
		$newPost['blog_id'] = $_POST['post_to'];
		$newPost['author_id'] = $user['blog_id'];
		$newPost['type'] = $_POST['type'];
		$newPost['guid'] = uniqid();
		$newPost['datetime'] = strtotime('now');
		
		if ($_POST['type']==1) {
			// text
			if (empty($_POST['body'])) $form->error('body', 'Cannot save empty text!');
			$newPost['body'] = $_POST['body'];
			$newPost['title'] = $_POST['title'];
		} else {
			$form->error('type', 'Not implemeted yet!');
		}
		
		if ($form->isValid) {
			$postOK = $db->from('posts')->insert($newPost)->execute();
			if ($postOK) {
				$postId = $db->insert_id;
				// todo: tagy
				Flight::redirect('/'.$canPostAs[$_POST['post_to']]);
			}
		}
	}
	
	Flight::render('header', ['title' => 'new post' ]);
	Flight::render('form', [
		'form' => $form,
	]);
	Flight::render('footer', []);
});	