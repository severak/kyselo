<?php
// /act/post
use severak\database\rows;

Flight::route('/act/post', function() {
	Flight::requireLogin();
	$rows = Flight::rows();
	$request = Flight::request();
	$user = Flight::user();
	$postType = 1;
	$hint = null;

	if (isset($_GET['type'])) {
		$postType = $_GET['type'];
	}

	if (isset($_POST['type'])) {
		$postType = $_POST['type'];
	}

	if (!empty($_GET['url'])) {
		$hint = get_info($_GET['url']);

		if ($hint->type=='video') {
			$postType = 5;
		} elseif ($hint->type=='photo') {
			$postType = 4;
		} else {
			$postType = 2;
		}
	}

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
	foreach ($_SESSION['user']['groups'] as $gId=>$group) {
		$canPostAs[$gId] = $group['name'];
	}

	$form = get_post_form($postType, $canPostAs);

	$form->rule('blog_id', function($blog_id) use ($canPostAs){
		return !empty($canPostAs[$blog_id]);
	}, 'Cannot post as this user!');

	if ($hint) {
		$linkUrl = $hint->url;
		if (strpos($_GET['url'], '#')!==false) {
			$linkUrl = $_GET['url'];
		}
		if ($hint->type=='video') {
			$form->fill(['source'=>$hint->url]);
		} elseif ($hint->type=='photo') {
			$form->fill(['source'=>$hint->image, 'from'=>$hint->url, 'body'=>(!empty($hint->description) ? '<blockquote>'.$hint->description.'</blockquote>' : '')]);
		} elseif (!empty($_GET['quote'])) {
			$form->fill(['source'=>$hint->url, 'title'=>$hint->title, 'body'=>'<blockquote>'.$_GET['quote'].'</blockquote>']);
		} else {
			$form->fill(['source'=>$linkUrl, 'title'=>$hint->title, 'body'=>'<blockquote>'.$hint->description.'</blockquote>']);
		}
	}

	if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
		$newPost = $form->values;
		unset($newPost['post'], $newPost['upload'], $newPost['csrf_token']);
		$newPost['blog_id'] = $newPost['blog_id'];
		$newPost['author_id'] = $user['blog_id'];
		$newPost['guid'] = generate_uuid();
		$newPost['datetime'] = strtotime('now');

		$newPost = finish_post($newPost, $form, true);

		if ($form->isValid) {
			$postId = $rows->insert('posts', $newPost);
			if (!empty($newPost['tags'])) {
				foreach (array_unique(explode(' ', $newPost['tags'])) as $tag) {
					$rows->insert('post_tags', ['blog_id'=>$newPost['blog_id'], 'post_id'=>$postId, 'tag'=>$tag]);
				}
			}
			Flight::redirect('/'.$canPostAs[ $newPost['blog_id'] ]);
		}
	}

	if ($_GET['as']) {
		$form->values['blog_id'] = array_search($_GET['as'], $canPostAs);
	}

	Flight::render('header', ['title' => 'new post' ]);
	Flight::render('form', [
		'form' => $form,
	]);
	Flight::render('footer', []);
});

// /act/post/edit/@id
Flight::route('/act/post/edit/@id', function($id){
	Flight::requireLogin();
	$rows = Flight::rows();
	$user = Flight::user();
	$request = Flight::request();

	$post = $rows->one('posts', $id);

	if (!$post) Flight::notFound();
	if (!can_edit_post($post)) Flight::forbidden();
	$blog = $rows->one('blogs', $post['blog_id']);

	$form = get_post_form($post['type']);
	if ($post['type']==4 && !empty($post['url']) && !empty($post['source'])) {
	    $post['from'] = $post['source'];
	    $post['source'] = '';
    }
	$form->fill($post);

	if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
		$newPost = $form->values;
		unset($newPost['post'], $newPost['upload'], $newPost['csrf_token']);

		$newPost = finish_post($newPost, $form, false);

		if ($form->isValid) {
			$rows->update('posts', $newPost, $id);
			$rows->delete('post_tags', ['post_id'=>$id]);
			if (!empty($newPost['tags'])) {
				foreach (array_unique(explode(' ', $newPost['tags'])) as $tag) {
					$rows->insert('post_tags', ['blog_id'=>$post['blog_id'], 'post_id'=>$id, 'tag'=>$tag]);
				}
			}
			Flight::redirect('/'.$blog['name'].'/post/'.$id);
		}
	}

	Flight::render('header', ['title' => 'new post' ]);
	Flight::render('form', [
		'form' => $form,
	]);
	Flight::render('footer', []);
});

