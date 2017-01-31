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
	<?php echo date('j.n.Y', strtotime($post['datetime'])); ?><br/>
	
	<?php if ($post['type']==1): ?>
		<?php if (!empty($post['title'])) {
			echo '<h2>' . $post['title'] . '</h2>';
		} 
		echo $post['body'];
		?>
	<?php elseif ($post['type']==4): ?>
		<img class="pure-img" src="<?php echo $post['url']; ?>"/>
		<?php if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		} ?>
	<?php else: ?>	
		<?php dump($post); ?>
	<?php endif; ?>


		<!-- repost a spol -->
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
	echo '<a href="/'.$blog_name.'?since='.date('c', strtotime($posts[30]['datetime'])).'">load moar</a>';
} else {
	echo 'You have reached teh end...';
}


echo $footer;