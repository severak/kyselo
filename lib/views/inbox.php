<?php
// dialogue with someone
// arguments:
// - messages
// - outbox?
if (empty($outbox)) $outbox=false;

if (empty($messages)) {
?>
<div class="pure-g">
	<div class="pure-u-1-5"></div>
	<div class="pure-u-4-5">
		<em>awkward silence here...</em>
	</div>
</div>
<hr/>	
<?php
} else { 
foreach ($messages as $message) {
?>
<div class="pure-g">
    <div class="pure-u-1-5 ">
		<strong><?=htmlspecialchars($message['name']); ?></strong>
		<div class="kyselo-big-profile"><img src="<?=htmlspecialchars($message['avatar_url']); ?>" class="pure-img"></div>
	</div>
    <div class="pure-u-4-5">
		<a href="/act/messages/with/<?=$message['name'];?>" class="kyselo-message-text <?=($message['is_read'] ? 'old' : 'new'); ?>">
        <?php if ($outbox) { ?>Me: <?php } ?>
        <?= nl2br(htmlspecialchars($message['text'])); ?>
        </a>
		<br/><br/><small><?=date('Y-m-d H:i:s', $message['datetime']); ?></small>
	</div>
</div>
<hr/>
<?php } // endforeach
} // endif
