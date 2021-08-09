<?php
use \severak\database\rows;
// post a comment
Flight::route('/act/comment', function() {
    Flight::requireLogin();
    $user = Flight::user();
    /** @var severak\database\rows $rows */
    $rows = Flight::rows();
    $request = Flight::request();

    if ($request->method=='GET') {
        Flight::forbidden();
    }

    $postId = $_POST['post_id'];
    $text = $_POST['text'];
    $post = $rows->with('blogs', 'blog_id')->one('posts', ['id'=>$postId, 'is_visible'=>1]);
    if (empty($post)) {
        Flight::notFound();
    }

    $commentId = $rows->insert('comments', [
        'post_id' => $postId,
        'author_id' => $user['blog_id'],
        'datetime' => strtotime('now'),
        'text' => $text,
        'is_visible' => 1
    ]);

    $rows->execute($rows->fragment('UPDATE posts SET comments_count=comments_count+1 WHERE id=?', [$postId]));

    $mentioned = false;
    // notify mentioned users
    foreach (find_usernames($text) as $notifyUserName) {
        $notifyUser = $rows->one('blogs', ['name'=>$notifyUserName]);
        if (!$notifyUser) continue; // not existing user
        if ($notifyUser['is_group']) continue; // group
        if ($notifyUser['id']==$post['author_id']) $mentioned = true;

        $rows->insert('notifications', [
            'id_to' => $notifyUser['id'],
            'text' => sprintf('<i class="fa fa-comment"></i> <a href="/%s">%s</a> mentioned you in a <a href="/%s/post/%d#comment%d">comment</a>', $user['name'], $user['name'], $post['name'], $post['id'], $commentId),
            'datetime'=> strtotime('now')
        ]);
    }

    if (!$mentioned) {
        // notify post author
        $rows->insert('notifications', [
            'id_to' => $post['author_id'],
            'text' => sprintf('<i class="fa fa-comment"></i> <a href="/%s">%s</a> <a href="/%s/post/%d#comment%d">commented</a> on your post', $user['name'], $user['name'], $post['name'], $post['id'], $commentId),
            'datetime'=> strtotime('now')
        ]);
    }

    $comment = [
        'id'=>$commentId,
        'post_id'=>$postId,
        'name' =>$user['name'],
        'avatar_url' =>$user['avatar_url'],
        'datetime'=>strtotime('now'),
        'text'=>$text
    ];

    Flight::render('comment', ['comment'=>$comment]);
});

// TODO - zde
// /act/post/edit/@id
Flight::route('/act/comment/edit/@id', function($id){
    Flight::requireLogin();
    $rows = Flight::rows();
    $user = Flight::user();
    $request = Flight::request();

    $post = $rows->one('posts', $id);

    if (!$post) Flight::notFound();
    if (!can_edit_post($post)) Flight::forbidden();
    $blog = $rows->one('blogs', $post['blog_id']);

    $form = get_post_form($post['type']);
    if ($post['type']==4 && !empty($post['url']) && !empty($post['source'])) {
        $post['from'] = $post['source'];
        $post['source'] = '';
    }
    $form->fill($post);

    if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
        $newPost = $form->values;
        unset($newPost['post'], $newPost['upload'], $newPost['csrf_token']);

        $newPost = finish_post($newPost, $form, false);

        if ($form->isValid) {
            $rows->update('posts', $newPost, $id);
            $rows->delete('post_tags', ['post_id'=>$id]);
            if (!empty($newPost['tags'])) {
                foreach (explode(' ', $newPost['tags']) as $tag) {
                    $rows->insert('post_tags', ['blog_id'=>$post['blog_id'], 'post_id'=>$id, 'tag'=>$tag]);
                }
            }
            Flight::redirect('/'.$blog['name'].'/post/'.$id);
        }
    }

    Flight::render('header', ['title' => 'new post' ]);
    Flight::render('form', [
        'form' => $form,
    ]);
    Flight::render('footer', []);
});

// /act/post/delete/@id
Flight::route('/act/comment/delete/@id', function($id){
    Flight::requireLogin();
    /** @var rows $rows */
    $rows = Flight::rows();
    $user = Flight::user();
    $request = Flight::request();

    if ($request->method!='POST') Flight::forbidden();

    $comment = $rows->one('comments', $id);

    if (!$comment) Flight::notFound();
    if (!can_delete_comment($comment)) Flight::forbidden();

    // TODO - zde mažeme
    $rows->update('comments', ['is_visible'=>0], $id);

    echo 'OK';
});

function can_edit_comment($comment)
{
    $user = Flight::user();
    if (!$user) return false;
    if ($user['id']==1) return true; // admin can edit everything
    return $comment['author_id']==$user['blog_id'];
}

function can_delete_comment($comment)
{
    $user = Flight::user();
    if (!$user) return false;
    if ($user['id']==1) return true; // admin can edit everything
    return $comment['author_id']==$user['blog_id'];
}

// notifications
Flight::route('/act/notifications', function(){
    Flight::requireLogin();
    $user = Flight::user();
    /** @var severak\database\rows $rows */
    $rows = Flight::rows();

    $notifications = $rows->more('notifications', ['id_to'=>$user['id']], ['datetime'=>'desc'], 50);
    $rows->update('notifications', ['is_read'=>1], ['id_to'=>$user['id']]);

    Flight::render('header', ['title' => 'notifications', 'noMessages'=>true]);
    Flight::render('notifications', ['notifications'=>$notifications]);
    Flight::render('footer', []);
});
