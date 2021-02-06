<?php
$date = $prevDate = '';
echo '<div class="content">';
echo '<ul>';
foreach ($posts as $post) {
    if (empty($post['title'])) continue;
    $date = date('M Y', $post['datetime']);
    if ($date != $prevDate) {
        echo '</ul><h2>'.$date.'</h2><ul>';
    }
    echo '<li><a href="/' . $post['slug_name'] . '/post/' . $post['id'] .'">'.$post['title'].'</a></li>';

    $prevDate = $date;
}
echo '</ul>';
echo '</div>';