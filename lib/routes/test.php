<?php
// for testing new features
Flight::route('/act/test', function(){
	throw new Exception('Testing exception page.');
});

Flight::route('/act/backup', function (){
    // test of backup system
    Flight::requireLogin();
    $user = Flight::user();
    $rows = Flight::rows();

    header('Content-type: text/plain; charset=utf8');

    $postsCount = $rows->count('posts', ['blog_id'=>$user['id']]);

    if (!$postsCount) {
        echo '// no posts to archive';
        exit;
    }

    $since = $lastPost = null;

    ob_end_clean();
    echo '// ' . $postsCount . ' more post following ' . PHP_EOL;

    do {
        $lastPost = null;
        $where = $rows->fragment('blog_id = ? AND is_visible=1', [$user['id']]);

        if ($since) {
            $where = $where->add(' AND datetime <= ?', [$since]);
        }

        $posts = $rows->more('posts', $where, ['datetime'=>'DESC'], 16);

        if (count($posts)==16) {
            $lastPost = array_pop($posts);
            $since = $lastPost['datetime'];
        }

        foreach ($posts as $post) {
            $tags = [];
            if (!empty($post['tags'])) {
                $tags = explode(' ', $post['tags']);
            }

            $out = ['id'=>$post['id'], 'posted_by'=>$user['name'], 'datetime'=>$post['datetime'], 'tags'=>$tags, 'is_repost'=>$post['repost_of'] ? 1 : 0];

            if ($post['type']==1) {
                $out['type'] = 'text';
                $out['title'] = $post['title'];
                $out['html'] = $post['body'];
            } else if ($post['type']==2) {
                $out['type'] = 'link';
                $out['title'] = $post['title'];
                $out['description'] = $post['body'];
                $out['url'] = $post['source'];
            } else if ($post['type']==3) {
                $out['type'] = 'quote';
                $out['byline'] = $post['title'];
                $out['quote'] = $post['body'];
            } else if ($post['type']==4) {
                $out['type'] = 'image';
                $out['url'] = $post['url'];
                $out['description'] = $post['body'];
                $out['source'] = $post['source'];
            } else if ($post['type']==5) {
                $out['type'] = 'video';
                $out['title'] = $post['title'];
                $out['body'] = $post['body'];
                $out['source'] = $post['source'];
            }

            echo json_encode($out) . PHP_EOL;
        }

    } while ($lastPost);
    die;
});
