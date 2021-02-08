<?php
if (php_sapi_name()!='cli') {
    die('ERROR: This script is only for command line.');
}

if (!file_exists(__DIR__ . '/config.php')) {
    die("ERROR \nKyselo not installed.\n");
}
$config = require __DIR__ . '/config.php';

if (empty($argv[1])) {
    die("ERROR \nBad import parameters. \nPlease specifiy: \n- destination username\n");
}

$blogName = $argv[1];

if (!is_dir(__DIR__ . '/pub/'.  $blogName)) {
    die("ERROR \nThere is no dir: /pub/" . $blogName . "\n");
}

require dirname(__FILE__) . "/lib/flight/autoload.php";

\flight\core\Loader::addDirectory(dirname(__FILE__) . '/lib' );

$pdo = new PDO('sqlite:' . __DIR__ . '/' .  $config['database'], null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$rows = new severak\database\rows($pdo);

$blog = $rows->one('blogs', ['name'=>$blogName]);

if (!$blog) {
    die("ERROR\nThere is no blog ". $blogName);
}

$blogId = $blog['id'];

$timestamp = new DateTime('2007-01-01');

$d = dir(__DIR__ . "/pub/" . $blogName);
while (false !== ($entry = $d->read())) {
    $ext = pathinfo($entry, PATHINFO_EXTENSION);
    if ($ext=='txt') continue; // skipping TXT

    $timestamp->add(new DateInterval('PT5M'));

    $post = [
        'blog_id' => $blogId,
        'author_id' => $blogId,
        'guid' => generate_uuid(),
        'datetime' => $timestamp->getTimestamp(),
        'reposts_count' => 0,
        'comments_count' => 0,
        'url' => '/pub/' . $blogName . '/' . $entry
    ];

    if (is_file(__DIR__ . '/pub/' . $blogName . '/' . pathinfo($entry, PATHINFO_FILENAME) . '.txt')) {
        $text = file_get_contents(__DIR__ . '/pub/' . $blogName . '/' . pathinfo($entry,PATHINFO_FILENAME) . '.txt');
        $text = iconv('WINDOWS-1250', 'UTF-8', $text);

        $text = htmlspecialchars($text, ENT_HTML5);

        $text = turnUrlIntoHyperlink($text);
        $text = nl2br($text);

        echo $timestamp->format('Y-m-d H:i:S') . PHP_EOL;
        echo __DIR__ . '/pub/' . $blogName . '/' . pathinfo($entry, PATHINFO_FILENAME) . '.txt' . PHP_EOL;
        echo $text . PHP_EOL . '------==================-----------' . PHP_EOL;

        $post['body'] = $text;
    }

    $rows->insert('posts', $post);

    echo $entry."\n";
}
$d->close();

// var_dump($blog);

echo 'FINISHED' . PHP_EOL;

function turnUrlIntoHyperlink($string){
    //The Regular Expression filter
    $reg_exUrl = "/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:'\".,<>?«»“”‘’]))/";

    // Check if there is a url in the text
    if(preg_match_all($reg_exUrl, $string, $url)) {

        // Loop through all matches
        foreach($url[0] as $newLinks){
            if(strstr( $newLinks, ":" ) === false){
                $link = 'http://'.$newLinks;
            }else{
                $link = $newLinks;
            }

            // Create Search and Replace strings
            $search  = $newLinks;
            $replace = '<a href="'.$link.'">'.$link.'</a>';
            $string = str_replace($search, $replace, $string);
        }
    }

    //Return result
    return $string;
}

function generate_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
        mt_rand( 0, 0xffff ),
        mt_rand( 0, 0x0fff ) | 0x4000,
        mt_rand( 0, 0x3fff ) | 0x8000,
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}