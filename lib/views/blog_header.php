<?php
// blog header
// arguments:
// - $blog
// - $user
// - $tab
?>
<div class="pure-g">
	<div class="pure-u-1-5"><img class="pure-img" src="<?php echo $blog['avatar_url']; ?>"/></div>
	<div class="pure-u-4-5">
		<h1><?php echo $blog['title']; ?></h1>
		<small><?php echo $blog['about']; ?></small>
		<div class="kyselo-tabs">
			<a href="/<?php echo $blog['name']; ?>" class="pure-button <?php echo $tab=='blog' ? 'pure-button-active': ''; ?>"><i class="fa fa-user"></i> <?php echo $blog['name']; ?></a>
			<a href="/<?php echo $blog['name']; ?>/friends" class="pure-button <?php echo $tab=='friends' ? 'pure-button-active': ''; ?>"><i class="fa fa-users"></i> friends</a>
			<?php if (!empty($user) && $blog['id']==$user['blog_id']) { ?>
			<a href="/act/settings/<?php echo $blog['name']; ?>" class="pure-button <?php echo $tab=='settings' ? 'pure-button-active': ''; ?>"><i class="fa fa-cog"></i> settings</a>
			<?php } ?>
		</div>
	</div>
</div>
<hr/>