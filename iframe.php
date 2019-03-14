<?php
// params
$blog = isset($_GET['blog']) ? $_GET['blog'] : 'admin';
$limit = !empty($_GET['limit']) ? (int) $_GET['limit'] : 5;
if ($limit >100) $limit=100;

// vars
$message = false;
$posts = [];
$title = 'Kyselo IFRAME';

// db
ini_set('display_errors', 1);
$db = new PDO('sqlite:'.__DIR__.'/db/kyselo.sqlite');
$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

// main logic
$stmt = $db->prepare('SELECT b.*, p.*, NULL as group_name, b.name AS slug_name
FROM posts p 
INNER JOIN blogs b ON p.blog_id=b.id AND  b.name=?
ORDER BY datetime DESC LIMIT ' . ($limit + 1));

if ($stmt->execute( [$blog] )) {
	$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
	if (count($posts)==0) {
		$message = ['class'=>'error', 'msg'=> 'Blog not found.'];
	}
	
	if (count($posts)==($limit +1) ) {
		$lastPost = array_pop($posts);
		$moreParams = ['since'=>date('Y-m-d\TH:i:s', $lastPost['datetime'])];
		$more_link = '/' . $lastPost['slug_name'] . '?' . http_build_query($moreParams);
	}
	
	$title = $posts[0]['name'];
} else {
	$message = ['class'=>'error', 'msg'=> 'Database problems.'];
}
?>
<!doctype html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?= $title; ?></title>
	<link rel="stylesheet" href="/st/css/pure/pure.css">
	<link rel="stylesheet" href="/st/css/font-awesome/css/font-awesome.css">
	<link rel="stylesheet" href="/st/css/kyselo/kyselo.css?v=2018-01-11">
</head>
<body>
<div class="kyselo-content">
<?php

if ($message) {
	echo '<div class="kyselo-message kyselo-message-'.$message['class'].'">'.htmlspecialchars($message['msg']).'</div>';
}

// c&p from kyselo itself
// only target="_blank added"

// posts listing / post detail
// arguments:
// - $posts
// - $blog
// - $more_link

$icons = ['', 'book', 'link', 'paragraph', 'camera', 'youtube-play', 'file', 'star', 'calendar'];

$showFullVideo = count($posts) < 6;

foreach ($posts as $post) {
$nsfwClass = $post['is_nsfw'] ? 'is-nsfw' : '';	
?>
<div class="pure-g">
	<div class="pure-u-1-5">
		<i class="fa fa-<?php echo $icons[$post['type']]; ?> fa-3x"></i>
	</div>
	<div class="pure-u-4-5 <?=$nsfwClass; ?>">
	<div>
		<img src="<?php echo $post['avatar_url']; ?>" style="width: 1em"> <a href="/<?php echo $post['name']; ?>" target="_blank"><?php echo $post['name']; ?></a>
		<small><?php echo date('j.n.Y H:i:s', $post['datetime']); ?></small>
		<?php if ($post['group_name']) { ?>
		<br>in <img src="<?php echo $post['group_avatar_url']; ?>" style="width: 1em"> <a href="/<?php echo $post['group_name']; ?>"><?php echo $post['group_name']; ?></a>
		<?php } ?>
	</div><br>
	<?php 
	if ($post['type']==1) { // text
		if (!empty($post['title'])) {
			echo '<h2>' . $post['title'] . '</h2>';
		} 
		echo $post['body'];
	} else if ($post['type']==2) { // link	
		echo '<a href="' . $post['source'] . '">' . $post['title'] . '</a>';
		if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		}
	} else if ($post['type']==3) { // quote
		echo '<blockquote>'. $post['body'] .'<br/> &mdash; ' . $post['title'] . '</blockquote>';
		if (!empty($post['source'])) {
			echo '<p>from <a href="' . $post['source'] . '">' . $post['source'] . '</a></p>';
		}
	} else if ($post['type']==4) { // image
		echo '<img class="pure-img" src="' . $post['url'] . '"/>';
		if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		}
		if (!empty($post['source'])) {
			echo '<p>from <a href="' . $post['source'] . '">' . $post['source'] . '</a></p>';
		}
	} else if ($post['type']==5) { // video
		if (!empty($post['preview_html'])) {
			echo '<div class="kyselo-video" data-id="'.$post['id'].'">';
			if ($showFullVideo) {
				echo $post['preview_html'];
			} else {
				echo '<a href="' . $post['source'] . '" target="_blank" class="kyselo-play-video pure-button button-large"><i class="fa fa-youtube-play"></i><span class="kyselo-hidden"> play video</span></a>';
			}
			echo '</div>';
		}
		if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		}
		if (!empty($post['source'])) {
			echo '<p>from <a href="' . $post['source'] . '">' . $post['source'] . '</a></p>';
		}
	} else if ($post['type']==6) { // file
		echo '<a href="' . $post['url'] . '">' . $post['title'] . '</a> <small>' . $post['file_info'] . '</small>';
		if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		}
	} else if ($post['type']==7) { // rating
		echo '<h2>' . $post['title'] . '</h2>';
		echo 'rating: ' . str_repeat('❋', $post['rating']);
		if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		}
		// todo URL of video
		if (!empty($post['source'])) {
			echo '<p>from <a href="' . $post['source'] . '">' . $post['source'] . '</a></p>';
		}
	} else if ($post['type']==8) { // event
		if (!empty($post['title'])) {
			echo '<h2>' . $post['title'] . '</h2>';
		}
		if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		}
		if (!empty($post['url'])) {
			echo '<img src="' . $post['url'] . '" class="pure-img">';
		}
		echo '<p>from: ' . $post['start_date'] . '</p>';
		echo '<p>to: ' . $post['end_date'] . '</p>';
		echo '<p>where: ' . $post['location'] . '</p>';
	}
	
	echo '<div class="kyselo-tags">';
	if (!empty($post['tags'])) {
		foreach (explode(' ', $post['tags']) as $tag) {
			echo '<a href="/'.$post['slug_name'].'?tags='.$tag.'" target="_blank">#'.$tag.'</a> ';
		}
	}
	echo '</div>';

	$permalink = '/' . $post['slug_name'] . '/post/' . $post['id'];
	?>


		<div style="height: 2.1em">
                <div class="pure-menu pure-menu-horizontal">
                    <ul class="pure-menu-list pull-right">
                        <li class="pure-menu-item"><a href="<?php echo $permalink; ?>" target="_blank" class="pure-button"><i class="fa fa-link"></i>&#8203;<span class="kyselo-hidden">permalink</span></a></li>
                    </ul>
                </div>
		</div>
	</div>
</div>
<hr>
<?php
}

if (!empty($more_link)) {
	echo '<p>▼ <a href="'.$more_link.'" target="_blank">see more...</a> ▼</p>';
}
?>
</div>
</body>
</html>