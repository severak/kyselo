#!/usr/bin/env php
<?php

use flight\template\View;
use kyselo\backup\format;

if (php_sapi_name()!='cli') {
    die('ERROR: This script is only for command line.');
}

if (!file_exists(__DIR__ . '/config.php')) {
    die("ERROR \nKyselo not installed.\n");
}

$config = require __DIR__ . '/config.php';

require dirname(__FILE__) . "/lib/flight/autoload.php";
\flight\core\Loader::addDirectory(dirname(__FILE__) . '/lib' );
\flight\core\Loader::addDirectory(dirname(__FILE__) . '/lib/flourish' );

/**
 * Command line assistant for Kyselo instance.
 */
class kyselo
{
    public function __construct($config)
    {
        $pdo = new PDO('sqlite:' . __DIR__ . '/' .  $config['database'], null, null, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
        $this->rows = new severak\database\rows($pdo);
    }

    /**
     * Generates backup.
     *
     * @param string $blog for blog.
     */
    public function backup($blog)
    {
        global $config;

        if (!set_time_limit(0)) {
            die(sprintf('Not enough time for import - just %d s.', ini_get('max_execution_time')));
        }

        $blogData = $this->rows->one('blogs', ['name'=>$blog]);
        if (!$blogData) {
            die(sprintf('Blog %s not found!', $blog));
        }

        @mkdir(__DIR__ . '/pub/backup/');

        $zipPrefix = substr(uniqid('', true), -5);;
        $zipName = fFilesystem::makeUniqueName(__DIR__ . '/pub/backup/' . $blog.  '-' . $zipPrefix, 'zip');

        $postsCount = $this->rows->count('posts', ['blog_id'=>$blogData['id'], 'is_visible'=>1]);

        $zip = new ZipArchive();
        $zip->open($zipName, ZipArchive::CREATE);

        $includedAvatars = [];

        $formatter = new kyselo\backup\format();
        $filter = new kyselo\timeline($this->rows);
        $filter->mode = 'own';
        $filter->blogId = $blogData['id'];

        $pages =  $filter->countPages();
        $page = 0;

        $view = new View(__DIR__ . '/lib/views');
        $site_url = $config['site_url'];

        $jsonl = json_encode(['is_metadata'=>true, 'count'=>$postsCount, 'blog'=>['name'=>$blogData['name'], 'title'=>$blogData['title'], 'description'=>$blogData['about'], 'avatar_url'=>$blogData['avatar_url'], 'since'=>$blogData['since'], 'custom_css'=>$blogData['custom_css']]]) . PHP_EOL;

        do {
            $posts = $filter->posts();
            foreach ($posts as $post) {
                // saving avatars
                if (!in_array($post['avatar_url'], $includedAvatars)) {
                    $zip->addFile(__DIR__ . '/' . $post['avatar_url'], ltrim($post['avatar_url'], '/'));
                    $zip->setCompressionName(ltrim($post['avatar_url'], '/'), ZipArchive::CM_STORE);
                    $includedAvatars[] = $post['avatar_url'];
                }

                if (!empty($post['group_avatar_url']) && !in_array($post['group_avatar_url'], $includedAvatars)) {
                    $zip->addFile(__DIR__ . '/' . $post['group_avatar_url'], ltrim($post['group_avatar_url'], '/'));
                    $zip->setCompressionName(ltrim($post['group_avatar_url'], '/'), ZipArchive::CM_STORE);
                    $includedAvatars[] = $post['group_avatar_url'];
                }

                // saving image
                if ($post['type']==4) {
                    $zip->addFile(__DIR__ . '/' . $post['url'], ltrim($post['url'], '/'));
                    $zip->setCompressionName(ltrim($post['url'], '/'), ZipArchive::CM_STORE);
                }
                $jsonl .= $formatter->post2backup($post);
            }

            $pageName = ($page > 0) ? sprintf('page%04d.html', $page) : 'index.html';
            $nextPage  = ($filter->moreSince) ? sprintf('page%04d.html', $page+1) : false;

            ob_start();
            $view->render('archive.php', ['posts'=>$posts, 'next_page'=>$nextPage,  'blog'=>$blogData, 'site_url'=>$site_url]);
            $pageContent = ob_get_clean();

            $zip->addFromString($pageName, $pageContent);

            $page++;

            $zip->close();
            $zip->open($zipName, ZipArchive::CREATE);

            echo sprintf('written page %d of %d', $page, $pages['total']) . PHP_EOL;

        } while ($filter->moveToNextPage());

        echo 'writing jsonl...' . PHP_EOL;
        $zip->addFromString($blog . '.jsonl', $jsonl);

        $style = <<<STYLE
/* bare bones CSS style to display something legible: */

img {
    height: auto;
    max-width: 100%;
}

.image.is-128x128 {
    height: 128px;
    width: 128px;
}

.image.is-64x64 {
    height: 64px;
    width: 64px;
}

.kyselo-container {
    max-width: 1152px;
    margin: auto;
}

.ub-cols {
    display: flex;
    box-sizing: border-box;
}

.kyselo-post {
    border-bottom: 1px solid gray;
}

.kyselo-post div {
    padding: 1em;
}

/* NSFW pics */
.is-nsfw .kyselo-post-body img {
    background-color: lightgrey;
    filter: blur(10px) sepia(1) opacity(0.3);
    -webkit-filter: blur(10px) sepia(1) opacity(0.3);
}

.kyselo-image {
    max-height: 80vh;
}

.kyselo-image-square {
    max-height: 80vh;
}

.kyselo-next-page {
    font-size: x-large;
    display: block;
    text-align: center;
    padding: 1.5em;
}

/* your custom style: */

STYLE;

        $style .= $blogData['custom_css'];

        $zip->addFromString('style.css', $style);

        echo 'finishing zip...' . PHP_EOL;

        $zip->setArchiveComment(sprintf('Backup for %s/%s generated at %s', $site_url, $blog, date('Y-m-d')));

        $zip->close();

        echo 'Written to ' . $site_url . '/pub/backup/' . pathinfo($zipName, PATHINFO_BASENAME) . PHP_EOL;
    }
}

\severak\cligen\app::run(new kyselo($config));
