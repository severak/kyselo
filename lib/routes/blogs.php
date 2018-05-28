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

	$filter = new kyselo\filter(Flight::rows());
	$filter->mode = 'all';
	if (!empty($_GET['since'])) {
		$filter->since = $_GET['since'];
	}

	$posts = $filter->posts();
	$moreLink = $filter->moreLink;
	$theEnd = !$filter->moreLink;

	Flight::render('header', ['title' => '{{ all blogs }}' ]);
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
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'blog'
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
	$rows = Flight::rows();
	
	$blog = $rows->one('blogs', ['name'=>$name, 'is_visible'=>1]);
	
	if (empty($blog)) {
		Flight::notFound();
	}
	
	$posts = blog_posts($rows, $blog, $_GET);

	$moreLink = null;
	$theEnd = true;

	if (count($posts)==31) {
		$lastPost = array_pop($posts);
		$query = array_intersect_key($_GET, ['since'=>true, 'date'=>true, 'type'=>true, 'tags'=>true]);
		$query['since'] = date('Y-m-d\TH:i:s', $lastPost['datetime']);
		$moreLink = '/' . $blog['name'] . '?' . http_build_query($query);
		$theEnd = false;
	}

	$filter = new kyselo\filter(Flight::rows());
	$filter->mode = 'own';
	if (!empty($_GET['since'])) {
		$filter->since = $_GET['since'];
	}
	if (!empty($_GET['tags'])) {
		$filter->tags = $_GET['tags'];
	}

	$posts = $filter->posts();
	$moreLink = $filter->moreLink;
	$theEnd = !$filter->moreLink;

	Flight::render('header', ['title' => $blog["title"] ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'blog'
	]);
	echo '<div class="pure-g"><div class="pure-u-1-5">&nbsp;</div><div class="pure-u-4-5"><a href="/'.$blog['name'].'/tags" style="text-decoration: none;">###</a><br><br></div></div>';
	Flight::render('posts', [
		'posts'=>$posts,
		'blog'=>$blog,
		'user' => Flight::user(),
		'more_link'=>$moreLink,
		'the_end'=>$theEnd
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
		'tab'=>'blog'
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
	
	$posts = blog_posts($rows, $blog, []);

	$filter = new kyselo\filter(Flight::rows());
	$filter->mode = 'own';

	$posts = $filter->posts();
	
	// https://www.mnot.net/rss/tutorial/
	
	header('Content-type: text/xml');
	$xw = new XMLWriter();
	$xw->openMemory();
	$xw->startDocument("1.0");
	$xw->startElement("rss");
	$xw->writeAttribute("version", "2.0");
	$xw->startElement("channel");
	$xw->writeElement("title", $blog['title']);
	$xw->writeElement("link", 'http://' . $_SERVER['SERVER_NAME'] . '/' . $blog['name']);
	$xw->writeElement("description", strip_tags($blog['about']));
	
	foreach ($posts as $post) {
		$xw->startElement("item");
		$xw->writeElement("title", !empty($post['title']) ? $post['title'] : '(no title)');
		$xw->writeElement("link", 'http://' . $_SERVER['SERVER_NAME'] . '/' . $blog['name'] . '/post/' . $post['id']);
		
		$desc = '';
		if ($post['type']==1 || $post['type']==3) {
			$desc = $post['body'];
		} elseif (in_array($post['type'], [2, 5, 6])) {
			// link, video, file
			$desc = '<a href="'.$post['url'].'">'.$post['url'].'</a>';
		} elseif ($post['type']==4) {
			$desc = '<img src="'.$post['url'].'">';
		}
		
		$xw->writeElement("description", $desc);
		$xw->writeElement("guid", $post['guid']);
		$xw->writeElement("pubDate", date('r', $post['datetime']));
		
		/*
		if ($post['type']==4) {
			$xw->startElement("enclosure");
			$xw->writeAttribute("url", $post['url']);
			$xw->writeAttribute("type", "image/jpeg");
			$xw->endElement();
		}
		*/
		
		$xw->endElement();
	}
	
	$xw->endElement();
	$xw->endElement();
	$xw->endDocument();
	echo $xw->outputMemory();
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

	$filter = new kyselo\filter(Flight::rows());
	$filter->mode = 'friends';
	if (!empty($_GET['since'])) {
		$filter->since = $_GET['since'];
	}
	if (!empty($_GET['tags'])) {
		$filter->tags = $_GET['tags'];
	}

	$posts = $filter->posts();
	$moreLink = $filter->moreLink;
	$theEnd = !$filter->moreLink;


	Flight::render('header', ['title' => $blog["title"] ]);
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
		'the_end'=>$theEnd
	]);
	Flight::render('footer', []);
});

function blog_posts($rows, $blog, $filter=[])
{
	$postsWhere = ['blog_id'=>$blog['id'], 'is_visible'=>1];
	
	if (!empty($filter['date'])) {
		$filter['since'] = $filter['date']; // backward compatibility with soup
	}
	
	if (!empty($filter['since'])) {
		$postsWhere[] = $rows->fragment('posts.datetime <= ?', [strtotime($filter['since'])]);
	}
	
	$type2code = ['text'=>1, 'link'=>2, 'quote'=>3, 'image'=>4, 'video'=>5, 'file'=>6, 'review'=>7, 'event'=>8];
	if (!empty($filter['type']) && $type2code[$filter['type']]) {
		$postsWhere['type'] = $type2code[$filter['type']];
	}
	
	if (!empty($filter['tags'])) {
		foreach (explode(',', $filter['tags']) as $tag) {
			$rows->with('post_tags', 'id', 'post_id',  ['blog_id'=>$blog['id'], 'tag'=>$tag]);
		}
	}
	
	return $rows
		->with('blogs', 'blog_id')
		->more('posts', $postsWhere, ['datetime'=>'desc'], 31);
}

