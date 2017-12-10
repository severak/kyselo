<?php
// homepage
Flight::route('/', function(){
	Flight::render('header', array('title' => 'resoUp'));
	Flight::render('homepage');
	Flight::render('footer', []);
});

// everyone page
Flight::route('/all', function(){
	$db = Flight::db();
	
	$sel = $db
		->from('posts')
		->join('blogs', ['blog_id'=>'blogs.id'])
		->where('blogs.is_visible', 1)
		->where('posts.is_visible', 1);
	
	if (!empty($_GET['since'])) {
		$sel->where('datetime <= ', strtotime($_GET['since']) );
	}
	
	$sel->limit(31)
		->sortDesc('datetime')
		->select('posts.*, blogs.name as name, blogs.avatar_url');
	
	$posts = $sel->many();

	$moreLink = null;
	$theEnd = true;

	if (count($posts)==31) {
		$lastPost = array_pop($posts);
		$moreLink = '/all?since=' . date('Y-m-d\TH:i:s', $lastPost['datetime']);
		$theEnd = false;
	}

	Flight::render('header', ['title' => '{{ all soups }}' ]);
	Flight::render('posts', [
		'posts'=>$posts,
		'more_link'=>$moreLink,
		'the_end'=>$theEnd,
		'user' => Flight::user()
	]);
	Flight::render('footer', []);
});

// post detail
Flight::route('/@name/post/@postid', function($name, $postId){
	$db = Flight::db();

	$blog = $db->from('blogs')->where('name', $name)->where('is_visible', 1)->select()->one();

	if (empty($blog)) {
		Flight::notFound();
	}

	$sel = $db
		->from('posts')
		->join('blogs', ['blog_id'=>'blogs.id'])
		->where('posts.id', $postId)
		->where('posts.is_visible', 1);

	$sel->select('posts.*, blogs.name as name, blogs.avatar_url');

	$post = $sel->one();
	if (!$post) {
		Flight::notFound();
	}

	// todo: check if blog.id = posts.blog_id

	Flight::render('header', ['title' => $blog["title"] ]);
	Flight::render('blog_header', [
		'title'=> $blog['title'],
		'about'=> $blog['about'],
		'avatar_url'=> $blog['avatar_url']
	]);
	Flight::render('posts', [
		'posts'=> [$post],
		'blog' => $blog,
		'user' => Flight::user()
	]);
	Flight::render('footer', []);
});

// blog posts
Flight::route('/@name', function($name){
	$db = Flight::db();
	
	$blog = $db->from('blogs')->where('name', $name)->where('is_visible', 1)->select()->one();
	
	if (empty($blog)) {
		Flight::notFound();
	}
	
	$sel = $db
		->from('posts')
		->join('blogs', ['blog_id'=>'blogs.id'])
		->where('blog_id', $blog['id'])
		->where('posts.is_visible', 1);
	
	if (!empty($_GET['since'])) {
		$sel->where('datetime <= ', strtotime($_GET['since']) );
	}
	
	$sel->limit(31)
		->sortDesc('datetime')
		->select('posts.*, blogs.name as name, blogs.avatar_url');
	
	$posts = $sel->many();

	$moreLink = null;
	$theEnd = true;

	if (count($posts)==31) {
		$lastPost = array_pop($posts);
		$moreLink = '/' . $blog['name'] . '?since=' . date('Y-m-d\TH:i:s', $lastPost['datetime']);
		$theEnd = false;
	}

	Flight::render('header', ['title' => $blog["title"] ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'blog'
	]);
	Flight::render('posts', [
		'posts'=>$posts,
		'blog' => $blog,
		'user' => Flight::user(),
		'more_link'=>$moreLink,
		'the_end'=>$theEnd
	]);
	Flight::render('footer', []);
});

// /@blog/friends
Flight::route('/@name/friends', function($name){
	$db = Flight::db();
	
	$blog = $db->from('blogs')->where('name', $name)->where('is_visible', 1)->select()->one();
	
	if (empty($blog)) {
		Flight::notFound();
	}
	
	$sel = $db
		->from('posts')
		->join('blogs', ['blog_id'=>'blogs.id'])
		->join('friendships', ['friendships.to_blog_id'=>'blogs.id'])
		->where('friendships.from_blog_id', $blog['id'])
		->where('posts.is_visible', 1);
	
	if (!empty($_GET['since'])) {
		$sel->where('datetime <= ', strtotime($_GET['since']) );
	}
	
	$sel->limit(31)
		->sortDesc('datetime')
		->select('posts.*, blogs.name as name, blogs.avatar_url');
	
	$posts = $sel->many();

	$moreLink = null;
	$theEnd = true;

	if (count($posts)==31) {
		$lastPost = array_pop($posts);
		$moreLink = '/' . $blog['name'] . '/friends?since=' . date('Y-m-d\TH:i:s', $lastPost['datetime']);
		$theEnd = false;
	}

	Flight::render('header', ['title' => $blog["title"] ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'friends'
	]);
	Flight::render('posts', [
		'posts'=>$posts,
		'blog' => $blog,
		'user' => Flight::user(),
		'more_link'=>$moreLink,
		'the_end'=>$theEnd
	]);
	Flight::render('footer', []);
});

