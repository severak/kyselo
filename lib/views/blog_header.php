<?php
// blog header
// arguments:
// - $blog
// - $user
// - $tab
?>
<div class="pure-g">
	<div class="pure-u-1-5"><img class="pure-img" src="<?php echo $blog['$avatar_url']; ?>"/></div>
	<div class="pure-u-4-5">
		<h1><?php echo $blog['title']; ?></h1>
		<small><?php echo $blog['about']; ?></small>
		<div class="pure-menu pure-menu-horizontal">
			<a href="/<?php echo $blog['name']; ?>" class="pure-menu-heading pure-menu-link"><i class="fa fa-user"></i> <?php echo $blog['name']; ?></a>
			<ul class="pure-menu-list">
				<li class="pure-menu-item"><a href="/<?php echo $blog['name']; ?>/friends" class="pure-menu-link"><i class="fa fa-user-group"></i> friends</a></li>
				<li class="pure-menu-item"><a href="#" class="pure-menu-link">Sports</a></li>
			</ul>
		</div>
	</div>
</div>
<hr>
