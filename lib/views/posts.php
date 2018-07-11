<?php
// posts listing / post detail
// arguments:
// - $posts
// - $blog
// - $user
// - $more_link
// - $the_end

$icons = ['', 'book', 'link', 'paragraph', 'camera', 'youtube-play', 'file', 'star', 'calendar'];

$showFullVideo = count($posts)==1;

foreach ($posts as $post) {
$nsfwClass = $post['is_nsfw'] ? 'is-nsfw' : '';	
?>
<div class="pure-g">
	<div class="pure-u-1-5">
		<i class="fa fa-<?php echo $icons[$post['type']]; ?> fa-3x"></i>
	</div>
	<div class="pure-u-4-5 <?=$nsfwClass; ?>">
	<div>
		<img src="<?php echo $post['avatar_url']; ?>" style="width: 1em"> <a href="/<?php echo $post['name']; ?>"><?php echo $post['name']; ?></a>
		<small><?php echo date('j.n.Y H:i:s', $post['datetime']); ?></small>
		<?php if (!empty($post['group_name'])) { ?>
		<br>in <img src="<?php echo $post['group_avatar_url']; ?>" style="width: 1em"> <a href="/<?php echo $post['group_name']; ?>"><?php echo $post['group_name']; ?></a>
		<?php } ?>
	</div><br>
	<?php 

	if (!empty($post['reposted_from'])) {
		echo '<i class="fa fa-share"></i> reposted from <img src="'.$post['reposted_from']['avatar_url'].'" style="width:1em"> <a href="/'.$post['reposted_from']['name'].'/post/'.$post['repost_of'].'">' . $post['reposted_from']['name'] . '</a><br>';
	}

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
				echo '<a href="' . $post['source'] . '" class="kyselo-play-video pure-button button-large"><i class="fa fa-youtube-play"></i><span class="kyselo-hidden"> play video</span></a>';
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
			echo '<a href="/'.$post['slug_name'].'?tags='.$tag.'">#'.$tag.'</a> ';
		}
	}
	echo '</div>';

	if (!empty($post['reposted_by'])) {
		echo '<br><i class="fa fa-share"></i> reposted by ';
		foreach ($post['reposted_by'] as $repost) {
			echo '<img src="'.$repost['avatar_url'].'" style="width:1em"> <a href="/'.$repost['name'].'/post/'.$repost['repost_id'].'">' . $repost['name'] . '</a> ';
		}
	}

	$permalink = '/' . $post['slug_name'] . '/post/' . $post['id'];
	?>


		<div style="height: 2.1em">
                <div class="pure-menu pure-menu-horizontal">
                    <ul class="pure-menu-list pull-right">
                        <li class="pure-menu-item"><a href="<?php echo $permalink; ?>" class="pure-button"><i class="fa fa-link"></i>&#8203;<span class="kyselo-hidden">permalink</span></a></li>
                        
						<?php if (!empty($user)) { ?>
						<li class="pure-menu-item pure-menu-allow-hover pure-menu-has-children"><a href="#" class="pure-button"><i class="fa fa-share"></i>&#8203;<span class="kyselo-hidden">repost to</span></a>
							<ul class="pure-menu-children kyselo-dark">
								<li class="pure-menu-item"><a href="/act/repost?post_id=<?= $post['id']; ?>&blog_id=<?=$user['id']; ?>" class="pure-menu-link kyselo-repost"><img src="<?=$user['avatar_url']; ?>" style="width:1em"> <?=$user['name']; ?> </a></li>
								<?php foreach ($user['groups'] as $group) { ?>
								<li class="pure-menu-item"><a href="/act/repost?post_id=<?= $post['id']; ?>&blog_id=<?=$group['id']; ?>" class="pure-menu-link kyselo-repost"><img src="<?=$group['avatar_url']; ?>" style="width:1em"> <?=$group['name']; ?> </a></li>
								<?php } ?>
							</ul>
						</li>
						<?php } // repost ?>
						<?php if (!empty($user) && $user['blog_id']==$post['author_id']) { ?>
						<li class="pure-menu-item"><a href="/act/post/edit/<?=$post['id']; ?>" class="pure-button" title="edit"><i class="fa fa-pencil"></i>&#8203;<span class="kyselo-hidden">edit post</span></a></li>
						<li class="pure-menu-item"><a href="/act/post/delete/<?=$post['id']; ?>" class="pure-button" title="delete"><i class="fa fa-trash"></i>&#8203;<span class="kyselo-hidden">delete post</span></a></li>
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

// floating buttons:
echo '<div class="kyselo-float">';
if (empty($user)) {
	$loginAs = '';
	if (!empty($blog) && !$blog['is_group']) {
		$loginAs = '?as=' . $blog['name'];
	}
	// login
	echo '<a href="/act/login'.$loginAs.'" class="pure-button button-large"><i class="fa fa-key"></i><span class="kyselo-hidden"> login</span></a>';
} else {
	// my blog:
	if (!empty($blog) && $blog['name']==$user['name']) {
		// post
		echo '<a href="#post_types" id="new_post" class="pure-button button-large"><i class="fa fa-pencil"></i><span class="kyselo-hidden"> new post</span></a>';
		echo '<span id="post_types">';
		echo '<a href="/act/post?as='.$user['name'].'&type=1" class="pure-button button-large"><i class="fa fa-book"></i><span class="kyselo-hidden"> new post</span></a>';
		echo '<a href="/act/post?as='.$user['name'].'&type=2" class="pure-button button-large"><i class="fa fa-link"></i><span class="kyselo-hidden"> new post</span></a>';
		echo '<a href="/act/post?as='.$user['name'].'&type=3" class="pure-button button-large"><i class="fa fa-paragraph"></i><span class="kyselo-hidden"> new post</span></a>';
		echo '<a href="/act/post?as='.$user['name'].'&type=4" class="pure-button button-large"><i class="fa fa-camera"></i><span class="kyselo-hidden"> new post</span></a>';
		echo '<a href="/act/post?as='.$user['name'].'&type=5" class="pure-button button-large"><i class="fa fa-youtube-play"></i><span class="kyselo-hidden"> new post</span></a>';
		echo '<a href="/act/post?as='.$user['name'].'&type=6" class="pure-button button-large"><i class="fa fa-file"></i><span class="kyselo-hidden"> new post</span></a>';
		echo '<a href="/act/post?as='.$user['name'].'&type=7" class="pure-button button-large"><i class="fa fa-star"></i><span class="kyselo-hidden"> new post</span></a>';
		echo '<a href="/act/post?as='.$user['name'].'&type=8" class="pure-button button-large"><i class="fa fa-calendar"></i><span class="kyselo-hidden"> new post</span></a>';
		echo '</span>';
	}
	// group:
	if (!empty($blog) && $blog['name']!=$user['name'] && $blog['is_group']) {
		$friendshipExists = Flight::db()->from('friendships')->where('from_blog_id', $user['blog_id'])->where('to_blog_id', $blog['id'])->count() > 0;
		// follow
		echo '<a href="/act/follow?who='.$blog['name'].'" class="pure-button button-large kyselo-switch '.($friendshipExists ? 'on' : 'off').'"><i class="fa fa-heart"></i><span class="kyselo-hidden"> follow</span></a>';	
		$membership = Flight::db()->from('memberships')->where('member_id', $user['blog_id'])->where('blog_id', $blog['id'])->one();
		// member
		echo '<a href="/act/member?who='.$blog['name'].'" class="pure-button button-large kyselo-switch '.($membership ? 'on' : 'off').' '.(!empty($friendship['is_admin']) ? 'is-admin' : '').'"><i class="fa fa-user-plus"></i><span class="kyselo-hidden"> member</span></a>';	
	}
	// other blog:
	if (!empty($blog) && $blog['name']!=$user['name'] && !$blog['is_group']) {
		$friendshipExists = Flight::db()->from('friendships')->where('from_blog_id', $user['blog_id'])->where('to_blog_id', $blog['id'])->count() > 0;
		// follow
		echo '<a href="/act/follow?who='.$blog['name'].'" class="pure-button button-large kyselo-switch '.($friendshipExists ? 'on' : 'off').'"><i class="fa fa-heart"></i><span class="kyselo-hidden"> follow</span></a>';	
		// message
		echo '<a href="/act/messages/with/'.$blog['name'].'" class="pure-button button-large"><i class="fa fa-paper-plane"></i><span class="kyselo-hidden"> message</span></a>';	
	}
}
echo '</div>';

?>
<script>
$('#new_post').on('click', function(){
	$('#post_types').show();
	return false;
});
</script>
