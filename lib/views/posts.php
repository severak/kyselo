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
    echo '<div class="kyselo-friendlist">';
    foreach ($friends as $friend) {
        echo '<a href="/'.$friend['name'].'" class="kyselo-friend-box"><img src="'.kyselo_small_image($friend['avatar_url'],64, true).'" title="'.$friend['title'].'"><span>'.$friend['name'].'</span></a>';
    }
    echo '</div><hr>';
}

$showFullVideo = count($posts)==1;
$commentsCollapsed = count($posts)>1;

foreach ($posts as $post) {
$nsfwClass = $post['is_nsfw'] ? 'is-nsfw' : '';
?>
<div class="media kyselo-post">
	<div class="media-left">
		<a href="/<?=$post['name']; ?>">
			<img src=<?php echo kyselo_small_image($post['avatar_url'], 64, true); ?> class="image is-64x64">
			<?=$post['name']; ?>
		</a>
		<?php if (!empty($post['group_name'])) { ?>
			&nbsp;in <a href="/<?=$post['group_name']; ?>">
			<img src=<?php echo kyselo_small_image($post['group_avatar_url'], 64, true); ?> class="image is-64x64">
			<?=$post['group_name']; ?>
		</a>
		<?php } ?>

	</div>
	<div class="media-content content <?=$nsfwClass; ?>">
	<div>
		<small><i class="fa fa-<?php echo $icons[$post['type']]; ?>"></i> <?php
            $datum = new fTimestamp($post['datetime']);
            echo '<span title="' . $datum->getFuzzyDifference() . '">';
            echo $datum->format('j.n.Y H:i:s');
            echo '</span>';
        ?>
        <?php
        if (!empty($post['reposted_from'])) {
            echo '<i class="fa fa-refresh"></i> reposted from <img src="'.kyselo_small_image($post['reposted_from']['avatar_url'], 50, true).'" style="width:1em"> <a href="/'.$post['reposted_from']['name'].'/post/'.$post['repost_of'].'">' . $post['reposted_from']['name'] . '</a><br>';
        }
        ?>
        </small>
	</div><br>
    <div class="kyselo-post-body">
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
        $imgClass = 'kyselo-image';
	    $size = @getimagesize(Flight::rootpath() . $post['url']);
        if (!empty($size[0]) && (($size[0]*3) < $size[1])) {
            $imgClass = 'kyselo-image-long';
        }
        if (!empty($size[0]) && ($size[0]==$size[1])) {
            $imgClass = 'kyselo-image-square';
        }

		echo '<img class="image '.$imgClass.'" src="' . $post['url'] . '"/>';
		if (!empty($post['body'])) {
			echo '<p>' . $post['body'] . '</p>';
		}
		if (!empty($post['source'])) {
			echo '<p>from <a href="' . $post['source'] . '">' . $post['source'] . '</a></p>';
		}
	} else if ($post['type']==5) { // video
        if (!empty($post['title'])) {
            echo '<h2>' . $post['title'] . '</h2>';
        }
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

	$repostedTo = [];
	if (!empty($post['reposted_by'])) {
		echo '<br><i class="fa fa-refresh"></i> reposted by ';
		foreach ($post['reposted_by'] as $repost) {
		    $repostedTo[$repost['name']] = true;
		    echo '<img src="'.kyselo_small_image($repost['avatar_url'], 32, true).'" style="width:1em"> <a href="/'.$repost['name'].'/post/'.$repost['repost_id'].'">' . $repost['name'] . '</a> ';
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
										<img src="<?=kyselo_small_image($user['avatar_url'], 32, true); ?>" style="width:1em"> <?=$user['name']; ?>
                                        <?php if (isset($repostedTo[$user['name']])) echo '<i class="fa fa-check" title="already reposted"></i>'; ?>
									</a>
									<?php foreach ($user['groups'] as $group) { ?>
									<a href="/act/repost?post_id=<?= $post['id']; ?>&blog_id=<?=$group['id']; ?>" class="dropdown-item kyselo-repost">
										<img src="<?=kyselo_small_image($group['avatar_url'], 32, true); ?>" style="width:1em"> <?=$group['name']; ?>&nbsp;
                                        <?php if (isset($repostedTo[$group['name']])) echo '<i class="fa fa-check" title="already reposted"></i>'; ?>
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

                <?php if ($commentsCollapsed && ($post['comments_count'] || !empty($user))) { ?>
                <details>
                    <summary><i class="fa fa-comments"></i> <?=$post['comments_count']; ?> comments</summary>
                <?php } // $commentsCollapsed ?>

                    <?php foreach ($post['comments'] as $comment) { ?>
                        <div class="media kyselo-comment" id="comment<?=$comment['id']; ?>">
                        <div class="media-left">
                            <a href="/<?=$comment['name']; ?>">
                                <img src=<?php echo kyselo_small_image($comment['avatar_url'], 64, true); ?> class="image is-64x64">
                                <?=$comment['name']; ?>
                            </a>
                        </div>
                        <div class="media-content">
                            <small><i class="fa fa-comment"></i> <?php
                                $datum = new fTimestamp($comment['datetime']);
                                echo '<span title="' . $datum->getFuzzyDifference() . '">';
                                echo $datum->format('j.n.Y H:i:s');
                                echo '</span>';
                                ?></small><br>
                            <?=kyselo_markup($comment['text']); ?>
                        </div>
                        <div class="media-right">
                            <?php if (can_edit_comment($comment)) { ?>
                                <button class="button is-small" data-edit-comment="<?=$comment['id']; ?>"><i class="fa fa-pencil"></i></button>
                            <?php } ?>
                            <?php if (can_delete_comment($comment, $post)) { ?>
                                <button class="button is-small" data-delete-comment="<?=$comment['id']; ?>"><i class="fa fa-trash"></i></button>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } // comments ?>

                    <?php if (!empty($user)) { ?>
                    <div class="comment-post-form" data-post-id="<?=$post['id'];?>">
                        <hr>
                        <form class="mt-2 mb-2">
                            <div class="field">
                                <div class="control">
                                    <textarea placeholder="text of your comment..." rows="2" class="textarea"></textarea>
                                </div>
                                <div>
                                    <button class="button is-info is-fullwidth comment-post-button">Post comment</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <?php } // $user ?>

                <?php if ($commentsCollapsed) { ?>
                </details>
                <?php } ?>
		</div>
</div>
<?php
}

if (!empty($more_link)) {
	echo '<p>▼ <a href="'.$more_link.'">see more...</a> ▼</p>';
}

if (!empty($the_end)) {
    echo '<div class="kyselo-the-end">';
    echo '<p>You have reached the end...</p>';
	echo '<p><img src="/st/img/undraw_a_moment_to_relax_bbpa.png" alt="THE END" class="kyselo-the-end"></p>';
	echo '</div>';
}

?>
