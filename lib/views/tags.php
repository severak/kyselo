<?php
// tags listing:
// - tags
// - blog

if (empty($tags)) {
    echo '<img src="/st/img/undraw_a_moment_to_relax_bbpa.png" alt="" class="kyselo-the-end"><p>There are no tags yet...</p>';
}

$maxNum = reset($tags);

foreach ($tags as $tag=>$num) {
    $size = (($num / $maxNum) * 100) + 10;

    echo '<a href="/'.$blog['name'].'?tag='.$tag.'" style="font-size: '.$size.'px; text-decoration: none;">#'.$tag.'&nbsp;'. $num. '×</a> ';
}

echo '<hr>';

$icons = ['', 'book', 'link', 'paragraph', 'camera', 'youtube-play', 'file', 'star', 'calendar'];
$type2code = ['text'=>1, 'link'=>2, 'quote'=>3, 'image'=>4, 'video'=>5, 'file'=>6, 'review'=>7, 'event'=>8];

foreach ($type2code as $type=>$code) {
    if (isset($types[$code])) {
        echo '<a href="/'.$blog['name'].'?type='.$type.'" class="button"><i class="fa fa-'.$icons[$code].'"></i>&nbsp;'.$types[$code].' '.$type.'s</a> ';
    }
}
