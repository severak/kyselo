<?php

Flight::route('/act/backup', function (){
    // test of backup system
    Flight::requireLogin();
    $user = Flight::user();
    $rows = Flight::rows();

    header('Content-type: text/plain; charset=utf8');
    header('Content-Disposition: attachment; filename="'.$user['name'].'.jsonl"');

    $postsCount = $rows->count('posts', ['blog_id'=>$user['id'], 'is_visible'=>1]);
    $blog = $rows->one('blogs', $user['blog_id']);

    if (!$postsCount) {
        echo '// no posts to archive';
        exit;
    }

    ob_end_clean();
    echo json_encode(['is_metadata'=>true, 'count'=>$postsCount, 'blog'=>['name'=>$blog['name'], 'title'=>$blog['title'], 'description'=>$blog['about'], 'avatar_url'=>$blog['avatar_url'], 'since'=>$blog['since'], 'custom_css'=>$blog['custom_css']]]) . PHP_EOL;

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
    $starttime = microtime(true);
    $user = Flight::user();
    if (!($user && $user['id']==1)) Flight::forbidden();

    $rows = Flight::rows();

    if (!set_time_limit(0)) {
        Flight::flash(sprintf('Not enough time for import - just %d s.', ini_get('max_execution_time')));
    }

    $blogs = $rows->more('blogs', ['is_visible'=>1, 'is_group'=>0], ['name'=>'ASC'], 999);
    $blogSelect = array_column($blogs, 'name', 'id');

    $form = new severak\forms\form(['method'=>'post']);
    $form->field('blog_id', ['label'=>'Target blog', 'type'=>'select', 'options'=>$blogSelect]);
    $form->field('upload', ['type'=>'file', 'label'=>'Backup file']);
    $form->field('url_prefix', ['label'=>'Image URL prefix', 'required'=>true]);
    $form->field('image_method', ['label'=>'Image upload method', 'type'=>'select', 'options'=>['ftp'=>'by FTP', 'mirror'=>'download & mirror', 'save_prefix'=>'save absolute prefix']]);
    $form->field('check', ['type'=>'submit', 'label'=>'Import data']);

    $formatter = new kyselo\backup\format();

    $request = Flight::request();
    if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {

        $uploadTmp = $_FILES['upload']['tmp_name'];
        $imageMethod = $form->values['image_method'];
        try {
            $file = new SplFileObject($uploadTmp);

            if ($imageMethod=='mirror' || $imageMethod=='save_prefix') {
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
            }

            if ($form->isValid) {
                // download and save it all
                $file->rewind();
                $postCount = $imported = 0;

                ob_end_clean();
                echo 'importing...' . PHP_EOL;

                $rows->begin();

                while (!$file->eof()) {
                    $post = $formatter->backup2post($file->fgets());

                    if (isset($post['is_metadata'])) {
                        $postCount = $post['count'];
                        continue; // we don't want import metadata
                    }

                    if (!empty($post['type'])) {
                        if ($post['type']==4) {
                            if ($imageMethod=='mirror') {
                                $post['url'] = kyselo_mirror_image($form->values['url_prefix'] . $post['url']);
                            } elseif ($imageMethod=='save_prefix') {
                                $post['url'] = $form->values['url_prefix'] . $post['url'];
                            } elseif ($imageMethod=='ftp') {
                                // we have already imported those images via FTP
                            }
                        }
                        $post['blog_id'] = $form->values['blog_id'];
                        $post['author_id'] = $form->values['blog_id'];
                        $postId = $rows->insert('posts', $post);
                        if (!empty($post['tags'])) {
                            foreach (explode(' ', $post['tags']) as $tag) {
                                $rows->insert('post_tags', ['blog_id'=>$post['blog_id'], 'post_id'=>$postId, 'tag'=>$tag]);
                            }
                        }
                        $imported++;

                        echo '.';
                    }
                }

                $endtime = microtime(true); // Bottom of page

                Flight::flash(sprintf('Blog imported - %d posts in %f s.', $imported, $endtime - $starttime), true);
                $rows->commit();

                if ($imported != $postCount) {
                    Flight::flash(sprintf('Only %d of %d posts were imported.', $imported, $postCount), false);
                }

                Flight::redirect('/' . $blogSelect[$form->values['blog_id']]);
            }
        } catch (Exception $e) {
            $form->error('upload', $e->getMessage());
        }
    }


    Flight::render('header', ['title' => 'restore backup' ]);
    Flight::render('form', ['h2'=>'restore backup', 'form' => $form]);
    Flight::render('footer', []);
});
