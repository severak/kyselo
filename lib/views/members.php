<?php
// arguments:
// - members
foreach ($members as $member) { ?>
    <div class="pure-g">
        <div class="pure-u-1-5 kyselo-big-profile">
            <img src="<?=kyselo_small_image($member['avatar_url'], 100, true); ?>" class="pure-img">
        </div>
        <div class="pure-u-3-5">
            <a href="/<?=$member['name']; ?>"><?=$member['name']; ?></a>
            <h2><?= $member['title']; ?></h2>
            <small><?= $member['about']; ?></small>
        </div>
        <div class="pure-u-1-5">
        </div>
    </div>
    <hr>
<?php } ?>