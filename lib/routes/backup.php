<?php

Flight::route('/act/backup', function (){
    // test of backup system
    Flight::requireLogin();
    $user = Flight::user();
    $rows = Flight::rows();

    header('Content-type: text/plain; charset=utf8');
    header('Content-Disposition: attachment; filename="'.$user['name'].'.jsonl"');

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
    $form->field('blog_id', ['type'=>'select', 'options'=>$blogSelect]);
    $form->field('check', ['type'=>'submit', 'label'=>'Check it!']);

    $formatter = new kyselo\backup\format();

    $request = Flight::request();
    if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
        $uploadTmp = $_FILES['upload']['tmp_name'];
        try {
            $file = new SplFileObject($uploadTmp);

            // check if we can download something
            while (!$file->eof()) {
                $post = $formatter->backup2post($file->fgets());
                if (!empty($post['type']) && $post['type']==4) {
                    $url = kyselo_mirror_image($form->values['url_prefix'] . $post['url']);
                    if (!$url) {
                        $form->error('url_prefix', 'We cannot download images from here.');
                    }
                    break;
                }
            }

            if ($form->isValid) {
                // download and save it all
                $file->rewind();

                ob_end_clean();
                echo 'importing...' . PHP_EOL;

                while (!$file->eof()) {
                    $post = $formatter->backup2post($file->fgets());
                    if (!empty($post['type'])) {
                        if ($post['type']==4) {
                            $post['url'] = kyselo_mirror_image($form->values['url_prefix'] . $post['url']);
                        }
                        $post['blog_id'] = $form->values['blog_id'];
                        $post['author_id'] = $form->values['blog_id'];
                        $postId = $rows->insert('posts', $post);
                        if (!empty($post['tags'])) {
                            foreach (explode(' ', $post['tags']) as $tag) {
                                $rows->insert('post_tags', ['blog_id'=>$post['blog_id'], 'post_id'=>$postId, 'tag'=>$tag]);
                            }
                        }

                        echo '.';
                    }
                }

                Flight::flash('Blog imported.', true);
                Flight::redirect('/act/restore');
            }
        } catch (Exception $e) {
            $form->error('upload', $e->getMessage());
        }
    }


    Flight::render('header', ['title' => 'restore backup' ]);
    Flight::render('form', ['h2'=>'restore backup', 'form' => $form]);
    Flight::render('footer', []);
});
