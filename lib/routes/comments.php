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

    if (!$mentioned && $post['author_id']!=$user['blog_id']) {
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

// /act/comment/edit/@id
Flight::route('/act/comment/edit/@id', function($id){
    Flight::requireLogin();
    $rows = Flight::rows();
    $user = Flight::user();
    $request = Flight::request();

    $comment = $rows->one('comments', $id);
    $post = $rows->one('posts', $comment['post_id']);

    if (!$comment || !$post) Flight::notFound();
    if (!can_edit_comment($comment)) Flight::forbidden();

    $form = new severak\forms\form(['method'=>'post', 'action'=>'/act/comment/edit/'.$id, 'class'=>'comment-edit']);
    $form->field('id', ['type'=>'hidden']);
    $form->field('text', ['type'=>'textarea', 'placeholder'=>'new comment text...', 'label'=>'Original comment text', 'class'=>'is-fullwidth']);
    $form->field('sbt', ['type'=>'submit', 'label'=>'Change comment', 'class'=>'is-info is-fullwidth', 'onclick'=>'return commentEdit('.$id.')']);

    $form->fill($comment);

    if ($request->method=='POST') {
        if (empty($_POST['text'])) Flight::forbidden();
        $rows->update('comments', ['text'=>$_POST['text']], $id);

        $editedComment = $rows->with('blogs', 'author_id')->one('comments', $id);
        Flight::render('comment', ['comment'=>$editedComment]);
    } else {
        Flight::render('form', ['form'=>$form]);
    }
});

// /act/comment/delete
Flight::route('/act/comment/delete', function(){
    Flight::requireLogin();
    /** @var rows $rows */
    $rows = Flight::rows();
    $user = Flight::user();
    $request = Flight::request();
    if ($request->method!='POST') Flight::forbidden();
    $id = $_POST['id'];

    $comment = $rows->one('comments', $id);
    $post = $rows->one('posts', $comment['id']);

    if (!$comment || !$post) Flight::notFound();
    if (!can_delete_comment($comment, $post)) Flight::forbidden();

    $rows->update('comments', ['is_visible'=>0], $id);
    $rows->execute($rows->fragment('UPDATE posts SET comments_count=comments_count-1 WHERE id=?', [$comment['post_id']]));
    echo 'OK';
});

function can_edit_comment($comment)
{
    $user = Flight::user();
    if (!$user) return false;
    if ($user['id']==1) return true; // admin can edit everything
    return $comment['author_id']==$user['blog_id'];
}

function can_delete_comment($comment, $post)
{
    $user = Flight::user();
    if (!$user) return false;
    if ($user['id']==1) return true; // admin can edit everything

    $canPostAs = [];
    $canPostAs[$user['blog_id']] = $user['name'];
    foreach ($_SESSION['user']['groups'] as $gId=>$group) {
        $canPostAs[$gId] = $group['name'];
    }

    return $comment['author_id']==$user['blog_id'] || isset($canPostAs[$post['blog_id']]);
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
