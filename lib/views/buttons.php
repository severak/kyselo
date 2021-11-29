<?php
// floating buttons:
echo '<div class="kyselo-float buttons">';
if (empty($user)) {
    $loginAs = '';
    if (!empty($blog)  && $blog['name']!='all' && !$blog['is_group']) {
        $loginAs = '?as=' . $blog['name'];
    }
    // login
    echo '<a href="/act/login'.$loginAs.'" class="button is-medium is-dark" title="login"><i class="fa fa-key"></i><span class="kyselo-hidden"> login</span></a>';
} else {
    // my blog:
    if (!empty($blog) && ($blog['name']==$user['name'] || $blog['name']=='all') ) {
        // post
        echo '<a href="#post_types" id="new_post" class="button is-medium is-dark is-dark"><i class="fa fa-pencil" title="post something"></i><span class="kyselo-hidden"> new post</span></a>';
        echo '<span id="post_types">';
        echo '<a href="/act/post?as='.$user['name'].'&type=1" class="button is-medium is-dark" title="post text"><i class="fa fa-book"></i><span class="kyselo-hidden"> new post</span></a>';
        echo '<a href="/act/post?as='.$user['name'].'&type=2" class="button is-medium is-dark" title="post link"><i class="fa fa-link"></i><span class="kyselo-hidden"> new post</span></a>';
        echo '<a href="/act/post?as='.$user['name'].'&type=3" class="button is-medium is-dark" title="post quote"><i class="fa fa-paragraph"></i><span class="kyselo-hidden"> new post</span></a>';
        echo '<a href="/act/post?as='.$user['name'].'&type=4" class="button is-medium is-dark" title="post image"><i class="fa fa-camera"></i><span class="kyselo-hidden"> new post</span></a>';
        echo '<a href="/act/post?as='.$user['name'].'&type=5" class="button is-medium is-dark" title="post video"><i class="fa fa-youtube-play"></i><span class="kyselo-hidden"> new post</span></a>';
        /*
        echo '<a href="/act/post?as='.$user['name'].'&type=6" class="button is-medium is-dark"><i class="fa fa-file"></i><span class="kyselo-hidden"> new post</span></a>';
        echo '<a href="/act/post?as='.$user['name'].'&type=7" class="button is-medium is-dark"><i class="fa fa-star"></i><span class="kyselo-hidden"> new post</span></a>';
        echo '<a href="/act/post?as='.$user['name'].'&type=8" class="button is-medium is-dark"><i class="fa fa-calendar"></i><span class="kyselo-hidden"> new post</span></a>';
        */
        echo '</span>';
    }
    // group:
    if (!empty($blog) && $blog['name']!='all' && $blog['name']!=$user['name'] && $blog['is_group']) {
        $friendshipExists = Flight::rows()->count('friendships', ['from_blog_id'=> $user['blog_id'], 'to_blog_id'=> $blog['id']]) > 0;
        // follow
        echo '<a href="/act/follow?who='.$blog['name'].'" class="button is-medium is-dark kyselo-switch '.($friendshipExists ? 'on' : 'off').'" title="'.($friendshipExists ? 'un' : '').'follow this group"><i class="fa fa-heart"></i><span class="kyselo-hidden"> follow</span></a>';

        $membership = Flight::rows()->one('memberships', ['member_id'=> $user['blog_id'], 'blog_id'=> $blog['id']]);
        // member
        echo '<a href="/act/member?who='.$blog['name'].'" class="button is-medium is-dark kyselo-switch '.($membership ? 'on' : 'off').' '.(!empty($friendship['is_admin']) ? 'is-admin' : '').'" title="'.($membership ? 'leave' : 'became member of').' this group"><i class="fa fa-user-plus"></i><span class="kyselo-hidden"> member</span></a>';

        if ($membership) {
            echo '<a href="#post_types" id="new_post" class="button is-medium is-dark" title="post something"><i class="fa fa-pencil"></i><span class="kyselo-hidden"> new post</span></a>';
            echo '<span id="post_types">';
            echo '<a href="/act/post?as='.$blog['name'].'&type=1" class="button is-medium is-dark" title="post text"><i class="fa fa-book"></i><span class="kyselo-hidden"> new post</span></a>';
            echo '<a href="/act/post?as='.$blog['name'].'&type=2" class="button is-medium is-dark" title="post link"><i class="fa fa-link"></i><span class="kyselo-hidden"> new post</span></a>';
            echo '<a href="/act/post?as='.$blog['name'].'&type=3" class="button is-medium is-dark" title="post quote"><i class="fa fa-paragraph"></i><span class="kyselo-hidden"> new post</span></a>';
            echo '<a href="/act/post?as='.$blog['name'].'&type=4" class="button is-medium is-dark" title="post image"><i class="fa fa-camera"></i><span class="kyselo-hidden"> new post</span></a>';
            echo '<a href="/act/post?as='.$blog['name'].'&type=5" class="button is-medium is-dark" title="post video"><i class="fa fa-youtube-play"></i><span class="kyselo-hidden"> new post</span></a>';
            echo '</span>';
        }

    }
    // other blog:
    if (!empty($blog) && $blog['name']!='all' && $blog['name']!=$user['name'] && !$blog['is_group']) {
        $friendshipExists = Flight::rows()->count('friendships', ['from_blog_id'=> $user['blog_id'], 'to_blog_id'=> $blog['id']]) > 0;
        // follow
        echo '<a href="/act/follow?who='.$blog['name'].'" class="button is-medium is-dark kyselo-switch '.($friendshipExists ? 'on' : 'off').'" title="'.($friendshipExists ? 'un' : '').'follow this blog"><i class="fa fa-heart"></i><span class="kyselo-hidden"> follow</span></a>';
        // message
        echo '<a href="/act/messages/with/'.$blog['name'].'" class="button is-medium is-dark" title=""><i class="fa fa-paper-plane"></i><span class="kyselo-hidden"> message</span></a>';
    }
}
echo '</div>';
