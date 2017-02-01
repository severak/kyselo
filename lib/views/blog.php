<?php
echo $header;
?>

<!-- hlavicka -->
<div class="pure-g">
	<div class="pure-u-1-5"><img class="pure-img" src="<?php echo $blog_image; ?>"/></div>
	<div class="pure-u-4-5">
		<h1><?php echo $blog_title; ?></h1>
		<small><?php echo $blog_about; ?></small>
	</div>
</div>
<hr>
<!-- / hlavicka-->

<?php
$i = 0;
foreach ($posts as $post) {
?>
<div class="pure-g">
	<div class="pure-u-1-5"><img class="pure-img" src="<?php echo $blog_image; ?>"/><br/><?php echo $blog_name; ?></div>
	<div class="pure-u-4-5">
	<?php echo date('j.n.Y H:i:s', strtotime($post['datetime'])); ?><br/>
	
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
	} else if ($post['type']==3) { // link	
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
		echo '<a href="' . $post['url'] . '">' . $post['title'] . '</a> <small>' . $post['info'] . '</small>';
		if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		}
	} else if ($post['type']==7) { // rating
		echo '<h2>' . $post['title'] . '</h2>';
		echo 'rating: ' . str_repeat('*', $post['rating']);
		if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		}
		// todo ULR of video
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
	} else dump($post);
	?>


		<!-- repost a spol -->
		<div style="height: 2.1em">
                <div class="pure-menu pure-menu-horizontal">
                    <ul class="pure-menu-list pull-right">
                        <li class="pure-menu-item"><a href="<?php echo sprintf('/%s/post/%d', $blog_name, $post['id']); ?>" class="pure-button">#</a></li>
                        <li class="pure-menu-item pure-menu-allow-hover pure-menu-has-children">
                            <a href="#" class="pure-menu-link pure-button">repost</a>
                            <ul class="pure-menu-children">
                                <li class="pure-menu-item"><a href="#" class="pure-menu-link">lorem</a></li>
                            <li class="pure-menu-item"><a href="#" class="pure-menu-link">ipsum</a></li>
                            <li class="pure-menu-item"><a href="#" class="pure-menu-link">dedit</a></li>
                    </ul>
                    </li>
                    <li class="pure-menu-item"><a href="#" class="pure-button">react</a></li>
                    </ul>
                </div>
				</div>
                <!-- /repost a spol -->
	</div>
</div>
<hr>

<?php
$i++;

if ($i==30) {
	break;
}



// end loop posts
}

if (isset($posts[30]['datetime'])) {
	echo '<a href="/'.$blog_name.'?since='.date('Y-m-d\TH:i:s', strtotime($posts[30]['datetime'])).'">load moar</a><br/><br/><br/>';
} else {
	echo 'You have reached teh end...';
}


echo $footer;