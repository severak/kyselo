<!--- TODO -->
<?php
// arguments:
// - members
foreach ($members as $member) { ?>
    <div class="media">
        <div class="media-left kyselo-big-profile">
            <a href="/<?=$member['name']; ?>">
            <img src="<?=kyselo_small_image($member['avatar_url'], 128, true); ?>" class="image is-128x128">
            <?= $member['name']; ?>
            </a>
        </div>
        <div class="media-content">
            <a href="/<?=$member['name']; ?>">
            <h2 class="subtitle"><?= $member['title']; ?></h2>
            </a>
            <div class="content"><?= $member['about']; ?></div>
            <?php if (isset($member['last_seen'])) { ?>
            <i class="fa fa-calendar"></i>&nbsp;last seen <?=date('j.n.Y', strtotime($member['since'])); ?>
            <?php } else { ?>
            <i class="fa fa-calendar"></i>&nbsp;joined <?=date('j.n.Y', strtotime($member['since'])); ?>
            <?php } ?>
        </div>
        <div class="media-left">
        </div>
    </div>
    <hr>
<?php } ?>
