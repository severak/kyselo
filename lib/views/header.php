<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title><?php echo $title; ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="/st/css/pure/pure.css">
	<link rel="stylesheet" href="/st/css/font-awesome/css/font-awesome.css">
	<link rel="stylesheet" href="/st/css/kyselo/kyselo.css?v=2018-01-11">
	<meta property="og:title" content="<?php $title; ?>" />
	<script src="/st/js/zepto.min.js"></script>
	<script src="/st/js/medium-editor.min.js"></script>
	<link rel="stylesheet" href="/st/css/medium-editor.min.css" type="text/css" media="screen" charset="utf-8">
	<link rel="stylesheet" href="/st/css/themes/default.min.css" type="text/css" media="screen" charset="utf-8">
</head>
<body class="<?php if ($_SESSION['hide_nsfw']) echo 'kyselo-hide-nsfw'; ?>">
	<!-- hlavni menu -->
	<div class="pure-menu pure-menu-horizontal pure-menu-fixed kyselo-dark">
        <ul class="pure-menu-list">
	<?php if (!empty($_SESSION['user'])): ?>
            <li class="pure-menu-item"><a href="/<?= $_SESSION['user']['name']; ?>" class="pure-menu-link"><i class="fa fa-home"></i> My blog</a></li>
            <li class="pure-menu-item pure-menu-allow-hover pure-menu-has-children">
                <a href="#" class="pure-menu-link">Groups</a>
                <ul class="pure-menu-children">
		    <?php foreach($_SESSION['user']['groups'] as $group): ?>
                    <li class="pure-menu-item"><a href="/<?= $group['name']; ?>" class="pure-menu-link"><?= $group['name']; ?></a></li>
                    <?php endforeach; ?>
		    <li class="pure-menu-item"><a href="/act/groups" class="pure-menu-link">Find &amp; create…</a></li>
                </ul>
            </li>
            <li class="pure-menu-item pure-menu-allow-hover pure-menu-has-children">
                <a href="#" class="pure-menu-link">People</a>
                <ul class="pure-menu-children">
                    <li class="pure-menu-item"><a href="/<?= $_SESSION['user']['name']; ?>/friends" class="pure-menu-link">My friends</a></li>
                    <!-- <li class="pure-menu-item"><a href="#" class="pure-menu-link">My followers</a></li> -->
                    <li class="pure-menu-item"><a href="/<?= $_SESSION['user']['name']; ?>/fof" class="pure-menu-link">Friends of a friend</a></li>
                    <li class="pure-menu-item"><a href="/act/invite" class="pure-menu-link">Find &amp; invite…</a></li>
                </ul>
            </li>
            <li class="pure-menu-item pure-menu-allow-hover pure-menu-has-children">
                <a href="#" class="pure-menu-link"><i class="fa fa-coffee"></i></a>
                <ul class="pure-menu-children">
                    <li class="pure-menu-item"><a href="/act/logout" class="pure-menu-link"><i class="fa fa-sing-out"></i>logout</a></li>
                </ul>
            </li>
	<?php else: ?>
		<li class="pure-menu-item"><a href="/all" class="pure-menu-link"><i class="fa fa-home"></i> all blogs</a></li>
		<li class="pure-menu-item"><a href="/act/login" class="pure-menu-link"> login</a></li>
		<li class="pure-menu-item"><a href="/act/register" class="pure-menu-link"> sign-up</a></li>
	<?php endif; ?>
		<li class="pure-menu-item"><a href="#" class="pure-menu-link" id="kyselo_nsfw_switch"><span class="show">show</span>/<span class="hide">hide</span> NSFW</a></li>
        </ul>
		
    </div>
    <!-- /hlavni menu -->
    <div class="kyselo-content">
<?php if (!empty($_SESSION['flash'])) {
	while ($message = array_shift($_SESSION['flash'])) {
		echo '<div class="kyselo-message kyselo-message-'.$message['class'].'">'.htmlspecialchars($message['msg']).'</div>';
	}
}	
	