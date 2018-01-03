<?php
// /act/follow (follow/unfollow)
Flight::route('/act/follow', function(){
	Flight::requireLogin();
	$db = Flight::db();
	$user = Flight::user();
	
	$blog = $db->from('blogs')->where('name', $_GET['who'])->where('is_visible', 1)->select()->one();

	if (empty($blog) || empty($_GET['who'])) {
		Flight::notFound();
	}
	
	$friendshipExists = $db->from('friendships')->where('from_blog_id', $user['blog_id'])->where('to_blog_id', $blog['id'])->count() > 0;
	
	if ($friendshipExists) {
		// unfollow
		$db->from('friendships')
			->where('from_blog_id', $user['blog_id'])
			->where('to_blog_id', $blog['id'])
			->delete()
			->execute();
			
		$db->from('friendships')
			->where('from_blog_id', $blog['id'])
			->where('to_blog_id', $user['blog_id'])
			->update(['is_bilateral'=>0])
			->execute();
			
	} else {
		// follow
		$isBilateral = $db->from('friendships')->where('from_blog_id', $blog['id'])->where('to_blog_id', $user['blog_id'])->count();
	
		$db->from('friendships')
			->insert([
				'from_blog_id'=>$user['blog_id'],
				'to_blog_id'=>$blog['id'],
				'since'=>date('Y-m-d H:i:s'),
				'is_bilateral'=>$isBilateral
			])
			->execute();
			
		if ($isBilateral) {
			$db->from('friendships')
			->where('from_blog_id', $blog['id'])
			->where('to_blog_id', $user['blog_id'])
			->update(['is_bilateral'=>1])
			->execute();
		}	
	}
	
	Flight::redirect('/'.$blog['name']);
});

// /act/member (member/dismember)
Flight::route('/act/member', function(){
	Flight::requireLogin();
	$db = Flight::db();
	$user = Flight::user();
	
	$blog = $db->from('blogs')->where('name', $_GET['who'])->where('is_visible', 1)->select()->one();

	if (empty($blog) || empty($_GET['who'])) {
		Flight::notFound();
	}
	
	$membershipExists = $db->from('memberships')->where('member_id', $user['blog_id'])->where('blog_id', $blog['id'])->count() > 0;
	
	if ($membershipExists) {
		$db->from('memberships')->where('member_id', $user['blog_id'])->where('blog_id', $blog['id'])->delete()->execute();
	} else {
		$db->from('memberships')->insert([
			'member_id'=>$user['blog_id'],
			'blog_id'=>$blog['id'],
			'since'=>date('Y-m-d H:i:s')
		])->execute();
	}

	Flight::redirect('/'.$blog['name']);
});

// /act/settings/@blog
Flight::route('/act/settings/@name', function($name){
	Flight::requireLogin();
	$request = Flight::request();
	$db = Flight::db();
	$user = Flight::user();
	
	$blog = $db->from('blogs')->where('name', $name)->where('is_visible', 1)->select()->one();
	
	if (empty($blog)) {
		Flight::notFound();
	}
	
	if ($user['blog_id']!=$blog['id'] && !isset($user['groups'][$blog['id']])) {
		Flight::halt(403, 'Not authorized!');
	}
	
	$form = new severak\forms\form(['method'=>'post']);
	$form->field('title', ['label'=>'Blog title', 'required'=>true]);
	$form->field('about', ['label'=>'Blog description', 'type'=>'textarea', 'rows'=>6, 'required'=>true]);
	$form->field('is_nsfw', ['label'=>'is NSFW blog', 'type'=>'checkbox']);
	$form->field('upload', ['label'=>'Change logo', 'type'=>'file']);
	$form->field('save', ['label'=>'Save', 'type'=>'submit']);
	
	$form->fill($blog);
	
	if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
		$update['title'] = $_POST['title'];
		$update['about'] = $_POST['about'];
		$update['is_nsfw'] = isset($_POST['is_nsfw']) ? 1 : 0; // todo - podobnou konstrukci mít spíš ve formuláři
		
		$newPhoto = kyselo_upload_image($form, 'upload');
		if ($newPhoto) $update['avatar_url'] = $newPhoto;
		
		if ($form->isValid) {
			$db->from('blogs')->where('id', $blog['id'])->update($update)->execute();
			Flight::redirect('/'.$blog['name']);
		}
	}
	
	Flight::render('header', ['title' => $blog["title"] ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'settings'
	]);
	Flight::render('form', ['form'=>$form]);
	Flight::render('bookmarklet', ['blog'=>$blog]);
	echo '<script>new MediumEditor("textarea", {buttonLabels:"fontawesome", placeholder:{text:"text..."}, paste:{forcePlainText:false}, autoLink: true, toolbar: {buttons: ["bold", "italic", "anchor", "quote", "pre", "unorderedlist","orderedlist"]}});</script>';
	Flight::render('footer', []);
});
	
	

