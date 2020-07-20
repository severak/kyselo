<h1><i class="fa fa-umbrella"></i> Find &amp; create groups</h1>
<?php foreach ($groups as $group) { ?>
<div class="pure-g">
    <div class="pure-u-1-5 kyselo-big-profile">
        <img src="<?=kyselo_small_image($group['avatar_url'],100, true); ?>" class="pure-img">
    </div>
    <div class="pure-u-3-5">
        <a href="/<?=$group['name']; ?>"><?=$group['name']; ?></a>
        <h2><?= $group['title']; ?></h2>
		<small><?= $group['about']; ?></small>
    </div>
    <div class="pure-u-1-5">
        <i class="fa fa-users"></i> <?=$group['member_count']; ?> members
        <?php if (isset($_SESSION['user']['groups'][$group['id']])) { ?>
        <br>including me
        <?php } ?>
    </div>
</div>
<hr>
<?php } ?>
