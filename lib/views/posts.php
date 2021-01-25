<?php
// posts listing / post detail
// arguments:
// - $posts
// - $blog
// - $user
// - $more_link
// - $the_end
// - friends

$icons = ['', 'book', 'link', 'paragraph', 'camera', 'youtube-play', 'file', 'star', 'calendar'];

if (!empty($friends)) {
    echo '<div classs="pure-g"><div class="pure-u-1-5"></div><div class="pure-u-4-5">';
    foreach ($friends as $friend) {
        echo '<a href="/'.$friend['name'].'"><img src="'.kyselo_small_image($friend['avatar_url'],50, true).'" title="'.$friend['title'].'" width="50"></a>';
    }
    echo '</div><hr>';
}

$showFullVideo = count($posts)==1;

foreach ($posts as $post) {
$nsfwClass = $post['is_nsfw'] ? 'is-nsfw' : '';	
?>
<div class="media">
	<div class="media-left">
		<a href="/<?=$post['name']; ?>">
			<img src=<?php echo kyselo_small_image($post['avatar_url'], 50, true); ?> class="image is-64x64">
			<?=$post['name']; ?>
		</a>
		<?php if (!empty($post['group_name'])) { ?>
			&nbsp;in <a href="/<?=$post['group_name']; ?>">
			<img src=<?php echo kyselo_small_image($post['group_avatar_url'], 50, true); ?> class="image is-64x64">
			<?=$post['group_name']; ?>
		</a>
		<?php } ?>
		
	</div>
	<div class="media-content content <?=$nsfwClass; ?>">
	<div>
		<small><i class="fa fa-<?php echo $icons[$post['type']]; ?>"></i> <?php echo date('j.n.Y H:i:s', $post['datetime']); ?></small>
	</div><br>
    <div class="kyselo-post-body">
	<?php 

	if (!empty($post['reposted_from'])) {
		echo '<i class="fa fa-refresh"></i> reposted from <img src="'.kyselo_small_image($post['reposted_from']['avatar_url'], 50, true).'" style="width:1em"> <a href="/'.$post['reposted_from']['name'].'/post/'.$post['repost_of'].'">' . $post['reposted_from']['name'] . '</a><br>';
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
		echo '<img class="image" src="' . $post['url'] . '"/>';
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
				echo '<a href="' . $post['source'] . '" class="kyselo-play-video button is-link is-outlined"><i class="fa fa-youtube-play"></i><span class="kyselo-hidden"> play video</span></a>';
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
			echo '<img src="' . $post['url'] . '" class="image">';
		}
		echo '<p>from: ' . $post['start_date'] . '</p>';
		echo '<p>to: ' . $post['end_date'] . '</p>';
		echo '<p>where: ' . $post['location'] . '</p>';
	}

	echo '</div>';
	echo '<div class="kyselo-tags">';
	if (!empty($post['tags'])) {
		foreach (explode(' ', $post['tags']) as $tag) {
			echo '<a href="/'.$post['slug_name'].'?tag='.$tag.'">#'.$tag.'</a> ';
		}
	}
	echo '</div>';

	if (!empty($post['reposted_by'])) {
		echo '<br><i class="fa fa-refresh"></i> reposted by ';
		foreach ($post['reposted_by'] as $repost) {
			echo '<img src="'.kyselo_small_image($repost['avatar_url'], 50, true).'" style="width:1em"> <a href="/'.$repost['name'].'/post/'.$repost['repost_id'].'">' . $repost['name'] . '</a> ';
		}
	}

	$permalink = '/' . $post['slug_name'] . '/post/' . $post['id'];
	?>


		      <div class="buttons p-2">
                        <a href="<?php echo $permalink; ?>" class="button" title="permalink"><i class="fa fa-link"></i>&#8203;<span class="kyselo-hidden">permalink</span></a>
                        
						<?php if (!empty($user)) { ?>
						
						<div class="dropdown is-hoverable is-overlay">
							<div class="dropdown-trigger">
								<button class="button" aria-haspopup="true" aria-controls="dropdown-menu-p<?=$post['id']; ?>">
								<span><i class="fa fa-refresh"></i>&#8203;<span class="kyselo-hidden">repost to</span>&nbsp;▼</span>
								</button>
							</div>
							<div class="dropdown-menu" id="dropdown-menu-p<?=$post['id']; ?>" role="menu">
								<div class="dropdown-content">
									<a href="/act/repost?post_id=<?= $post['id']; ?>&blog_id=<?=$user['id']; ?>" class="dropdown-item kyselo-repost">
										<img src="<?=kyselo_small_image($user['avatar_url'], 50, true); ?>" style="width:1em"> <?=$user['name']; ?>&nbsp;
									</a>
									<?php foreach ($user['groups'] as $group) { ?>
									<a href="/act/repost?post_id=<?= $post['id']; ?>&blog_id=<?=$group['id']; ?>" class="dropdown-item kyselo-repost">
										<img src="<?=kyselo_small_image($group['avatar_url'], 50, true); ?>" style="width:1em"> <?=$group['name']; ?>&nbsp;
									</a>
									<?php } // foreach ?>
								</div>
							</div>
						</div>
						
						<?php } // repost ?>
						<?php if (can_edit_post($post)) { ?>
						<a href="/act/post/edit/<?=$post['id']; ?>" class="button" title="edit"><i class="fa fa-pencil"></i>&#8203;<span class="kyselo-hidden">edit post</span></a>
						<a href="/act/post/delete/<?=$post['id']; ?>" class="button" title="delete"><i class="fa fa-trash"></i>&#8203;<span class="kyselo-hidden">delete post</span></a>
						<?php } ?>
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
    echo '<p>You have reached the end...</p>';
	echo '<p><img src="/st/img/undraw_a_moment_to_relax_bbpa.png" alt="THE END"></p>';
	
}

?>