// /act/post/delete/@id
Flight::route('/act/post/delete/@id', function($id){
	Flight::requireLogin();
	/** @var rows $rows */
	$rows = Flight::rows();
	$user = Flight::user();
	$request = Flight::request();

	$post = $rows
		->with('blogs', 'blog_id')
		->one('posts', $id);

	$blog = $rows->one('blogs', $post['blog_id']);

	if (!$post) Flight::notFound();

	// todo - možnost mazání postů pro správce a zakladatele skupin
	if (!can_edit_post($post)) Flight::forbidden();

	// docasny hack
	$post['slug_name'] = $post['name'];

	$form = new severak\forms\form(['method'=>'post']);
	$form->field('confirmed', ['type'=>'checkbox', 'label'=>'I want to delete this post permanently.']);
	kyselo_csrf($form);
	$form->field('delete', ['type'=>'submit']);

	if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
		if ($form->values['confirmed']) {
			$rows->update('posts', ['is_visible'=>0], $id);
            $rows->delete('post_tags', ['post_id'=>$id]);
            $rows->delete('reposts', ['repost_id'=>$id]);
		} else {
			Flight::flash('Nothing was deleted.', false);
		}
		Flight::redirect('/'.$blog['name']);
	}

	Flight::render('header', ['title' => 'delete post' ]);
	Flight::render('posts', ['posts'=>[$post], 'user'=>$user, 'blog'=>false, 'hideComments'=>true ]);
	Flight::render('form', [
		'form' => $form
	]);
	Flight::render('footer', []);
});

Flight::route('/act/repost', function(){
	$rows = Flight::rows();
	$user = Flight::user();

	if (empty($user)) {
		Flight::forbidden();
	}

	$request = Flight::request();

	$originalId = $_GET['post_id'];
	$blogId = $_GET['blog_id'];

	if (!($blogId==$user['id'] || isset($user['groups'][$blogId]) )) {
		Flight::forbidden();
	}

	$newPost = $rows->one('posts', ['id'=>$originalId, 'is_visible'=>1]);
	if (empty($newPost)) {
		Flight::notFound();
	}

	$newPost['repost_of'] = $originalId;
	unset($newPost['id']);
	$newPost['blog_id'] = $blogId;
	$newPost['author_id'] = $user['blog_id'];
	$newPost['guid'] = generate_uuid();
	$newPost['datetime'] = strtotime('now');
	$newPost['reposts_count'] = 0;
	$newPost['comments_count'] = 0;
	unset($newPost['tags']); // we don't wanna to copy tags to reposts

	$postId = $rows->insert('posts', $newPost);

	$rows->insert('reposts', ['post_id'=>$originalId, 'repost_id'=>$postId, 'reposted_by'=>$newPost['blog_id'] ]);
	$rows->execute($rows->fragment('UPDATE posts SET reposts_count=reposts_count+1 WHERE id=?', [$originalId]));

	if ($request->ajax) {
		echo 'OK ' . $postId;
	} else {
		$blog = $rows->one('blogs', $blogId);
		Flight::redirect('/'.$blog['name'].'/post/'.$postId);
	}
});

