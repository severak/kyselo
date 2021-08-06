<?php foreach ($notifications as $notification) { ?>
<div class="message <?php if (!$notification['is_read']) { ?>is-info<?php } ?>"><div class="message-body"><?=$notification['text']; ?></div></div>
<?php } ?>
<?php if (empty($notification)) { ?>
    <div class="message"><div class="message-body"><i class="fa fa-bell"></i> no notifications yet</div></div>
<?php } ?>
