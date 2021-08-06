<div class="media kyselo-comment">
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
        <?= kyselo_markup($comment['text']); ?>
    </div>
</div>
