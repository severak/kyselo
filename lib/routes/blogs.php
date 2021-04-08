<?php
// homepage
Flight::route('/', function(){
	Flight::render('header', array('title' => 'Kyselo'));
	Flight::render('homepage');
	Flight::render('footer', []);
});

// everyone page
Flight::route('/all', function(){
	$db = Flight::db();

	$filter = new kyselo\timeline(Flight::rows());
	$filter->mode = 'all';
	$filter->filter($_GET);

	$posts = $filter->posts();
	$moreLink = $filter->moreLink;
	$theEnd = !$filter->moreLink;

	Flight::render('header', ['title' => sprintf('all from %s', Flight::config('site_name')), 'rss'=>'/all/rss'. $filter->currentParams ]);
    Flight::render('blog_header', [
        'blog'=>[
            'name'=>'all',
            'title'=>sprintf('all from %s', Flight::config('site_name')),
            'is_group'=>true,
            'id'=>-1,
            'about'=>'(but no reposts)',
            'avatar_url'=>'/st/img/undraw_different_love_a3rg.png'
        ],
        'user'=>Flight::user(),
        'tab'=>'blog',
        'rsslink'=>kyselo_url('/all/rss', [], $_GET)
    ]);
	Flight::render('posts', [
		'posts'=>$posts,
		'more_link'=>$moreLink,
		'the_end'=>$theEnd,
		'user' => Flight::user()
	]);
	Flight::render('buttons', [
        'blog'=>['name'=>'all', 'is_group'=>false],
        'user'=>Flight::user()
    ]);
	Flight::render('footer', []);
});

// everyone RSS
Flight::route('/all/rss', function(){
    $db = Flight::db();

    $filter = new kyselo\timeline(Flight::rows());
    $filter->mode = 'all';
    $filter->filter($_GET, false);

    $posts = $filter->posts();

    $rss = new kyselo\rss\generator();
    $rss->urlPrefix = kyselo_url('/');
    $rss->pathPrefix = Flight::rootpath();
    $rss->mode = 'all';
    $rss->tagged = $filter->tag;

    header('Content-type: text/xml');
    echo $rss->generate(['title'=>sprintf('all on %s', Flight::config('site_name')), 'about'=>'(but no reposts)', 'name'=>'all'], $posts);
});

// post detail
Flight::route('/@name/post/@postid', function($name, $postId){
	$db = Flight::db();

	$blog = $db->from('blogs')->where('name', $name)->where('is_visible', 1)->select()->one();

	if (empty($blog)) {
		Flight::notFound();
	}

	$filter = new kyselo\timeline(Flight::rows());
	$filter->mode = 'one';
	$filter->blogId = $blog['id'];
	$filter->postId = $postId;
	$posts = $filter->posts();

	if (empty($posts)) Flight::notFound();
	
	Flight::render('header', ['title' => $blog["title"], 'rss'=>sprintf('/%s/rss', $blog['name']), 'ogp_post'=>$posts[0] ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'blog',
        'subtab'=>'blog'
	]);
	Flight::render('posts', [
		'posts'=> $posts,
		'blog' => $blog,
		'user' => Flight::user()
	]);
    Flight::render('buttons', [
        'blog'=>$blog,
        'user'=>Flight::user()
    ]);
	Flight::render('footer', []);
});

// blog posts
Flight::route('/@name', function($name){
	$db = Flight::db();
	$rows = Flight::rows();
	
	$blog = $rows->one('blogs', ['name'=>$name, 'is_visible'=>1]);
	
	if (empty($blog)) {
		Flight::notFound();
	}

	$filter = new kyselo\timeline(Flight::rows());
	$filter->mode = 'own';
	$filter->blogId = $blog['id'];
	$filter->filter($_GET);

	$posts = $filter->posts();
	$moreLink = $filter->moreLink;
	$theEnd = !$filter->moreLink;

	Flight::render('header', ['title' => $blog["title"], 'rss'=>sprintf('/%s/rss%s', $blog['name'], $filter->currentParams), 'ogp_blog'=>$blog ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'blog',
        'subtab'=>'blog'
	]);
	Flight::render('posts', [
		'posts'=>$posts,
		'blog'=>$blog,
		'user' => Flight::user(),
		'more_link'=>$moreLink,
		'the_end'=>$theEnd
	]);
    Flight::render('buttons', [
        'blog'=>$blog,
        'user'=>Flight::user()
    ]);
	Flight::render('footer', []);
});

