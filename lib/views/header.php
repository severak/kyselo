<?php
$msgCount = 0;
$notificationsCount = 0;
if (!empty($_SESSION['user'])) {
    $rows = Flight::rows();
    $msgCount = $rows->execute($rows->query('SELECT COUNT(cnt) FROM (
        SELECT COUNT(*) AS cnt
        FROM messages 
        WHERE id_to=? AND is_read=0
        GROUP BY id_from
        )', $_SESSION['user']['blog_id']))->fetchColumn();
    $notificationsCount = $rows->execute($rows->query('SELECT COUNT(*) AS cnt
        FROM notifications 
        WHERE id_to=? AND is_read=0', $_SESSION['user']['blog_id']))->fetchColumn();
}

$loggedIn = !empty($_SESSION['user']);
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : null;
$userAvatar = isset($_SESSION['user']['avatar_url']) ? $_SESSION['user']['avatar_url'] : null;
$groups = isset($_SESSION['user']['groups']) ? $_SESSION['user']['groups'] : [];
?>
<!doctype HTML>
<html class="has-navbar-fixed-top">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $title; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/st/css/bulma/bulma.css">
	<link rel="stylesheet" href="/st/css/font-awesome/css/font-awesome.css">
	<link rel="stylesheet" href="/st/css/kyselo/kyselo.css">
	<script src="/st/js/zepto.min.js"></script>
	<script src="/st/js/medium-editor.min.js"></script>
	<link rel="stylesheet" href="/st/css/medium-editor.min.css" type="text/css" media="screen" charset="utf-8">
	<link rel="stylesheet" href="/st/css/themes/default.min.css" type="text/css" media="screen" charset="utf-8">
	<?php if (isset($rss)) { ?>
	<link rel="alternate" type="application/rss+xml" href="<?=kyselo_url($rss); ?>" />
	<?php } ?>
	<?php if (!empty($ogp_post)) { ?>
	<meta property="og:type" content="website" />
	<?php if ($ogp_post['type']==4) { ?>
	<meta property="og:image" content="<?=kyselo_url('') . esc($ogp_post['url']); ?>" />
	<?php } else { ?>
	<meta property="og:title" content="<?=($ogp_post['title'] ? $ogp_post['title'] : $ogp_post['slug_name']); ?>" />
	<meta property="og:image" content="<?=kyselo_url(''); ?>/st/img/undraw_different_love_a3rg.png" />
	<?php } ?>
	<meta property="og:url" content="<?=kyselo_url('/%s/post/%d', [$ogp_post['slug_name'], $ogp_post['id']]); ?>" />
	<?php } ?>
	<?php if (!empty($ogp_blog)) { ?>
	<meta property="og:type" content="website" />
	<meta property="og:image" content="<?=kyselo_url('') . kyselo_small_image($ogp_blog['avatar_url'], 128, true); ?>" />
	<meta property="og:title" content="<?=$ogp_blog['name']; ?>" />
	<meta property="og:description" content="<?=strip_tags($ogp_blog['about']); ?>" />
	<meta property="og:url" content="<?=kyselo_url('/%s', [$ogp_blog['name']]); ?>" />
	<?php } ?>
</head>
<body class="<?php if (empty($_SESSION['show_nsfw'])) echo 'kyselo-hide-nsfw'; ?>">
<!-- hlavni menu -->
<nav class="navbar is-fixed-top is-dark" role="navigation" aria-label="main navigation">
<div class="container">
  <div class="navbar-brand">
	<a class="navbar-item has-text-primary" href="/all"><i class="fa fa-smile-o"></i>&nbsp;<?=Flight::config('site_name'); ?></a>

    <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="mainMenu">
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
      <span aria-hidden="true"></span>
    </a>
  </div>

  <div id="mainMenu" class="navbar-menu">
    <div class="navbar-start">

	<?php if ($loggedIn) { ?>

	<div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link" href="/all">people&nbsp;<i class="fa fa-users"></i></a>
        <div class="navbar-dropdown">
          <a class="navbar-item" href="/<?=$userName;?>/friends"><i class="fa fa-users"></i>&nbsp;my friends</a>
          <a class="navbar-item" href="/all"><i class="fa fa-globe"></i>&nbsp;everyone</a>
		</div>
      </div>

	 <div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link" href="/act/groups">groups&nbsp;<i class="fa fa-umbrella"></i></a>
        <div class="navbar-dropdown">
		<?php foreach ($groups as $group) { ?>
          <a class="navbar-item" href="/<?=$group['name'];?>"><img src="<?= kyselo_small_image($group['avatar_url'],32,true); ?>" style="width: 1em">&nbsp;<?= $group['name']; ?></a>
          <?php } ?>
		 <?php if (count($groups)) { ?>
		<hr class="navbar-divider">
		<?php } // count($groups) ?>
		<a class="navbar-item" href="/act/groups"><i class="fa fa-umbrella"></i>&nbsp;find&amp;create</a>
		</div>
      </div>

	<a class="navbar-item" href="#" id="kyselo_nsfw_switch"><span class="show">show</span>/<span class="hide">hide</span> NSFW&nbsp;<i class="fa fa-eye<?php if (empty($_SESSION['show_nsfw'])) echo '-slash'; ?>"></i></a>
    <?php if (Flight::config('chat_websocket_url')) { ?>
        <a class="navbar-item" href="/act/chat">chat&nbsp;<i class="fa fa-comments"></i></a>
    <?php } ?>
    <a class="navbar-item" href="/">about&nbsp;<i class="fa fa-question"></i></a>


	<?php } else { ?>
      <a class="navbar-item" href="/all">people&nbsp;<i class="fa fa-users"></i></a>
      <a class="navbar-item" href="/act/groups">groups&nbsp;<i class="fa fa-umbrella"></i></a>
      <a class="navbar-item" href="#" id="kyselo_nsfw_switch"><span class="show">show</span>/<span class="hide">hide</span>&nbsp;NSFW&nbsp;<i class="fa fa-eye<?php if (empty($_SESSION['show_nsfw'])) echo '-slash'; ?>"></i></a>
      <a class="navbar-item" href="/">about&nbsp;<i class="fa fa-question"></i></a>
    <?php } // $loggedIn ?>

	</div>

    <div class="navbar-end">
	<?php if ($loggedIn) { ?>

	<div class="navbar-item has-dropdown is-hoverable">
        <a class="navbar-link"><img src="<?= kyselo_small_image($userAvatar,32,true); ?>" style="width: 1em">&nbsp;<?=$userName; ?></a>
        <div class="navbar-dropdown">
          <a class="navbar-item" href="/<?=$userName;?>"><i class="fa fa-home"></i>&nbsp;my blog</a>
          <a class="navbar-item" href="/<?=$userName;?>/friends"><i class="fa fa-users"></i>&nbsp;my friends</a>
          <hr class="navbar-divider">
          <a class="navbar-item" href="/act/messages/inbox"><i class="fa fa-envelope"></i>&nbsp;inbox</a>
          <a class="navbar-item" href="/act/messages/outbox"><i class="fa fa-paper-plane"></i>&nbsp;outbox</a>
          <a class="navbar-item" href="/act/notifications"><i class="fa fa-bell"></i>&nbsp;notifications</a>
		  <hr class="navbar-divider">
          <a class="navbar-item" href="/act/random"><i class="fa fa-random"></i>&nbsp;random post</a>
          <a class="navbar-item" href="/act/random?from=<?=$userName; ?>"><img src="<?= kyselo_small_image($userAvatar,32,true); ?>" style="width: 1em">&nbsp;my random post&nbsp;<i class="fa fa-random"></i></a>
          <hr class="navbar-divider">
          <a class="navbar-item" href="/act/backup"><i class="fa fa-save"></i>&nbsp;download backup</a>
          <?php if($userName=='admin') { ?>
          <a class="navbar-item" href="/act/restore"><i class="fa fa-cloud-upload"></i>&nbsp;restore backup</a>
          <a class="navbar-item" href="/act/stats"><i class="fa fa-table"></i>&nbsp;usage stats</a>

          <?php } // if admin ?>
          <hr class="navbar-divider">
          <a class="navbar-item" href="/act/logout"><i class="fa fa-sign-out"></i>&nbsp;logout</a>
        </div>
      </div>
	</div>
  </div>
  <?php } else { ?>
	<div class="navbar-item">
        <div class="buttons">
		<a class="button is-warning" href="/act/register"><i class="fa fa-sign-in"></i>&nbsp;<strong>register</strong></a>
          <a class="button is-success" href="/act/login"><i class="fa fa-key"></i>&nbsp;login</a>
        </div>
      </div>

  <?php } // $loggedIn ?>

  </div>
</nav>
<!-- /hlavni menu -->

<div class="container p-2 kyselo-container">
<?php if ($msgCount>0 && !isset($noMessages)) { ?>
	<div class="message is-info"><div class="message-body"><i class="fa fa-envelope"></i>&nbsp;you have <a href="/act/messages/inbox"><?=$msgCount; ?> new messages</a></div></div>
	<?php } ?>

<?php if ($notificationsCount>0  && !isset($noMessages)) { ?>
    <div class="message is-info"><div class="message-body"><i class="fa fa-bell"></i>&nbsp;you have <a href="/act/notifications"><?=$notificationsCount; ?> new notifications</a></div></div>
<?php } ?>
<?php if (!empty($_SESSION['flash']) && !isset($noMessages)) {
	while ($message = array_shift($_SESSION['flash'])) {
		echo '<div class="message is-'.$message['class'].'"><div class="message-body">'.htmlspecialchars($message['msg']).'</div></div>';
	}
}
