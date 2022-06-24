<?php
// /act/groups
use severak\database\rows;

Flight::route('/act/groups', function(){
	$request = Flight::request();
	$rows = Flight::rows();
    $user = Flight::user();

    $existing = $rows->execute($rows->fragment('SELECT *
    FROM blogs b
    INNER JOIN (SELECT blog_id, COUNT(*) AS member_count FROM memberships GROUP BY blog_id) AS m ON m.blog_id=b.id
    WHERE b.is_group=1 AND b.is_visible=1
    ORDER BY b.name ASC'))->fetchAll(PDO::FETCH_ASSOC);

    $form = new severak\forms\form(['method'=>'post']);
    $form->field('name', ['label'=>'Group name  / URL', 'required'=>true]);
    $form->field('title', ['label'=>'Group title', 'required'=>true]);
    $form->field('about', ['label'=>'Blog description', 'class'=>'kyselo-editor', 'type'=>'textarea', 'rows'=>6, 'required'=>true]);
    $form->field('upload', ['label'=>'Change logo', 'type'=>'file']);
	kyselo_csrf($form);
    $form->field('save', ['label'=>'Create group', 'type'=>'submit']);

    $form->rule('name', function($name) {
		return preg_match('~^[a-z]([a-z0-9]{2,})$~', $name)===1;
    }, 'Bad group name format: 3 or more lower case letters and numbers allowed, must start with letter.');

    $form->rule('name', function($name) {
		$rows = Flight::rows();
		return empty($rows->one('blogs', ['name'=>$name]));
	}, 'Name already in use. Choose another.');

    if ($request->method=='POST' && $form->fill($_POST) && $form->validate()) {
        Flight::requireLogin();

		$update = $form->values;
        unset($update['upload'], $update['save'], $update['csrf_token']);

        $newPhoto = kyselo_upload_image($form, 'upload');
        $update['avatar_url'] = '/st/img/undraw_Appreciation_re_p6rl.png';
        if ($newPhoto) $update['avatar_url'] = $newPhoto;

        $update['since'] = date('Y-m-d H:i:s');
        $update['is_group'] = 1;
        $update['user_id'] = 0;

        $newGroupId = $rows->insert('blogs', $update);

        $rows->insert('memberships', ['blog_id'=>$newGroupId, 'member_id'=>$user['blog_id'], 'is_admin'=>1, 'is_founder'=>1, 'since'=>date('Y-m-d H:i:s')]);
        $rows->insert('friendships', ['to_blog_id'=>$newGroupId, 'from_blog_id'=>$user['blog_id'], 'is_bilateral'=>0, 'since'=>date('Y-m-d H:i:s')]);

        $rows->insert('messages', [
            'id_from'=>1,
            'id_to'=>2,
            'text'=>sprintf('SYSTEM: Group %s was created!', $form->values['name']),
            'datetime'=>strtotime('now'),
            'is_read'=>0
        ]);

        $_SESSION['user']['groups'][$newGroupId] = [
            'id'=>$newGroupId,
            'name'=>$update['name'],
            'title'=>$update['title'],
            'avatar_url'=>$update['avatar_url']
        ];

        Flight::redirect('/'. $update['name']);
    }

    Flight::render('header', ['title' => 'groups' ]);
    Flight::render('groups', ['groups' => $existing ]);
    if ($user) {
        Flight::render('form', ['form' => $form, 'h2' => 'Create group']);
    }
    Flight::render('footer', []);
});

Flight::route('/all/members', function (){
    /** @var rows $rows */
    $rows = Flight::rows();

    if (isset($_GET['sortBy']) && $_GET['sortBy']=='lastSeen') {
        $members = $rows->execute($rows->query('SELECT blogs.*, lsu.maxdt as last_seen
FROM (
SELECT blog_id, MAX(datetime) as maxdt
FROM posts
GROUP BY blog_id
) as lsu
inner join blogs on lsu.blog_id=blogs.id
where blogs.is_group=0
order by maxdt desc'))->fetchAll();
    } else {
        $members = $rows->more('blogs', ['is_visible'=>1, 'is_group'=>0], ['name'=>'asc'], 300);
    }

    Flight::render('header', ['title' => sprintf('all from %s', Flight::config('site_name'))]);
    Flight::render('blog_header', [
        'blog'=>['name'=>'all', 'title'=>sprintf('all from %s', Flight::config('site_name')), 'is_group'=>true, 'id'=>-1, 'about'=>'(member list)', 'avatar_url'=>'/st/img/undraw_different_love_a3rg.png'],
        'user'=>Flight::user(),
        'tab'=>'members'
    ]);
    Flight::render('members', [
        'members'=>$members
    ]);
    Flight::render('footer', []);
});

Flight::route('/@name/members', function($name){
    /** @var rows $rows */
    $rows = Flight::rows();

    $blog = $rows->one('blogs', ['name'=>$name, 'is_visible'=>1]);

    if (empty($blog)) {
        Flight::notFound();
    }

    if (!$blog['is_group']) {
        Flight::forbidden();
    }

    $members = $rows
        ->with('memberships', 'id', 'member_id', ['blog_id'=>$blog['id']])
        ->more('blogs');

    Flight::render('header', ['title' => $blog["title"] ]);
    Flight::render('blog_header', [
        'blog'=>$blog,
        'user'=>Flight::user(),
        'tab'=>'members'
    ]);
    Flight::render('members', [
        'members'=>$members
    ]);
    Flight::render('footer', []);

});