// /@blog/tags
Flight::route('/@name/tags', function($name){
	$rows = Flight::rows();
	
	$blog = $rows->one('blogs', ['name'=>$name, 'is_visible'=>1]);
	
	if (empty($blog)) {
		Flight::notFound();
	}
	
	$tags = $rows->execute($rows->fragment('select tag, count(*) as cnt
	from post_tags
	where blog_id=?
	group by tag
	order by count(*) desc', [$blog['id']]))->fetchAll(PDO::FETCH_KEY_PAIR);

	Flight::render('header', ['title' => $blog["title"] . ' - tags' ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'blog',
        'subtab'=>'tags'
	]);
	Flight::render('tags', [
		'tags'=>$tags,
		'blog'=>$blog
	]);
	Flight::render('footer', []);
});

// /@blog/rss
Flight::route('/@name/rss', function($name){
	$rows = Flight::rows();
	
	$blog = $rows->one('blogs', ['name'=>$name, 'is_visible'=>1]);
	
	if (empty($blog)) {
		Flight::notFound();
	}
	
	$filter = new kyselo\timeline(Flight::rows());
	$filter->blogId = $blog['id'];
	$filter->mode = 'own';
    $filter->filter($_GET, false);

	$posts = $filter->posts();

	$rss = new kyselo\rss\generator();
	$rss->urlPrefix = kyselo_url('/');
	$rss->pathPrefix = Flight::rootpath();
    $rss->tagged = $filter->tag;

	header('Content-type: text/xml');
	echo $rss->generate($blog, $posts);
});	

// /@blog/friends
Flight::route('/@name/friends', function($name){
	$db = Flight::db();
	$rows = Flight::rows();
	
	$blog = $db->from('blogs')->where('name', $name)->where('is_visible', 1)->select()->one();
	
	if (empty($blog)) {
		Flight::notFound();
	}
	
	$filter = new kyselo\timeline(Flight::rows());
	$filter->mode = 'friends';
	$filter->name = $name;
	$filter->blogId = $blog['id'];
	$filter->filter($_GET);

	$posts = $filter->posts();
	$moreLink = $filter->moreLink;
	$theEnd = !$filter->moreLink;

    $friends = [];
    if (empty($filter->since)) {
        $friends = $rows
            ->with('friendships', 'id', 'to_blog_id', ['from_blog_id'=>$blog['id']])
            ->more('blogs');
    }


	Flight::render('header', ['title' => $blog["title"] . ' - friends' ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'friends'
	]);
	Flight::render('posts', [
		'posts'=>$posts,
		'user' => Flight::user(),
		'blog'=>false,
		'more_link'=>$moreLink,
		'the_end'=>$theEnd,
        'friends'=>$friends
	]);
	Flight::render('footer', []);
});

Flight::route('/@name/videos', function($name){
    $db = Flight::db();
    $rows = Flight::rows();

    $blog = $rows->one('blogs', ['name'=>$name, 'is_visible'=>1]);

    if (empty($blog)) {
        Flight::notFound();
    }
    if (!$blog['has_videos']) {
        Flight::forbidden();
    }

    $filter = new kyselo\timeline(Flight::rows());
    $filter->mode = 'own';
    $filter->blogId = $blog['id'];
    $filter->filter($_GET);
    $filter->type = 'video';
    $filter->limit = 72;

    $posts = $filter->posts();

    Flight::render('header', ['title' => $blog["title"] . ' - videos', 'rss'=>sprintf('/%s/rss%s', $blog['name'], $filter->currentParams), 'ogp_blog'=>$blog ]);
    Flight::render('blog_header', [
        'blog'=>$blog,
        'user'=>Flight::user(),
        'tab'=>'blog',
        'subtab'=>'blog'
    ]);
    Flight::render('videos', [
        'posts'=>$posts
    ]);
    Flight::render('buttons', [
        'blog'=>$blog,
        'user'=>Flight::user()
    ]);
    Flight::render('footer', []);
});

Flight::route('/@name/journal', function($name){
    $db = Flight::db();
    $rows = Flight::rows();

    $blog = $rows->one('blogs', ['name'=>$name, 'is_visible'=>1]);

    if (empty($blog)) {
        Flight::notFound();
    }
    if (!$blog['has_journal']) {
        Flight::forbidden();
    }

    $filter = new kyselo\timeline(Flight::rows());
    $filter->mode = 'own';
    $filter->blogId = $blog['id'];
    $filter->filter($_GET);
    $filter->type = 'text';
    $filter->limit = 72;

    $posts = $filter->posts();

    Flight::render('header', ['title' => $blog["title"] . ' - journal', 'rss'=>sprintf('/%s/rss%s', $blog['name'], $filter->currentParams), 'ogp_blog'=>$blog ]);
    Flight::render('blog_header', [
        'blog'=>$blog,
        'user'=>Flight::user(),
        'tab'=>'blog',
        'subtab'=>'blog'
    ]);
    Flight::render('journal', [
        'posts'=>$posts
    ]);
    Flight::render('buttons', [
        'blog'=>$blog,
        'user'=>Flight::user()
    ]);
    Flight::render('footer', []);
});

Flight::route('/@name/custom.css', function($name){
    $rows = Flight::rows();

    $blog = $rows->one('blogs', ['name'=>$name, 'is_visible'=>1]);

    if (empty($blog)) {
        Flight::notFound();
    }

    header('Content-type: text/css');
    if (empty($blog['custom_css'])) {
        echo '/* ' . $blog['name'] . ' has no custom.css */';
    }
    echo $blog['custom_css'];
});

