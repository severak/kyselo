<?php
// posts listing / post detail
// arguments:
// - $posts
// - $user
// - $more_link
// - $the_end

$icons = ['', 'book', 'link', 'paragraph', 'camera', 'youtube-play', 'file', 'star', 'calendar'];

foreach ($posts as $post) {
?>
<div class="pure-g" xmlns="http://www.w3.org/1999/html">
	<div class="pure-u-1-5">
		<i class="fa fa-<?php echo $icons[$post['type']]; ?> fa-3x"></i>
	</div>
	<div class="pure-u-4-5">
	<div>
		<img src="<?php echo $post['avatar_url']; ?>" style="width: 1em"> <a href="/<?php echo $post['name']; ?>"><?php echo $post['name']; ?></a>
		<small><?php echo date('j.n.Y H:i:s', $post['datetime']); ?></small>
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
	} else if ($post['type']==5) { // video (URL for now)
		echo '<a href="' . $post['url'] . '">' . $post['body'] . '</a>';
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

	$permalink = '/' . $post['name'] . '/post/' . $post['id'];
	?>


		<div style="height: 2.1em">
                <div class="pure-menu pure-menu-horizontal">
                    <ul class="pure-menu-list pull-right">
                        <li class="pure-menu-item"><a href="<?php echo $permalink; ?>" class="pure-button">#permalink</a></li>
						<?php if (!empty($user)) { ?>
                        <li class="pure-menu-item pure-menu-allow-hover pure-menu-has-children">
                            <a href="#" class="pure-menu-link pure-button">repost</a>
                            <ul class="pure-menu-children">
                                <li class="pure-menu-item"><a href="#" class="pure-menu-link">lorem</a></li>
                            <li class="pure-menu-item"><a href="#" class="pure-menu-link">ipsum</a></li>
                            <li class="pure-menu-item"><a href="#" class="pure-menu-link">dedit</a></li>
                    </ul>
                    </li>
                    <li class="pure-menu-item"><a href="#" class="pure-button">react</a></li>
					<?php } ?>
                    </ul>
                </div>
		</div>
	</div>
</div>
<hr>
<?php
}

if (!empty($more_link)) {
	echo '<p>▼ <a href="'.$more_link.'">see more...</a> ▼</p>';
}

if (!empty($the_end)) {
	echo '<p>You have reached teh end...</p>';
}