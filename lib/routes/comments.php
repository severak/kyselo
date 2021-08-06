<?php
// messages with someone
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
    $post = $rows->one('posts', ['id'=>$postId, 'is_visible'=>1]);
    if (empty($newPost)) {
        Flight::notFound();
    }

    $id = $rows->insert('comments', [
        'post_id' => $postId,
        'author_id' => $user['blog_id'],
        'datetime' => strtotime('now'),
        'text' => $text,
        'is_visible' => 1
    ]);

    $rows->execute($rows->fragment('UPDATE posts SET comments_count=comments_count+1 WHERE id=?', [$postId]));

    $comment = [
        'id'=>$id,
        'post_id'=>$postId,
        'name' =>$user['name'],
        'avatar_url' =>$user['avatar_url'],
        'datetime'=>strtotime('now'),
        'text'=>$text
    ];

    // TODO - notifikace autorovi postu
    // TODO - notifikace zmíněným uživatelům

    Flight::render('comment', ['comment'=>$comment]);
});
