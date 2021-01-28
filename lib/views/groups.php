<h1 class="title"><i class="fa fa-umbrella"></i> Find &amp; create groups</h1>
<?php foreach ($groups as $group) { ?>
<div class="media">
    <div class="media-left">
        <a href="/<?=$group['name']; ?>">
        <img src="<?=kyselo_small_image($group['avatar_url'],100, true); ?>" class="image is-128x128">
        </a>
    </div>
    <div class="media-content">
        <a href="/<?=$group['name']; ?>">
            <h2 class="subtitle"><?= $group['title']; ?></h2>
        </a>
        <div class="content"><?= $group['about']; ?></div>
        <i class="fa fa-users"></i> <?=$group['member_count']; ?> members
        <?php if (isset($_SESSION['user']['groups'][$group['id']])) { ?>
            <br>including me
        <?php } ?>
    </div>
</div>
<hr>
<?php } ?>

<?php if (empty($groups)) { ?>
    <img src="/st/img/undraw_not_found_60pq.png" alt="" class="kyselo-the-end">
    <p>There are no groups yet.</p>
<?php } // empty($groups ?>