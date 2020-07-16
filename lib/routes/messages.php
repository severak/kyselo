<?php
// inbox
Flight::route('/act/messages/inbox', function(){
	Flight::requireLogin();
	$user = Flight::user();
	/** @var severak\database\rows $rows */
	$rows = Flight::rows();
	$request = Flight::request();

	$messages = $rows->execute($rows->fragment('SELECT m.*, b.name, b.avatar_url
	FROM messages m
	INNER JOIN blogs b ON m.id_from=b.id
	WHERE id_to=?
	GROUP BY id_from
	HAVING MAX(datetime)
	ORDER BY datetime DESC
	', [ $user['blog_id'] ]))->fetchAll(PDO::FETCH_ASSOC);

	Flight::render('header', ['title' => 'inbox']);
	Flight::render('inbox', ['messages'=>$messages]);
	Flight::render('footer', []);
});	

// outbox
Flight::route('/act/messages/outbox', function(){
	Flight::requireLogin();
	$user = Flight::user();
	/** @var severak\database\rows $rows */
	$rows = Flight::rows();
	$request = Flight::request();

	$messages = $rows->execute($rows->fragment('SELECT m.*, b.name, b.avatar_url
	FROM messages m
	INNER JOIN blogs b ON m.id_to=b.id
	WHERE id_from=?
	GROUP BY id_to
	HAVING MAX(datetime)
	ORDER BY datetime DESC', [ $user['blog_id'] ]))->fetchAll(PDO::FETCH_ASSOC);

	Flight::render('header', ['title' => 'inbox']);
	Flight::render('inbox', ['messages'=>$messages, 'outbox'=>true]);
	Flight::render('footer', []);
});

// messages with someone
Flight::route('/act/messages/with/@name', function($name){
	Flight::requireLogin();
	$user = Flight::user();
	/** @var severak\database\rows $rows */
	$rows = Flight::rows();
	$request = Flight::request();
	
	$with = $rows->one('blogs', ['name'=>$name]);
	if (!$with) {
		Flight::notFound();
	}
	$myId = $user['blog_id'];
	$withId = $with['id'];
	
	// todo: this does not make much sense:
	$limit = max(30, $rows->count('messages', ['is_read'=>0, 'id_from'=>$withId]));
	
	$messages = $rows
		->with('blogs', 'id_from')
		->more('messages', $rows->fragment('(id_from=? AND id_to=?) OR (id_from=? AND id_to=?)', [$myId, $withId, $withId, $myId]), ['datetime'=>'DESC']);
	$messages = array_reverse($messages);
	
	$rows->update('messages', ['is_read'=>1], ['id_from'=>$withId, 'id_to'=>$myId]);
	
	$form = new severak\forms\form(['method'=>'post']);
	$form->field('text', ['type'=>'textarea', 'label'=>'Your message', 'required'=>true, 'cols'=>30, 'rows'=>5]);
	$form->field('send', ['type'=>'submit']);
	
	if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
		// todo: upload fotek
		
		$rows->insert('messages', [
			'id_from'=>$myId,
			'id_to'=>$withId,
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