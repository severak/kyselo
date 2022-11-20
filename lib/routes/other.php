<?php
// /act/follow (follow/unfollow)
Flight::route('/act/follow', function(){
	Flight::requireLogin();
	$rows = Flight::rows();
	$user = Flight::user();

	$blog = $rows->one('blogs', ['name'=>$_GET['who'], 'is_visible'=>1]);

	if (empty($blog) || empty($_GET['who'])) {
		Flight::notFound();
	}

	$friendshipExists = $rows->count('friendships', ['from_blog_id'=>$user['blog_id'], 'to_blog_id'=>$blog['id']]) > 0;

	if ($friendshipExists) {
		// unfollow
        $rows->delete('friendships', ['from_blog_id'=> $user['blog_id'], 'to_blog_id'=>$blog['id']]);
        $rows->update('friendships', ['is_bilateral'=>0], ['from_blog_id'=> $blog['id'], 'to_blog_id'=>$user['blog_id']]);
	} else {
		// follow
		$isBilateral = $rows->count('friendships', ['from_blog_id'=> $blog['id'], 'to_blog_id'=> $user['blog_id']]);

        $rows->insert('friendships', ['from_blog_id'=> $user['blog_id'], 'to_blog_id'=>$blog['id'], 'since'=>date('Y-m-d H:i:s'), 'is_bilateral'=>$isBilateral]);

        if ($isBilateral) {
            $rows->update('friendships', ['is_bilateral'=>1], ['from_blog_id'=> $blog['id'], 'to_blog_id'=>$user['blog_id']]);
        }

		// notification
        if (!$blog['is_group']) {
            $rows->insert('notifications', [
                'id_to' => $blog['id'],
                'text' => sprintf('<i class="fa fa-heart"></i> <a href="/%s">%s</a> followed you' . ($isBilateral ? ' back' : ''), $user['name'], $user['name']),
                'datetime'=> strtotime('now')
            ]);
        }
	}

	Flight::redirect('/'.$blog['name']);
});

// /act/member (member/dismember)
Flight::route('/act/member', function(){
	Flight::requireLogin();
	$rows = Flight::rows();
	$user = Flight::user();

	$blog = $rows->one('blogs', ['name'=>$_GET['who'], 'is_visible'=>1]);

	if (!$blog['is_group']) {
		Flight::forbidden();
	}

	if (empty($blog) || empty($_GET['who'])) {
		Flight::notFound();
	}

	$membershipExists = $rows->count('memberships', ['member_id'=>$user['blog_id'], 'blog_id'=>$blog['id']]) > 0;

	kyselo_start_session();
	if ($membershipExists) {
		unset($_SESSION['user']['groups'][$blog['id']]);
		$rows->delete('memberships', ['member_id'=>$user['blog_id'], 'blog_id'=>$blog['id']]);
	} else {
        // notify members of the group about new member
        $members = $rows->more('memberships', ['blog_id'=>$blog['id']], [], 99);
        foreach ($members as $member) {
            $rows->insert('notifications', [
                'id_to' => $member['member_id'],
                'text' => sprintf('<i class="fa fa-umbrella"></i> <a href="/%s">%s</a> joined your group <a href="/%s">%s</a>', $user['name'], $user['name'], $blog['name'], $blog['name']),
                'datetime'=> strtotime('now')
            ]);
        }

        $rows->insert('memberships', [
            'member_id'=>$user['blog_id'],
            'blog_id'=>$blog['id'],
            'since'=>date('Y-m-d H:i:s')
        ]);

		$_SESSION['user']['groups'][$blog['id']] = [
            'id'=>$blog['id'],
            'name'=>$blog['name'],
            'title'=>$blog['title'],
            'avatar_url'=>$blog['avatar_url']
        ];
	}

	Flight::redirect('/'.$blog['name']);
});

