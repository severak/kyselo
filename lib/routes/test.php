<?php
// for testing new features
Flight::route('/act/test', function(){
    $timeline = new kyselo\timeline(Flight::rows());

    $posts = $timeline->lastPostBy();
    $moreLink = $timeline->moreLink;
    $theEnd = !$timeline->moreLink;

    Flight::render('header', ['title' => sprintf('last posts from %s', Flight::config('site_name')) /*, 'rss'=>'/all/rss'. $timeline->currentParams */ ]);
    Flight::render('blog_header', [
        'blog'=>[
            'name'=>'all',
            'title'=>sprintf('last posts from %s', Flight::config('site_name')),
            'is_group'=>true,
            'id'=>-1,
            'about'=>'(but no reposts)',
            'avatar_url'=>'/st/img/undraw_different_love_a3rg.png'
        ],
        'user'=>Flight::user(),
        'tab'=>'blog',
        // 'rsslink'=>kyselo_url('/all/rss', [], $_GET)
    ]);
    Flight::render('posts', [
        'posts'=>$posts,
        'more_link'=>$moreLink,
        'the_end'=>$theEnd,
        'user' => Flight::user()
    ]);
    Flight::render('buttons', [
        'blog'=>['name'=>'all', 'is_group'=>false],
        'user'=>Flight::user()
    ]);
    Flight::render('footer', []);
});

