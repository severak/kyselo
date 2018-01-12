<?php


// messages with someone
Flight::route('/act/messages/with/@name', function($name){
	Flight::requireLogin();
	$user = Flight::user();
	$rows = Flight::rows();
	$request = Flight::request();
	
	$with = $rows->one('blogs', ['name'=>$name]);
	if (!$with) {
		Flight::notFound();
	}
	$withId = $with['id'];
	
	$limit = max(30, $rows->count('messages', ['is_read'=>0, 'id_from'=>$withId]));
	
	$messages = $rows
		->with('blogs', 'id_from')
		->more('messages', $rows->fragment('(id_from=? OR id_to=?)', [$withId, $withId]), ['datetime'=>'DESC']);
	$messages = array_reverse($messages);	
	
	$form = new severak\forms\form(['method'=>'post']);
	$form->field('text', ['type'=>'textarea', 'label'=>'Your message', 'required'=>true, 'cols'=>30, 'rows'=>5]);
	$form->field('send', ['type'=>'submit']);
	
	if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
		// todo: upload fotek
		
		$rows->insert('messages', [
			'id_from'=>$user['blog_id'],
			'id_to'=>$with['id'],
			'text'=>$form->values['text'],
			'datetime'=>strtotime('now'),
			'is_read'=>0
		]);
		
		Flight::redirect('/act/messages/with/'.$with['name']);
	}
	
	Flight::render('header', ['title' => 'messages with ' .  $with['name']]);
	Flight::render('dialog', ['with'=>$with, 'messages'=>$messages]);
	Flight::render('form', ['form'=>$form]);
	Flight::render('footer', []);
});