// /act/settings/@blog
Flight::route('/act/settings/@name', function($name){
	Flight::requireLogin();
	$request = Flight::request();
	$rows = Flight::rows();
	$user = Flight::user();

	$blog = $rows->one('blogs', ['name'=>$name]);

	if (empty($blog)) {
		Flight::notFound();
	}

	if ($user['blog_id']!=$blog['id'] && !isset($user['groups'][$blog['id']])) {
		Flight::forbidden();
	}

	$form = new severak\forms\form(['method'=>'post']);
	$form->field('title', ['label'=>'Blog title', 'required'=>true]);
	$form->field('about', ['label'=>'Blog description', 'class'=>'kyselo-editor', 'type'=>'textarea', 'rows'=>6, 'required'=>true]);
	$form->field('is_nsfw', ['label'=>'is NSFW blog', 'type'=>'checkbox']);
	$form->field('upload', ['label'=>'Change logo', 'type'=>'file']);
    $form->field('has_journal', ['label'=>'journal view enabled', 'type'=>'checkbox']);
    $form->field('has_videos', ['label'=>'videos playlist enabled', 'type'=>'checkbox']);
    $form->field('exclude_from_robots', ['label'=>'exclude from search engines', 'type'=>'checkbox']);
    kyselo_csrf($form);
	$form->field('save', ['label'=>'Save blog settings', 'type'=>'submit']);

	$form->fill($blog);

	$form->rule('about', function ($html) {return !detect_xss($html);}, 'Please don\'t hack us!');

	if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
		$update = $form->values;
		unset($update['upload'], $update['save'], $update['csrf_token']);

		$newPhoto = kyselo_upload_image($form, 'upload');
		if ($newPhoto) $update['avatar_url'] = $newPhoto;

		if ($form->isValid) {
		    $rows->update('blogs', $update, $blog['id']);
			Flight::redirect('/'.$blog['name']);
		}
	}

	Flight::render('header', ['title' => $blog["title"] . ' - settings' ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'settings',
        'settings_subtab' => 'settings'
	]);
	Flight::render('form', ['form'=>$form]);
	Flight::render('bookmarklet', ['blog'=>$blog]);
	Flight::render('footer', []);
});

// /act/settings/@blog
Flight::route('/act/custom-css/@name', function($name){
    Flight::requireLogin();
    $request = Flight::request();
    $rows = Flight::rows();
    $user = Flight::user();

    $blog = $rows->one('blogs', ['name'=>$name]);

    if (empty($blog)) {
        Flight::notFound();
    }

    if ($user['blog_id']!=$blog['id'] && !isset($user['groups'][$blog['id']])) {
        Flight::forbidden();
    }

    $form = new severak\forms\form(['method'=>'post']);
    $form->field('custom_css', ['label'=>'custom CSS', 'type'=>'textarea', 'rows'=>5, 'placeholder'=>'/* write custom CSS here */', 'id'=>'custom_css']);
    $form->field('save', ['label'=>'Save custom CSS', 'type'=>'submit']);

    $form->fill($blog);

    $form->rule('custom_css', function ($html) {return !detect_xss($html);}, 'Please don\'t hack us!');

    if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
        $update = $form->values;
        unset($update['save']);


        if ($form->isValid) {
            $rows->update('blogs', $update, $blog['id']);
            Flight::redirect('/'.$blog['name']);
        }
    }

    Flight::render('header', ['title' => $blog["title"] . ' - custom CSS' ]);
    Flight::render('blog_header', [
        'blog'=>$blog,
        'user'=>Flight::user(),
        'tab'=>'settings',
        'settings_subtab' => 'custom_css'
    ]);
    Flight::render('custom_css_help');
    Flight::render('form', ['form'=>$form]);
    Flight::render('footer', []);
});

Flight::route('/act/iframe/@id', function($id) {
	$rows = Flight::rows();
	$post = $rows->one('posts', ['id'=>$id, 'is_visible'=>true]);

	if (!$post) {
		Flight::notFound();
	}
	echo $post['preview_html'];
});

Flight::route('/act/toggle_nsfw', function(){
    kyselo_start_session();
    $_SESSION['show_nsfw'] = empty($_SESSION['show_nsfw']) ? 1 : 0;
	Flight::json(['show_nsfw'=>$_SESSION['show_nsfw']]);
});

