<?php
$msgCount = 0;
if (!empty($_SESSION['user'])) {
    $rows = Flight::rows();
    $msgCount = $rows->execute($rows->query('SELECT COUNT(cnt) FROM (
        SELECT COUNT(*) AS cnt
        FROM messages 
        WHERE id_to=? AND is_read=0
        GROUP BY id_from
        )', $_SESSION['user']['blog_id']))->fetchColumn(); 
}
?>
<!doctype HTML>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $title; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/st/css/pure/pure.css">
	<link rel="stylesheet" href="/st/css/font-awesome/css/font-awesome.css">
	<link rel="stylesheet" href="/st/css/kyselo/kyselo.css?v=2020-09-08">
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
	<meta property="og:image" content="<?=kyselo_url('') . kyselo_small_image($ogp_blog['avatar_url'], 100, true); ?>" />
	<meta property="og:title" content="<?=$ogp_blog['name']; ?>" />
	<meta property="og:description" content="<?=strip_tags($ogp_blog['about']); ?>" />
	<meta property="og:url" content="<?=kyselo_url('/%s', [$ogp_blog['name']]); ?>" />
	<?php } ?>
</head>
<body class="<?php if (empty($_SESSION['show_nsfw'])) echo 'kyselo-hide-nsfw'; ?>">
	<!-- hlavni menu -->
	<div class="pure-menu pure-menu-horizontal pure-menu-fixed kyselo-dark">
        <ul class="pure-menu-list">
	<?php if (!empty($_SESSION['user'])): ?>
            <li class="pure-menu-item"><a href="/<?= $_SESSION['user']['name']; ?>" class="pure-menu-link"><i class="fa fa-home"></i> My blog</a></li>
            <li class="pure-menu-item pure-menu-allow-hover pure-menu-has-children">
                <a href="/act/groups" class="pure-menu-link"><i class="fa fa-umbrella"></i> Groups</a>
                <ul class="pure-menu-children">
		    <?php foreach($_SESSION['user']['groups'] as $group): ?>
                    <li class="pure-menu-item">
						<a href="/<?= $group['name']; ?>" class="pure-menu-link"><img src="<?= kyselo_small_image($group['avatar_url'],50,true); ?>" style="width: 1em">&nbsp;<?= $group['name']; ?></a>
					</li>
                    <?php endforeach; ?>
		    <li class="pure-menu-item"><a href="/act/groups" class="pure-menu-link"><i class="fa fa-umbrella"></i> Find &amp; create…</a></li>
                </ul>
            </li>
            <li class="pure-menu-item pure-menu-allow-hover pure-menu-has-children">
                <a href="/all" class="pure-menu-link"><i class="fa fa-users"></i> People</a>
                <ul class="pure-menu-children">
                    <li class="pure-menu-item"><a href="/<?= $_SESSION['user']['name']; ?>/friends" class="pure-menu-link"><i class="fa fa-users"></i>  My friends</a></li>
                    <li class="pure-menu-item"><a href="/all" class="pure-menu-link"><i class="fa fa-globe"></i>  Everyone</a></li>
                </ul>
            </li>
            <li class="pure-menu-item pure-menu-allow-hover pure-menu-has-children">
                <a href="#" class="pure-menu-link"><i class="fa fa-coffee"></i></a>
                <ul class="pure-menu-children">
                    <li class="pure-menu-item"><a href="/act/messages/inbox" class="pure-menu-link"><i class="fa fa-envelope"></i> inbox</a></li>
                    <li class="pure-menu-item"><a href="/act/messages/outbox" class="pure-menu-link"><i class="fa fa-paper-plane"></i> outbox</a></li>
                    <li class="pure-menu-item"><a href="/act/logout" class="pure-menu-link"><i class="fa fa-sign-out"></i> logout</a></li>
                </ul>
            </li>
	<?php else: ?>
		<li class="pure-menu-item"><a href="/all" class="pure-menu-link"><i class="fa fa-globe"></i> all blogs</a></li>
		<li class="pure-menu-item"><a href="/act/login" class="pure-menu-link"><i class="fa fa-key"></i>  login</a></li>
		<li class="pure-menu-item"><a href="/act/register" class="pure-menu-link"><i class="fa fa-sign-in"></i>  register</a></li>
	<?php endif; ?>
		<li class="pure-menu-item"><a href="#" class="pure-menu-link" id="kyselo_nsfw_switch"><span class="show">show</span>/<span class="hide">hide</span> NSFW</a></li>
        <?php if ($msgCount>0) { ?>
            <li class="pure-menu-item"><a href="/act/messages/inbox" class="pure-menu-link"><i class="fa fa-envelope"></i> <?=$msgCount; ?> new messages</a></li>
        <?php } ?>
        <li class="pure-menu-item"><a href="/" class="pure-menu-link"><i class="fa fa-question"></i>  about Kyselo</a></li>
        </ul>
		
    </div>
    <!-- /hlavni menu -->
    <div class="kyselo-content">
<?php if (!empty($_SESSION['flash'])) {
	while ($message = array_shift($_SESSION['flash'])) {
		echo '<div class="kyselo-message kyselo-message-'.$message['class'].'">'.htmlspecialchars($message['msg']).'</div>';
	}
}	
	