<?php
// blog header
// arguments:
// - $blog
// - $user
// - $tab
// - $subtab
?>
<div class="media">
	<div class="media-left">
		<div class="kyselo-big-profile">
			<a href="/<?=$blog['name']; ?>"><img class="image is-128x128" src="<?php echo kyselo_small_image($blog['avatar_url'], 100, true); ?>"/></a>
		</div>	
	</div>
	<div class="media-center">
		<a href="/<?=$blog['name']; ?>"><h1 class="title"><?php echo $blog['title']; ?></h1></a>
		<div class="content"><?php echo $blog['about']; ?></div>
    </div>
</div>
<div class="tabs is-boxed kyselo-tabs-padding">
		<ul>
			<li <?=($tab=='blog' ? 'class="is-active"' : ''); ?> ><a href="/<?php echo $blog['name']; ?>"><i class="fa fa-user"></i> <?php echo $blog['name']; ?></a></li>
            <?php if ($blog['is_group']) { ?>
			<li <?=($tab=='members' ? 'class="is-active"' : ''); ?>><a href="/<?php echo $blog['name']; ?>/members"><i class="fa fa-users"></i> members</a></li>
			<?php } else { ?>
            <li <?=($tab=='friends' ? 'class="is-active"' : ''); ?>><a href="/<?php echo $blog['name']; ?>/friends"><i class="fa fa-users"></i> friends</a></li>
			<?php } ?>
            <?php if (( !empty($user) && $blog['id']==$user['blog_id'] ) || (!empty($_SESSION['user']['groups'][$blog['id']]))) { ?>
			<li <?=($tab=='settings' ? 'class="is-active"' : ''); ?>><a href="/act/settings/<?php echo $blog['name']; ?>"><i class="fa fa-cog"></i> settings</a></li>
			<?php } ?>
		</ul>
</div>
<?php if (isset($subtab)) { ?>
        <div class="buttons">
            <a href="<?=kyselo_url('/%s/tags', [$blog['name']]); ?>" class="button"><i class="fa fa-tags"></i>&nbsp;tags</a>
            <?php if (!empty($blog['has_journal'])) { ?>
            <a href="<?=kyselo_url('/%s/journal', [$blog['name']]); ?>" class="button"><i class="fa fa-list"></i>&nbsp;journal</a>
            <?php } // has_headlines_view ?>
            <?php if (!empty($blog['has_videos'])) { ?>
            <a href="<?=kyselo_url('/%s/videos', [$blog['name']]); ?>" class="button"><i class="fa fa-youtube-play"></i>&nbsp;playlist</a>
            <?php } // has_playlist_view ?>

            <?php
            $subtabParams = $_GET;
            unset($subtabParams['since']);
            $subtabQ = empty($subtabParams) ? '' : ('?' . http_build_query($subtabParams));
            ?>
            <a href="<?=kyselo_url('/%s/rss%s', [$blog['name'], $subtabQ]); ?>" class="button"><i class="fa fa-rss"></i>&nbsp;RSS</a>
</div>
<?php } //endif $subtab?>

<?php if (isset($rsslink)) { ?>
    <div class="buttons">
            <a href="<?=$rsslink; ?>" class="button"><i class="fa fa-rss"></i> RSS</a>
    </div>
<?php } //endif $rsslink?>