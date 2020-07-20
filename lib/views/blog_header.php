<?php
// blog header
// arguments:
// - $blog
// - $user
// - $tab
?>
<div class="pure-g">
	<div class="pure-u-1-5">
		<div class="kyselo-big-profile">
			<img class="pure-img" src="<?php echo kyselo_small_image($blog['avatar_url'], 100, true); ?>"/>
		</div>	
	</div>
	<div class="pure-u-4-5">
		<h1><?php echo $blog['title']; ?></h1>
		<small><?php echo $blog['about']; ?></small>
		<ul class="kyselo-tabs">
			<li <?=($tab=='blog' ? 'class="active"' : ''); ?> ><a href="/<?php echo $blog['name']; ?>"><i class="fa fa-user"></i> <?php echo $blog['name']; ?></a></li>
            <?php if ($blog['is_group']) { ?>
			<li <?=($tab=='members' ? 'class="active"' : ''); ?>><a href="/<?php echo $blog['name']; ?>/members"><i class="fa fa-users"></i> members</a></li>
			<?php } else { ?>
            <li <?=($tab=='friends' ? 'class="active"' : ''); ?>><a href="/<?php echo $blog['name']; ?>/friends"><i class="fa fa-users"></i> friends</a></li>
			<?php } ?>
            <?php if (( !empty($user) && $blog['id']==$user['blog_id'] ) || (!empty($_SESSION['user']['groups'][$blog['id']]))) { ?>
			<li <?=($tab=='settings' ? 'class="active"' : ''); ?>><a href="/act/settings/<?php echo $blog['name']; ?>"><i class="fa fa-cog"></i> settings</a></li>
			<?php } ?>
		</ul>
	</div>
</div>
<hr class="kyselo-tabs-base">