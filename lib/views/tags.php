<?php
// tags listing:
// - tags
// - blog

$maxNum = reset($tags);

foreach ($tags as $tag=>$num) {
    $size = (($num / $maxNum) * 100) + 10;

    echo '<a href="/'.$blog['name'].'?tags='.$tag.'" style="font-size: '.$size.'px; text-decoration: none;">#'.$tag.'</a> ';
}
