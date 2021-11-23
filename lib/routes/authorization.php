<?php

use severak\database\rows;
const KYSELO_PASSWORD_ALG = PASSWORD_DEFAULT;

// famous 1-step registration process

Flight::route('/act/register', function() {
	if (!empty($_SESSION['user']['name'])) {
		Flight::redirect('/' . $_SESSION['user']['name'] . '/friends');
	}

	/** @var Sparrow $db */
	$db = Flight::db(); // TODO - refactor na rows
	$request = Flight::request();

	$form = new severak\forms\form(['method'=>'POST']);
	$form->field('username', ['label'=>'User name / URL', 'required'=>true]);
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
				'avatar_url'=> '/st/img/undraw_unicorn.png', // todo - zkusit tahat fotku z Gravataru
				'user_id' => $userId,
				'since' => date('Y-m-d H:i:s')
			])->execute();

			$blogId = $db->insert_id;

			$db->from('users')->update(['blog_id'=>$blogId])->where(['id'=>$userId])->execute();

			// autofollow on registration
            $db->from('friendships')
                ->insert([
                    'from_blog_id'=>$blogId,
                    'to_blog_id'=>1,
                    'since'=>date('Y-m-d H:i:s'),
                    'is_bilateral'=>0
                ])
                ->execute();

            // ping admin when someone registers
            $db->from('messages')
                ->insert([
                    'id_from'=>1,
                    'id_to'=>2,
                    'text'=>sprintf('SYSTEM: New user %s registered!', $form->values['username']),
                    'datetime'=>strtotime('now'),
                    'is_read'=>0
                ])
                ->execute();

            // TODO uvítací mail


			Flight::flash('Successfully registered. You can login now.');
			Flight::redirect('/act/login');
		}
	}

	Flight::render('header', ['title' => 'registration' ]);
	Flight::render('form', [
		'form' => $form,
	]);

	if (Flight::config('tos_post')) {
        /** @var rows $rows */
	    $rows = Flight::rows();
        $post = $rows->with('blogs', 'blog_id')->one('posts', Flight::config('tos_post'));
        $post['slug_name'] = $post['name'];
        echo '<hr>';
        Flight::render('posts', ['posts'=>[$post]]);
    }

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

					$groupsFromDb = $rows
						->with('memberships', 'id', 'blog_id', ['member_id'=>$blog['id'] ])
						->more('blogs', [], ['name'=>'asc']);


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
        'links' => [
            kyselo_url('/act/unlock') => 'forgotten password?'
        ]
	]);
	Flight::render('footer', []);
});

// password reset step 1
Flight::route('/act/unlock', function (){

    if (!empty($_SESSION['user']['name'])) {
        Flight::redirect('/' . $_SESSION['user']['name'] . '/friends');
    }

    $rows = Flight::rows();
    $request = Flight::request();

    $form = new severak\forms\form(['method'=>'POST']);
    $form->field('username', ['label'=>'User name', 'required'=>true]);
    $form->field('unlock', ['label'=>'reset password', 'type'=>'submit']);

    if ($request->method=='POST') {
        $form->fill($_POST);

        if ($form->validate()) {
            $blog = $rows->one('blogs', ['name' => $form->values['username']]);
            if (!empty($blog) && !$blog['is_spam'] && !$blog['is_group']) {

                $user = $rows->one('users', $blog['user_id']);
                $userName = $blog['name'];

                if ($user) {
                    $token = fCryptography::randomString(64);

                    $rows->update('users', [
                        'activation_token'=>$token,
                        'token_expires'=>strtotime('now + 1 day')
                    ], $user['id']);

                    $siteName = Flight::config('site_name');
                    $resetLink = kyselo_url('/act/unlocked',[], ['for'=>$userName, 'key'=>$token]);

                    $email = kyselo_email();
                    $email->addRecipient($user['email']);
                    $email->setSubject(sprintf('password reset for %s', Flight::config('site_name')));
                    $email->setBody("Hello $userName,
                    please click the following link to reset your password:
                    
                    $resetLink
                    
                    Your friendly bots from $siteName.
                    ", true);

                    $email->send();

                    Flight::flash('Reset link sent. Check your e-mail folder.');
                    Flight::redirect('/');

                }
            } else {
                $form->error('username', 'Username does not exist.');
            }

            if ($blog && $blog['is_group']) $form->error('username', 'No password resets for groups.');
        }
    }

    Flight::render('header', ['title' => 'password reset' ]);
    Flight::render('form', [
        //'h2'=>'password reset',
        'form' => $form,
        'links' => [
            kyselo_url('/act/login') => 'back to login'
        ]
    ]);
    Flight::render('footer', []);
});

// password reset step 2
Flight::route('/act/unlocked', function (){
    if (!empty($_SESSION['user']['name'])) {
        Flight::redirect('/' . $_SESSION['user']['name'] . '/friends');
    }

    $rows = Flight::rows();
    $req = Flight::request();

    $blog = $rows->one('blogs', ['name'=>$_GET['for'], 'is_group'=>0, 'is_visible'=>1]);
    $user = $rows->one('users', $blog['user_id']);

    if (!$blog || !$user) {
        Flight::flash('Not able to do password reset. Contact admins.', false);
        Flight::redirect('/');
    }

    if ($user['activation_token']!=$_GET['key'] || $user['token_expires']<strtotime('now')) {
        Flight::flash('Reset token got expired.', false);
        Flight::redirect('/');
    }

    $form = new severak\forms\form(['method'=>'POST']);
    $form->field('user_id', ['type'=>'hidden']);
    $form->field('password', ['label'=>'Password', 'type'=>'password', 'required'=>true]);
    $form->field('password_again', ['label'=>'and again', 'type'=>'password', 'required'=>true]);
    $form->field('reset', ['type'=>'submit']);

    $form->rule('password_again', function($password, $fields) {
        return $password==$fields['password'];
    }, 'Must match previous password.');


    if ($req->method=='POST' && $form->fill($_POST) && $form->validate()) {
        $rows->update('users', ['password'=>password_hash($form->values['password'], KYSELO_PASSWORD_ALG)], $user['id']);
        Flight::flash('Password has changed. You can login now.');
        Flight::redirect('/act/login');
    }

    Flight::render('header', ['title' => 'password reset' ]);
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
	session_destroy();
	Flight::redirect('/');
});

