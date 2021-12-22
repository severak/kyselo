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

Flight::route('/act/restore', function() {
    $user = Flight::user();
    if (!($user && $user['id']==1)) Flight::forbidden();

    $rows = Flight::rows();

    $blogs = $rows->more('blogs', ['is_visible'=>1, 'is_group'=>0], ['name'=>'ASC'], 999);
    $blogSelect = array_column($blogs, 'name', 'id');

    $form = new severak\forms\form(['method'=>'post']);
    $form->field('upload', ['type'=>'file', 'label'=>'Backup file']);
    $form->field('url_prefix', ['label'=>'Image URL prefix', 'required'=>true]);
    $form->field('blog', ['type'=>'select', 'options'=>$blogSelect]);
    $form->field('check', ['type'=>'submit', 'label'=>'Check it!']);

    // TODO - upload backupu
    // TODO - čtení řádku po řádce
    // TODO - krok 1: nalezení fotky a pokus o její stažení
    // TODO - krok 2: uložení do DB

    // TODO - kyselo_upload_tmp
    // TODO - kyselo_mirror_image


    Flight::render('header', ['title' => 'restore backup' ]);
    Flight::render('form', ['h2'=>'restore backup', 'form' => $form]);
    Flight::render('footer', []);
});
