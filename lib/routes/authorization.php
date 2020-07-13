<?php
const KYSELO_PASSWORD_ALG = PASSWORD_DEFAULT;

// famous 1-step registration process

Flight::route('/act/register', function() {
	if (!empty($_SESSION['user']['name'])) {
		Flight::redirect('/' . $_SESSION['user']['name'] . '/friends');
	}

	/** @var Sparrow $db */
	$db = Flight::db();
	$request = Flight::request();
	
	$form = new severak\forms\form(['method'=>'POST']);
	$form->field('username', ['label'=>'User name', 'required'=>true]);
	$form->field('email', ['label'=>'E-mail', 'type'=>'email', 'required'=>true]);
	$form->field('password', ['label'=>'Password', 'type'=>'password', 'required'=>true]);
	$form->field('password_again', ['label'=>'and again', 'type'=>'password', 'required'=>true]);
	
	// BIG TODO: wording of error messages
	if (Flight::config('invitation_code')) {
		$form->field('invitation_code', ['label'=>'Invitation code', 'placeholder'=>'xyzzy', 'required'=>true]);
		$form->rule('invitation_code', function($v){
			return in_array($v, Flight::config('invitation_code'));
		}, 'You cannot register without valid invitation code.');
	}
	
	$form->field('terms_agreement', ['label'=>'I agree with terms of service', 'type'=>'checkbox']);
	kyselo_csrf($form);
	$form->field('register', ['label'=>'Register new account', 'type'=>'submit']);
	// todo: catchpa - viz http://jecas.cz/recaptcha

	$form->rule('username', function($name) {
		$db = Flight::db();
		return $db->from('blogs')->where('name', $name)->count() == 0;
	}, 'Username already in use. Choose another.');
	
	$form->rule('username', function($name) {
		return preg_match('~^[a-z]([a-z0-9]{2,})$~', $name)===1;
	}, 'Bad username format: 3 or more lower case letters and numbers allowed, must start with letter.');
	
	// todo: validovat mail + posílat mailem ověření
	
	// todo: password sanity test

	$form->rule('password_again', function($password, $fields) {
		return $password==$fields['password'];
	}, 'Must match previous password.');
	
	$form->rule('terms_agreement', function($agreed){
		return !empty($agreed);
	}, 'You cannot use our service without terms agreement.');	

	if ($request->method=='POST') {
		$form->fill($_POST);
		
		if  ($form->validate()) {

			$db->from('users')->insert([
				'blog_id' => 0,
				'email' => $form->values['email'],
				'password' => password_hash($form->values['password'], KYSELO_PASSWORD_ALG),
				'is_active' => 1
			])->execute();

			$userId = $db->insert_id;

			$db->from('blogs')->insert([
				'name' => $form->values['username'],
				'title' => $form->values['username'],
				'about' => 'I am new here',
				'avatar_url'=> '/st/johnny-automatic-horse-head-50px.png', // todo - zkusit tahat fotku z Gravataru
				'user_id' => $userId,
				'since' => date('Y-m-d H:i:s')
			])->execute();

			$blogId = $db->insert_id;

			$db->from('users')->update(['blog_id'=>$blogId])->where(['id'=>$userId])->execute();

			Flight::flash('Successfully registered. You can login now.');
			Flight::redirect('/act/login');
		}
	}
	
	Flight::render('header', ['title' => 'registration' ]);
	Flight::render('form', [
		'form' => $form,
	]);
	Flight::render('footer', []);
});

// login
Flight::route('/act/login', function() {
	if (!empty($_SESSION['user']['name'])) {
		Flight::redirect('/' . $_SESSION['user']['name'] . '/friends');
	}

	$rows = Flight::rows();
	$request = Flight::request();
	
	$form = new severak\forms\form(['method'=>'POST']);
	$form->field('username', ['label'=>'User name', 'required'=>true]);
	$form->field('password', ['label'=>'Password', 'type'=>'password', 'required'=>true]);
	$form->field('persistent', ['label'=>'Keep me logged in', 'type'=>'checkbox']);
	kyselo_csrf($form);
	$form->field('login', ['label'=>'Login', 'type'=>'submit']);
	
	if (!empty($_GET['as'])) {
		$form->fill(['username'=>$_GET['as']]);
	}
	
	if ($request->method=='POST') {
		$form->fill($_POST);
		
		if ($form->validate()) {
			$blog = $rows->one('blogs', ['name'=>$form->values['username']]);
			if (!empty($blog)) {
				$user = $rows->one('users', $blog['user_id']);
				if (password_verify($_POST['password'], $user['password'])) {
					if ($form->values['persistent']) {
						fSession::enablePersistence();
					}
					
					$groupsFromDb = $rows
						->with('memberships', 'id', 'blog_id', ['member_id'=>$blog['id'] ])
						->more('blogs');
						
					
					$groups = [];
					foreach ($groupsFromDb as $group) {
						$groups[$group['id']] = [
							'id'=>$group['id'], 
							'name'=>$group['name'], 
							'title'=>$group['title'],
							'avatar_url'=>$group['avatar_url']
						];
					}
					
					$_SESSION['user'] = [
						'id' => $blog['id'],
						'name' => $blog['name'],
						'blog_id' => $blog['id'],
						'avatar_url' => $blog['avatar_url'],
						'groups'=> $groups
					];
					
					Flight::redirect('/' . $blog['name'] . '/friends');
				}
			}
		}
		$form->error('password', 'Bad login/password!');
	}

	Flight::render('header', ['title' => 'login' ]);
	Flight::render('form', [
		'form' => $form,
	]);
	Flight::render('footer', []);
});



// logout
Flight::route('/act/logout', function() {
	if (empty($_SESSION['user']['name'])) {
		Flight::redirect('/');
	}
	
	$_SESSION['user'] = false;
	fSession::destroy();
	Flight::redirect('/');
});

