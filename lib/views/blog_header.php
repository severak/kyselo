<?php
// blog header
// arguments:
// - $title
// - $about
// - $avatar_url
?>
<div class="pure-g">
	<div class="pure-u-1-5"><img class="pure-img" src="<?php echo $avatar_url; ?>"/></div>
	<div class="pure-u-4-5">
		<h1><?php echo $title; ?></h1>
		<small><?php echo $about; ?></small>
	</div>
</div>
<hr>