Flight::route('/act/chat', function () {

    if (!Flight::config('chat_websocket_url')) {
        Flight::forbidden();
    }
    Flight::requireLogin();

    Flight::render('header', ['title'=>Flight::config('site_name') . ' - chat']);
    Flight::render('chat', ['user'=>Flight::user()]);
    Flight::render('footer', []);
});

Flight::route('/act/stats', function() {
    $user = Flight::user();
    if (!($user && $user['id']==1)) Flight::forbidden();

    $rows = Flight::rows();
    Flight::render('header', ['title' => 'system stats' ]);

    $firstPost = $rows->one('posts', ['is_visible'=>1], ['datetime'=>'asc']);
    $now = new DateTime('now');
    $firstDate = new DateTime('@'.$firstPost['datetime']);
    $age  = $now->diff($firstDate);

    $noUsers = $rows->execute($rows->query('select count(*) from blogs where is_visible=1 and is_group=0;'))->fetch(PDO::FETCH_COLUMN);
    $noGroups = $rows->execute($rows->query('select count(*) from blogs where is_visible=1 and is_group=1;'))->fetch(PDO::FETCH_COLUMN);

    $topUsers = $rows->execute($rows->query('SELECT name, posts_count 
    FROM blogs b
    INNER JOIN (SELECT blog_id, COUNT(*) AS posts_count FROM posts GROUP BY blog_id) AS m ON m.blog_id=b.id
    WHERE b.is_group=0 AND b.is_visible
    ORDER BY posts_count DESC;'))->fetchAll(PDO::FETCH_ASSOC);

    $topGroups = $rows->execute($rows->query('SELECT name, posts_count 
    FROM blogs b
    INNER JOIN (SELECT blog_id, COUNT(*) AS posts_count FROM posts GROUP BY blog_id) AS m ON m.blog_id=b.id
    WHERE b.is_group=1 AND b.is_visible
    ORDER BY posts_count DESC;'))->fetchAll(PDO::FETCH_ASSOC);

    $avgPosts = $rows->execute($rows->query('
    select avg(cnt)
from (
select date(datetime,"unixepoch"), count(*) as cnt 
from posts 
where is_visible=1
group by date(datetime,"unixepoch")
)
    '))->fetch(PDO::FETCH_COLUMN);

    $avgPostsOriginals = $rows->execute($rows->query('
    select avg(cnt)
from (
select date(datetime,"unixepoch"), count(*) as cnt 
from posts 
where is_visible=1 and repost_of is null
group by date(datetime,"unixepoch")
)
    '))->fetch(PDO::FETCH_COLUMN);

    echo '<ul>';
    echo sprintf('<li>we are alive for %s</li>', $age->format('%a days (%y years %m months)'));
    echo sprintf('<li>we have %d users</li>', $noUsers);
    echo sprintf('<li>and %d groups</li>', $noGroups);
    if ($topUsers[0]) {
        echo sprintf('<li>most active user is <a href="/%s">@%s</a> with %d posts</li>', $topUsers[0]['name'], $topUsers[0]['name'], $topUsers[0]['posts_count']);
    }
    if ($topGroups[0]) {
        echo sprintf('<li>most active group is <a href="/%s">@%s</a> with %d posts</li>', $topGroups[0]['name'], $topGroups[0]['name'], $topGroups[0]['posts_count']);
    }
    echo sprintf('<li>there are about %d post per day</li>', $avgPosts);
    echo sprintf('<li>of these %d are originals (no reposts)</li>', $avgPostsOriginals);

    echo '</ul>';

    Flight::render('footer', []);
});

// TODO - this will be better served by some static pages
if (Flight::config('tos_post')) {
    Flight::route('/act/tos', function (){
        $rows = Flight::rows();
        $blog = $rows->one('blogs', ['name'=>'admin', 'is_visible'=>1]);

        $filter = new kyselo\timeline(Flight::rows());
        $filter->mode = 'one';
        $filter->blogId = $blog['id'];
        $filter->postId = Flight::config('tos_post');
        $filter->withComments = false;
        $posts = $filter->posts();

        Flight::render('header', ['title' => $posts[0]['title'], 'rss'=>sprintf('/%s/rss', $blog['name']), 'ogp_post'=>$posts[0] ]);
        Flight::render('posts', [
            'posts'=> $posts,
            'blog' => $blog,
            'user' => Flight::user(),
            'hideComments'=>true
        ]);
        Flight::render('footer', []);
    });
}


if (Flight::config('gdpr_post')) {
    Flight::route('/act/privacy-policy', function (){
        $rows = Flight::rows();
        $blog = $rows->one('blogs', ['name'=>'admin', 'is_visible'=>1]);

        $filter = new kyselo\timeline(Flight::rows());
        $filter->mode = 'one';
        $filter->blogId = $blog['id'];
        $filter->postId = Flight::config('gdpr_post');
        $filter->withComments = false;
        $posts = $filter->posts();

        Flight::render('header', ['title' => $posts[0]['title'], 'rss'=>sprintf('/%s/rss', $blog['name']), 'ogp_post'=>$posts[0] ]);
        Flight::render('posts', [
            'posts'=> $posts,
            'blog' => $blog,
            'user' => Flight::user(),
            'hideComments'=>true
        ]);
        Flight::render('footer', []);
    });
}

// CRONjob for notifying people to actually use Kyselo
Flight::route('/act/cron', function(){
    $rows = Flight::rows();

    header('Content-type: text/plain; charset=utf-8');

    if (!isset($_GET['key']) || $_GET['key']!=Flight::config('cron_key')) {
        die('Invalid cron key!');
    }

    $allBlogs = $rows->more('blogs', ['is_group'=>0], [], 999);

    foreach ($allBlogs as $blog) {

        $user = $rows->one('users', ['id'=>$blog['user_id']]);
        $unreadMessages = $rows->count('messages', ['id_to'=>$blog['id'], 'is_read'=>0]);
        $unreadNotifications = $rows->count('notifications', ['id_to'=>$blog['id'], 'is_read'=>0]);

        if ($unreadMessages > 0 || $unreadNotifications > 5) {
            // we want to notify user if there is a unread message or more than 4 unread notifications
            $msg = 'Hi ' . $blog['name'] . ',' . PHP_EOL;
            $msg .= 'you have ' ;
            if ($unreadMessages>0) {
                $msg .= $unreadMessages . ' unread messages ';
            }
            if ($unreadMessages>0 && $unreadNotifications>0) {
                $msg .= 'and ';
            }
            if ($unreadNotifications >0) {
                $msg .= $unreadNotifications . ' new notifications ';
            }
            $msg .= 'at ' . kyselo_url('') . PHP_EOL;
            $msg .= PHP_EOL . '';

            if ($unreadMessages) {
                $msg .= 'Please log-in to ' . Flight::config('site_name') . ' to read them. ' . PHP_EOL . PHP_EOL;
            } else {
                $msg .= 'Please log-in to ' . Flight::config('site_name') . ' to mute this notification. ' . PHP_EOL . PHP_EOL;
            }
            $msg .= 'If you forgot your password please visit ' . kyselo_url('/act/unlock') . ' to unlock your account. ';

            $msg .= PHP_EOL . PHP_EOL.  '';

            $msg .= 'See you soon on ' . Flight::config('site_name') . '!';

            $msg .= PHP_EOL . PHP_EOL  . 'Your friendly bots from ' . Flight::config('site_name');

            if ($unreadMessages > 0) {
                $subject = sprintf('%d new messages on %s' , $unreadMessages, Flight::config('site_name'));
            } else {
                $subject = sprintf('new notifications on %s', Flight::config('site_name'));
            }


            $email = kyselo_email();
            $email->addRecipient($user['email']);
            $email->setSubject(sprintf($subject));
            $email->setBody($msg, false);

            try {
                $email->send();
                echo $user['email'] . ' will got mail'. PHP_EOL .  $msg . PHP_EOL . '===' . PHP_EOL . PHP_EOL;
            } catch (Exception $exception) {
                echo $exception->getMessage();
            }
        }
    }
});