function get_post_form($postType, $canPostAs=null)
{
	$form = new severak\forms\form(['method'=>'post']);

	$form->field('type', ['type'=>'hidden', 'value'=>$postType]);

	if ($postType==1) {
		$form->field('title', ['placeholder'=>'title']);
		$form->field('body', ['type'=>'textarea', 'class'=>'kyselo-editor', 'rows'=>5, 'cols'=>30, 'placeholder'=>'text...', 'label'=>'Text', 'required'=>true]);
	} elseif ($postType==2) {
		$form->field('source', ['required'=>true, 'label'=>'URL', 'placeholder'=>'http://example.org']);
		// todo: parse_url checking
		$form->field('title', ['placeholder'=>'title']);
		$form->field('body', ['type'=>'textarea', 'class'=>'kyselo-editor', 'rows'=>5, 'cols'=>30, 'placeholder'=>'text...', 'label'=>'Text']);
	} elseif ($postType==3) {
		$form->field('body', ['type'=>'textarea', 'class'=>'kyselo-editor', 'rows'=>5, 'cols'=>30, 'placeholder'=>'text...', 'label'=>'Quote', 'required'=>true]);
		$form->field('title', ['placeholder'=>'Joe Doe', 'label'=>'by']);
	} elseif ($postType==4) {
		$form->field('upload', ['type'=>'file', 'label'=>'Upload']);
		$form->field('source', ['label'=>'OR download from', 'placeholder'=>'http://example.org/cat.jpg']);
		$form->field('body', ['type'=>'textarea', 'class'=>'kyselo-editor', 'rows'=>5, 'cols'=>30, 'placeholder'=>'text...', 'label'=>'Description']);
		$form->field('from', ['label'=>'From', 'placeholder'=>'http://example.org/funny-cat-picture']);
	} elseif ($postType==5) {
		$form->field('source', ['label'=>'Video URL', 'placeholder'=>'https://www.youtube.com/watch?v=YT0k99hCY5I', 'required'=>true]);
        $form->field('body', ['type'=>'textarea', 'class'=>'kyselo-editor', 'rows'=>5, 'cols'=>30, 'placeholder'=>'text...', 'label'=>'Description']);
	} else {
		throw new Exception('Not yet!');
	}

	$form->field('tags', ['label'=>'Tags']);
	$form->field('is_nsfw', ['type'=>'checkbox', 'label'=>'is NSFW']);
	if ($canPostAs)
		$form->field('blog_id', ['type'=>'select', 'label'=>'Post to', 'options'=>$canPostAs]);
	kyselo_csrf($form);
	$form->field('post', ['type'=>'submit', 'label'=>'Post it!']);

    $form->rule('body', function ($html) {return !detect_xss($html);}, 'Please don\'t hack us!');

	return $form;
}

function finish_post($newPost, $form, $required=true)
{
	if ($newPost['type']==4) {
		// photo uploading
		$newPhoto = kyselo_upload_image($form, 'upload');
		if ($newPhoto) {
			$newPost['url'] = $newPhoto;
		}
		// photo mirroring
		if (!empty($newPost['source'])) {
			$newPhoto = kyselo_download_image($form, 'source');
			if ($newPhoto) {
				$newPost['url'] = $newPhoto;
			}
		}

		if (empty($newPost['url']) && empty($newPost['source']) && $required) $form->error('source', 'You have to upload photo or download it from outside.');

        if (!empty($newPost['from'])) {
            $newPost['source'] = $newPost['from'];
        }
        unset($newPost['from']);
	}

	if ($newPost['type']==5) {
		$info = get_info($newPost['source']);
		if ($info->type=='video') {
			$newPost['url'] = $newPost['source'];
			$newPost['preview_html'] = $info->code;
			$newPost['title'] = $info->title;
		} elseif ($required) {
			$form->error('source', 'Not a valid video.');
		}
	}

	if ($newPost['type']>6) {
		$form->error('type', 'Post type not yet implemented.');
	}

	return $newPost;
}



