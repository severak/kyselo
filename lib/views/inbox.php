<?php
// dialogue with someone
// arguments:
// - messages
// - outbox?
if (empty($outbox)) $outbox=false;

if (empty($messages)) {
?>
<div class="media">
	<div class="media-left"></div>
	<div class="media-content">
		<em>awkward silence here...</em>
	</div>
</div>
<hr/>	
<?php
} else { 
foreach ($messages as $message) {
?>
<div class="media">
    <div class="media-left ">
		<strong><?=htmlspecialchars($message['name']); ?></strong>
		<div class="kyselo-big-profile"><img src="<?=htmlspecialchars($message['avatar_url']); ?>" class="image is-128x128"></div>
	</div>
    <div class="media-content">
		<a href="/act/messages/with/<?=$message['name'];?>#last" class="kyselo-message-text <?=($message['is_read'] ? 'old' : 'new'); ?>">
        <?php if ($outbox) { ?>Me: <?php } ?>
        <?= nl2br(htmlspecialchars($message['text'])); ?>
        </a>
		<br/><br/><small><?=date('Y-m-d H:i:s', $message['datetime']); ?></small>
	</div>
</div>
<hr/>
<?php } // endforeach
} // endif
