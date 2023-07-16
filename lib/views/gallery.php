<?php
$date = $prevDate = '';
echo '<div class="content">';

if (empty($posts)) {
    echo '<img src="/st/img/undraw_a_moment_to_relax_bbpa.png" alt="" class="kyselo-the-end"><p>There are no news yet...</p>';
}

echo '<div class="kyselo-gallery">';
foreach ($posts as $post) {
    $nsfwClass = $post['is_nsfw'] ? 'is-nsfw' : '';
    $date = date('M Y', $post['datetime']);
    if ($date != $prevDate) {
        echo '<h2>'.$date.'</h2>';
    }
    echo '<a href="/' . $post['slug_name'] . '/post/' . $post['id'] .'">';
    echo '<img src="' . kyselo_small_image($post['url'], 320, true) . '" class="'.$nsfwClass.'">';
    echo '</a>';
    $prevDate = $date;
}


echo '</div>';
echo '</div>';

if (!empty($more_link)) {
    echo '<div class="media"><div style="width: 64px">&nbsp;</div><div>';
    echo '<a href="'.str_replace('?', '/gallery?', $more_link).'" class="button is-medium">▼ see more... ▼</a>';
    echo '</div></div>';
}

if (!empty($the_end)) {
    echo '<div class="media kyselo-the-end"><div style="width: 64px">&nbsp;</div><div>';
    echo '<div>';
    echo '<p>You have reached <a href="https://www.youtube.com/watch?v=ZeMlQEWEg2Q" target="_blank">the end</a>...</p>';
    echo '<p><img src="/st/img/the-end.png" alt="THE END"></p>';
    echo '</div>';
    echo '</div></div>';
}
