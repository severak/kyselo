<?php
// /act/follow (follow/unfollow)
Flight::route('/act/follow', function(){
	Flight::requireLogin();
	$db = Flight::db();
	$user = Flight::user();

	$blog = $db->from('blogs')->where('name', $_GET['who'])->where('is_visible', 1)->select()->one();

	if (empty($blog) || empty($_GET['who'])) {
		Flight::notFound();
	}

	$friendshipExists = $db->from('friendships')->where('from_blog_id', $user['blog_id'])->where('to_blog_id', $blog['id'])->count() > 0;

	if ($friendshipExists) {
		// unfollow
		$db->from('friendships')
			->where('from_blog_id', $user['blog_id'])
			->where('to_blog_id', $blog['id'])
			->delete()
			->execute();

		$db->from('friendships')
			->where('from_blog_id', $blog['id'])
			->where('to_blog_id', $user['blog_id'])
			->update(['is_bilateral'=>0])
			->execute();

	} else {
		// follow
		$isBilateral = $db->from('friendships')->where('from_blog_id', $blog['id'])->where('to_blog_id', $user['blog_id'])->count();

		$db->from('friendships')
			->insert([
				'from_blog_id'=>$user['blog_id'],
				'to_blog_id'=>$blog['id'],
				'since'=>date('Y-m-d H:i:s'),
				'is_bilateral'=>$isBilateral
			])
			->execute();

		if ($isBilateral) {
			$db->from('friendships')
			->where('from_blog_id', $blog['id'])
			->where('to_blog_id', $user['blog_id'])
			->update(['is_bilateral'=>1])
			->execute();
		}

		$rows = Flight::rows();
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
	$db = Flight::db();
	$user = Flight::user();

	$blog = $db->from('blogs')->where('name', $_GET['who'])->where('is_visible', 1)->select()->one();

	if (!$blog['is_group']) {
		Flight::forbidden();
	}

	if (empty($blog) || empty($_GET['who'])) {
		Flight::notFound();
	}

	$membershipExists = $db->from('memberships')->where('member_id', $user['blog_id'])->where('blog_id', $blog['id'])->count() > 0;

	if ($membershipExists) {
		unset($_SESSION['user']['groups'][$blog['id']]);
		$db->from('memberships')->where('member_id', $user['blog_id'])->where('blog_id', $blog['id'])->delete()->execute();
	} else {
        $rows = Flight::rows();
        // notificate members of the group about new member
        $members = $rows->more('memberships', ['blog_id'=>$blog['id']], [], 99);
        foreach ($members as $member) {
            $rows->insert('notifications', [
                'id_to' => $member['member_id'],
                'text' => sprintf('<i class="fa fa-umbrella"></i> <a href="/%s">%s</a> joined your group <a href="/%s">%s</a>', $user['name'], $user['name'], $blog['name'], $blog['name']),
                'datetime'=> strtotime('now')
            ]);
        }

		$db->from('memberships')->insert([
			'member_id'=>$user['blog_id'],
			'blog_id'=>$blog['id'],
			'since'=>date('Y-m-d H:i:s')
		])->execute();

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
	$db = Flight::db();
	$user = Flight::user();

	$blog = $db->from('blogs')->where('name', $name)->where('is_visible', 1)->select()->one();

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
    $form->field('custom_css', ['label'=>'custom CSS', 'type'=>'textarea', 'rows'=>5, 'placeholder'=>'/* write custom CSS here */', 'id'=>'custom_css']);
	kyselo_csrf($form);
	$form->field('save', ['label'=>'Save blog settings', 'type'=>'submit']);

	$form->fill($blog);

	$form->rule('about', function ($html) {return !detect_xss($html);}, 'Please don\'t hack us!');
	$form->rule('custom_css', function ($html) {return !detect_xss($html);}, 'Please don\'t hack us!');

	if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
		$update = $form->values;
		unset($update['upload'], $update['save'], $update['csrf_token']);

		$newPhoto = kyselo_upload_image($form, 'upload');
		if ($newPhoto) $update['avatar_url'] = $newPhoto;

		if ($form->isValid) {
			$db->from('blogs')->where('id', $blog['id'])->update($update)->execute();
			Flight::redirect('/'.$blog['name']);
		}
	}

	Flight::render('header', ['title' => $blog["title"] . ' - settings' ]);
	Flight::render('blog_header', [
		'blog'=>$blog,
		'user'=>Flight::user(),
		'tab'=>'settings'
	]);
	Flight::render('form', ['form'=>$form]);
	Flight::render('bookmarklet', ['blog'=>$blog]);
	Flight::render('custom_css_help');
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


