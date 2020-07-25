<?php

use severak\database\rows;

Flight::route('/act/interop/userlist', function (){

    if (empty($_GET['key']) || $_GET['key']!=Flight::config('souper_endpoint_key')) {
        Flight::forbidden();
    }

    /** @var rows $rows */
    $rows = Flight::rows();
    $blogs = $rows->more('blogs', ['is_visible'=>1, 'is_group'=>0], [], 300);

    $userlist = [];

    foreach ($blogs as $blog) {
        $userlist[] = [
            'url' => 'https://kyselo.eu/' . $blog['name'],
            'name' => $blog['name'],
            'title' => $blog['title'],
            'avatar_url' => 'https://kyselo.eu' . $blog['avatar_url'],
        ];
    }

    Flight::json($userlist);
});