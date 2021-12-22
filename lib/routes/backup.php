<?php

Flight::route('/act/backup', function (){
    // test of backup system
    Flight::requireLogin();
    $user = Flight::user();
    $rows = Flight::rows();

    header('Content-type: text/plain; charset=utf8');

    $postsCount = $rows->count('posts', ['blog_id'=>$user['id'], 'is_visible'=>1]);

    if (!$postsCount) {
        echo '// no posts to archive';
        exit;
    }

    ob_end_clean();
    echo '// ' . $postsCount . ' more post following ' . PHP_EOL;

    $formatter = new kyselo\backup\format();
    $filter = new kyselo\timeline(Flight::rows());
    $filter->mode = 'own';
    $filter->blogId = $user['id'];

    do {
        $posts = $filter->posts();
        foreach ($posts as $post) {
            $formatter->post2backup($post);
        }
    } while ($filter->moveToNextPage());
    die;
});
