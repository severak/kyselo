<?php
// blog header
// arguments:
// - $blog
// - $user
// - $tab
// - $subtab
// - $settings_subtab
?>
<div class="media">
	<div class="media-left">
		<div class="kyselo-big-profile">
			<a href="/<?=$blog['name']; ?>"><img class="image is-128x128" src="<?php echo kyselo_small_image($blog['avatar_url'], 128, true); ?>"/></a>
		</div>
	</div>
	<div class="media-center kyselo-about">
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
            <?php } // has_journal ?>
            <?php if (!empty($blog['has_gallery'])) { ?>
                <a href="<?=kyselo_url('/%s/gallery', [$blog['name']]); ?>" class="button"><i class="fa fa-photo"></i>&nbsp;gallery</a>
            <?php } // has_gallery ?>
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
<hr>
<?php } //endif $subtab?>

<?php if (isset($settings_subtab)) { ?>
    <div class="buttons">
        <a href="<?=kyselo_url('/act/settings/%s', [$blog['name']]); ?>" class="button"><i class="fa fa-cog"></i>&nbsp;<?=$blog['is_group'] ? 'group' : 'blog'; ?> settings</a>
        <a href="<?=kyselo_url('/act/custom-css/%s', [$blog['name']]); ?>" class="button"><i class="fa fa-paint-brush"></i>&nbsp;custom CSS</a>
        <?php if (!$blog['is_group']) { ?>
            <a href="<?=kyselo_url('/act/change-password'); ?>" class="button"><i class="fa fa-key"></i>&nbsp;change password</a>
        <?php }  ?>
    </div>
    <hr>
<?php } //endif $subtab?>

<?php if (isset($all)) { ?>
    <div class="buttons">
        <a href="/all/rss" class="button"><i class="fa fa-rss"></i>&nbsp;RSS</a>
        <a href="/raw" class="button"><i class="fa fa-truck"></i>&nbsp;unfiltered view</a>
        <a href="/act/last-posts-by" class="button"><i class="fa fa-calendar"></i>&nbsp;last posts by each user</a>
    </div>
    <hr>
<?php } //endif $all?>

<?php if (isset($members)) { ?>
    <div class="buttons">
        <a href="/all/members" class="button"><i class="fa fa-sort-alpha-asc"></i>&nbsp;by alphabet</a>
        <a href="/all/members?sortBy=lastSeen" class="button"><i class="fa fa-sort-numeric-asc"></i>&nbsp;by last activity</a>
        <a href="/all/members?sortBy=numberOfPosts" class="button"><i class="fa fa-calendar"></i>&nbsp;by number of posts</a>
    </div>
    <hr>
<?php } //endif $all?>

<style>
<?php echo (isset($blog['custom_css']) ? $blog['custom_css'] : ''); ?>
</style>
