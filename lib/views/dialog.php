<?php
// dialogue with someone
// arguments:
// - messages
// - with

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
	<div class="pure-u-1-5 kyselo-big-profile">
		<strong><?=htmlspecialchars($message['name']); ?></strong>:
		<img src="<?=kyselo_small_image($message['avatar_url'], 100, true); ?>" class="pure-img">
	</div>
	<div class="pure-u-4-5">
		<?= nl2br(htmlspecialchars($message['text'])); ?>
		<br/><br/><small><?=date('Y-m-d H:i:s', $message['datetime']); ?></small>
	</div>
</div>
<hr/>
<?php } // endforeach
} // endif

