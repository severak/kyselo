<?php
// dialogue with someone
// arguments:
// - messages
// - with

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
foreach ($messages as $idx=>$message) {
?>
<div class="media" <?php if ($idx==count($messages)-1) echo ' id="last"'; ?> >
	<div class="media-left kyselo-big-profile">
		<a href="/<?=$message['name']; ?>">
		<strong><?=htmlspecialchars($message['name']); ?></strong>:
		<img src="<?=kyselo_small_image($message['avatar_url'], 128, true); ?>" class="image is-128x128 is-64x64-mobile">
		</a>
	</div>
	<div class="media-content">
		<?= nl2br(htmlspecialchars($message['text'])); ?>
		<br/><br/><small><?=date('Y-m-d H:i:s', $message['datetime']); ?></small>
	</div>
</div>
<hr/>
<?php } // endforeach
} // endif
