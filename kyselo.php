#!/usr/bin/env php
<?php

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
     * @param string $blog      for blog.
     * @param string $dropTo    directory where to drop ZIP file.
     * @param string $urlPrefix returned URL prefix.
     */
    public function backup($blog, $dropTo='./pub/backup', $urlPrefix='https://backup.kyselo.eu/')
    {
        if (!set_time_limit(0)) {
            die(sprintf('Not enough time for import - just %d s.', ini_get('max_execution_time')));
        }

        $blogData = $this->rows->one('blogs', ['name'=>$blog]);
        if (!$blogData) {
            die(sprintf('Blog %s not found!', $blog));
        }

        @mkdir(__DIR__ . '/pub/backup/');
        $zipName = fFilesystem::makeUniqueName(__DIR__ . '/pub/backup/' . $blog, 'zip');

        $postsCount = $this->rows->count('posts', ['blog_id'=>$blogData['id'], 'is_visible'=>1]);

        $zip = new ZipArchive();
        $zip->open($zipName, ZipArchive::CREATE);

        $formatter = new kyselo\backup\format();
        $filter = new kyselo\timeline($this->rows);
        $filter->mode = 'own';
        $filter->blogId = $blogData['id'];

        $pages =  $filter->countPages();
        $page = 0;

        $jsonl = json_encode(['is_metadata'=>true, 'count'=>$postsCount, 'blog'=>['name'=>$blogData['name'], 'title'=>$blogData['title'], 'description'=>$blogData['about'], 'avatar_url'=>$blogData['avatar_url'], 'since'=>$blogData['since'], 'custom_css'=>$blogData['custom_css']]]) . PHP_EOL;

        do {
            $posts = $filter->posts();
            foreach ($posts as $post) {
                if ($post['type']==4) {
                    $zip->addFile(__DIR__ . '/' . $post['url'], ltrim($post['url'], '/'));
                    $zip->setCompressionName(ltrim($post['url'], '/'), ZipArchive::CM_STORE);
                }
                $jsonl .= $formatter->post2backup($post);
            }

            // todo - write page backup
            // todo - write index.html and page0000.html

            $page++;

            $zip->close();
            $zip->open($zipName, ZipArchive::CREATE);

            echo sprintf('written page %d of %d', $page, $pages['total']) . PHP_EOL;

        } while ($filter->moveToNextPage());

        echo 'writing jsonl...' . PHP_EOL;
        $zip->addFromString($blog . '.jsonl', $jsonl);


        echo 'finishing zip...' . PHP_EOL;

        // todo - writte date to the comment

        $zip->close();

        echo 'Written to ' . $zipName;
    }
}

\severak\cligen\app::run(new kyselo($config));
