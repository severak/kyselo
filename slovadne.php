<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Slova dne</title>
</head>
<body>
<?php
ini_set('display_errors', 1);
$db = new PDO('sqlite:'.__DIR__.'/db/kyselo.sqlite'); // toto změnit

$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
$posty = $db->query('SELECT id, title, datetime FROM posts WHERE blog_id=3 AND title!="" AND is_visible=1 ORDER BY datetime DESC');

$ts = 0;
$lastTs = 0;

foreach ($posty as $post) {
	$ts = (int) $post['datetime'];
	if (date('Y', $ts) != date('Y', $lastTs)) {
		echo '<h1>'.date('Y', $ts).'</h1>';
	}
	if (date('m', $ts) != date('m', $lastTs)) {
		echo '<h2>'.date('m', $ts).'</h2>';
	}
	echo '<a href="http://kyselo.svita.cz/slovodne/post/'.$post['id'].'">'.htmlspecialchars($post['title']).'</a><br>';
	$lastTs = $ts;
}
?>
</body>
</html>