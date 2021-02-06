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
