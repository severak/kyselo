<!doctype HTML>
<html class="has-navbar-fixed-top">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title><?php echo $blog['name']; ?>'s backup</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="style.css">
<body>

<div class="kyselo-container">
<div class="kyselo-post ub-cols">
    <div><img src=".<?=$blog['avatar_url']; ?>" class="image is-128x128"></div>
    <div class="kyselo-about">
        <h1><?=$blog['title']; ?></h1>
        <?=$blog['about']; ?>
    </div>
</div>


<?php foreach ($posts as $post) {
    $nsfwClass = $post['is_nsfw'] ? 'is-nsfw' : '';

    echo '<div class="kyselo-post ub-cols '.$nsfwClass.'">';
    echo '<div><a href="'.$site_url.'/'.$post['name'].'"><img src=".'.$post['avatar_url'].'" class="image is-64x64"><br>' .  $post['name'].'</a>';
    if (!empty($post['group_name'])) {
        echo ' in<br><a href="'.$site_url.'/'.$post['group_name'].'"><img src=".'.$post['group_avatar_url'].'" class="image is-64x64"><br>' .  $post['group_name'] . '</a>';
    }
    echo '</div><div class="kyselo-post-body">';

    echo '<a>'.date('Y-m-d H:i', $post['datetime']).' <a href="'.$site_url.'/'.$post['slug_name'].'/post/'.$post['id'].'">#'.$post['id'].'</a></p>';
    if (!empty($post['reposted_from'])) {
        echo '<p>reposted from  <a href="'.$site_url.'/'.$post['reposted_from']['name'].'/post/'.$post['repost_of'].'">' . $post['reposted_from']['name'] . '</a></p>';
    }

    if ($post['type']==1) { // text
        if (!empty($post['title'])) {
            echo '<h2>' . $post['title'] . '</h2>';
        }
        echo $post['body'];
    } else if ($post['type']==2) { // link
        echo '<a href="' . $post['source'] . '">' . $post['title'] . '</a>';
        if (!empty($post['body'])) {
            echo '<p>' . $post['body'] . '</p>';
        }
    } else if ($post['type']==3) { // quote
        echo '<blockquote>'. $post['body'] .'<br/> &mdash; ' . $post['title'] . '</blockquote>';
        if (!empty($post['source'])) {
            echo '<p>from <a href="' . $post['source'] . '">' . $post['source'] . '</a></p>';
        }
    } else if ($post['type']==4) { // image
        $imgClass = 'kyselo-image';
        $size = @getimagesize(__DIR__ . $post['url']);
        if (!empty($size[0]) && (($size[0]*3) < $size[1])) {
            $imgClass = 'kyselo-image-long';
        }
        if (!empty($size[0]) && ($size[0]==$size[1])) {
            $imgClass = 'kyselo-image-square';
        }

        echo '<img class="image '.$imgClass.'" src=".' . $post['url'] . '"/>';
        if (!empty($post['body'])) {
            echo '<p>' . $post['body'] . '</p>';
        }
        if (!empty($post['source'])) {
            echo '<p>from <a href="' . $post['source'] . '">' . $post['source'] . '</a></p>';
        }
    } else if ($post['type']==5) { // video
        if (!empty($post['title'])) {
            echo '<h2>' . $post['title'] . '</h2>';
        }
        // not displaying video iframe in archive
        if (!empty($post['source'])) {
            echo '<p>from <a href="' . $post['source'] . '">' . $post['source'] . '</a></p>';
        }
    } else if ($post['type']==6) { // file
        echo '<a href="' . $post['url'] . '">' . $post['title'] . '</a> <small>' . $post['file_info'] . '</small>';
        if (!empty($post['body'])) {
            echo '<p>' . $post['body'] . '</p>';
        }
    } else if ($post['type']==7) { // rating
        echo '<h2>' . $post['title'] . '</h2>';
        echo 'rating: ' . str_repeat('❋', $post['rating']);
        if (!empty($post['body'])) {
            echo '<p>' . $post['body'] . '</p>';
        }
        if (!empty($post['source'])) {
            echo '<p>from <a href="' . $post['source'] . '">' . $post['source'] . '</a></p>';
        }
    } else if ($post['type']==8) { // event
        if (!empty($post['title'])) {
            echo '<h2>' . $post['title'] . '</h2>';
        }
        if (!empty($post['body'])) {
            echo '<p>' . $post['body'] . '</p>';
        }
        if (!empty($post['url'])) {
            echo '<img src="' . $post['url'] . '" class="image">';
        }
        echo '<p>from: ' . $post['start_date'] . '</p>';
        echo '<p>to: ' . $post['end_date'] . '</p>';
        echo '<p>where: ' . $post['location'] . '</p>';
    }


    echo '<div class="kyselo-tags">';
    if (!empty($post['tags'])) {
        foreach (explode(' ', $post['tags']) as $tag) {
            echo '<a href="'.$site_url.'/'.$post['slug_name'].'?tag='.$tag.'">#'.$tag.'</a> ';
        }
    }
    echo '</div>';

    $repostedTo = [];
    if (!empty($post['reposted_by'])) {
        echo '<p>reposted by ';
        foreach ($post['reposted_by'] as $repost) {
            $repostedTo[$repost['name']] = true;
            echo '<a href="'.$site_url.'/'.$repost['name'].'/post/'.$repost['repost_id'].'">' . $repost['name'] . '</a> ';
        }
        echo '</p>';
    }

    echo '</div>';

    echo '</div>' . PHP_EOL;
}

if (!empty($next_page)) {
    echo '<a href="'.$next_page.'" class="kyselo-next-page">next page</a>';
}


?>



</div>



</body>
</html>
