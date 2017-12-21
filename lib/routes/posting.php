<?php
// /act/post
Flight::route('/act/post', function() {
	Flight::requireLogin();
	$db = Flight::db();
	$request = Flight::request();
	$user = Flight::user();
	$postType = isset($_GET['type']) ? $_GET['type'] : 1;
	
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
	
	$form->field('blog_id', ['type'=>'select', 'options'=>$canPostAs, 'label'=>'Post to']);
	
	$form->rule('blog_id', function($blog_id) use ($canPostAs){
		return !empty($canPostAs[$blog_id]);
	}, 'Cannot post as this user!');
	
	$form->field('type', ['type'=>'hidden', 'value'=>$postType]);
	
	//$form->field('type', ['type'=>'select', 'options'=>$postTypes]);
	
	if ($postType==1) {
		$form->field('title', ['placeholder'=>'title']);
		$form->field('body', ['type'=>'textarea', 'rows'=>5, 'cols'=>30, 'placeholder'=>'text...', 'label'=>'Text', 'required'=>true]);
	} elseif ($postType==2) {
		$form->field('source', ['required'=>true, 'label'=>'URL', 'placeholder'=>'http://example.org']);
		// todo: parse_url checking
		$form->field('title', ['placeholder'=>'title']);
		$form->field('body', ['type'=>'textarea', 'rows'=>5, 'cols'=>30, 'placeholder'=>'text...', 'label'=>'Text']);
	} elseif ($postType==3) {	
		$form->field('body', ['type'=>'textarea', 'rows'=>5, 'cols'=>30, 'placeholder'=>'text...', 'label'=>'Quote', 'required'=>true]);
		$form->field('title', ['placeholder'=>'Joe Doe', 'label'=>'by']);
	} elseif ($postType==4) {
		$form->field('upload', ['type'=>'file', 'label'=>'Upload']);
		$form->field('source', ['label'=>'OR download from', 'placeholder'=>'http://example.org/cat.jpg']);
		$form->field('body', ['type'=>'textarea', 'rows'=>5, 'cols'=>30, 'placeholder'=>'text...', 'label'=>'Description']);
	} elseif ($postType==5) {
		$form->field('source', ['label'=>'Video URL', 'placeholder'=>'https://www.youtube.com/watch?v=YT0k99hCY5I', 'required'=>true]);
	}
	
	$form->field('tags', ['label'=>'Tags']);
	$form->field('is_nsfw', ['type'=>'checkbox', 'label'=>'is NSFW']);
	$form->field('post', ['type'=>'submit', 'label'=>'Post it!']);
	
	if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
		$newPost = $form->values;
		
		unset($newPost['post'], $newPost['upload']);
		$newPost['author_id'] = $user['blog_id'];
		$newPost['guid'] = generate_uuid();
		$newPost['datetime'] = strtotime('now');
		
		if ($newPost['type']==4) {
			// photo uploading
			$newPhoto = kyselo_upload_image($form, 'upload');
			if ($newPhoto) {
				$newPost['url'] = $newPhoto;
			}
			if (!empty($newPost['source'])) {
				// todo: potom budeme stahovat a uchovávat si lokální kopii
				$newPost['url'] = $newPost['source'];
			}
			if (empty($newPost['url'])) $form->error('source', 'You have to upload photo or download it from outside.');
		}
		
		if ($newPost['type']==5) {
			$info = Embed\Embed::create($newPost['source']);
			if ($info->type=='video') {
				$newPost['url'] = $newPost['source'];
				$newPost['preview_html'] = $info->code;
				$newPost['body'] = $info->title;
			} else {
				$form->error('source', 'Not a valid video.');
			}
		}
		
		if ($newPost['type']>6) {
			$form->error('blog_id', 'Post type not yet implemented.');
		}
		
		if ($form->isValid) {
			$postId = Flight::rows()->insert('posts', $newPost);
			if (!empty($newPost['tags'])) {
				foreach (explode(' ', $newPost['tags']) as $tag) {
					Flight::rows()->insert('post_tags', ['blog_id'=>$newPost['blog_id'], 'post_id'=>$postId, 'tag'=>$tag]);
				}
			}
			Flight::redirect('/'.Flight::user('name'));
		}
	}
	
	Flight::render('header', ['title' => 'new post' ]);
	Flight::render('form', [
		'form' => $form,
	]);
	echo '<script>new MediumEditor("textarea", {buttonLabels:"fontawesome", placeholder:{text:"text..."}, paste:{forcePlainText:false}, autoLink: true, toolbar: {buttons: ["bold", "italic", "anchor", "quote", "pre", "unorderedlist","orderedlist"]}});</script>';	
	Flight::render('footer', []);
});


function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}	