<?php
// bookmarklet
// arguments:
// - $blog

/*

Bookmarklet source code:

var text = "";
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != "Control") {
        text = document.selection.createRange().text;
    }
window.open('http://KYSELO_URL/act/post?as=BLOG&url='+encodeURIComponent(window.location.href)+'&quote='+encodeURIComponent(text));

Compiled via https://mrcoles.com/bookmarklet/

*/

$js = <<<JS
javascript:(function()%7Bvar%20text%20%3D%20%22%22%3Bif%20(window.getSelection)%20%7Btext%20%3D%20window.getSelection().toString()%3B%7D%20else%20if%20(document.selection%20%26%26%20document.selection.type%20!%3D%20%22Control%22)%20%7Btext%20%3D%20document.selection.createRange().text%3B%7Dwindow.open('http%3A%2F%2FKYSELO_URL%2Fact%2Fpost%3Fas%3DBLOG%26url%3D'%2BencodeURIComponent(window.location.href)%2B'%26quote%3D'%2BencodeURIComponent(text))%7D)()
JS;

$js = str_replace('KYSELO_URL', $_SERVER['HTTP_HOST'], $js);
$js = str_replace('BLOG', $blog['name'], $js);
$js = trim($js);

?>
<hr>
<a href="<?=$js; ?>" class="pure-button button-large">post to Kyselo as <?=$blog['name']; ?></a>