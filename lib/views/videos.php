<?php
$videos = [];
foreach ($posts as $post) {
    $match = [];
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $post['url'], $match);
    if ($match[1]) {
        $post['id'] = $match[1];
        $videos[] = $post;
    }
}
?>
<p>now playing:<br><strong id="songName">?</strong><br>recommended by <a id="djName" href="#">?</a></p>
<div id="player"></div>
<hr>
<script>
    var playlist = <?=json_encode($videos); ?>;
    var details = {};
    var idList = [];
    playlist.forEach(function(vid){
        details[vid.id] = vid;
        idList.push(vid.id);
    });


    // 2. This code loads the IFrame Player API code asynchronously.
    var tag = document.createElement('script');
    tag.src = "https://www.youtube.com/iframe_api";
    var firstScriptTag = document.getElementsByTagName('script')[0];
    firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    // 3. This function creates an <iframe> (and YouTube player)
    //    after the API code downloads.
    var player;
    function onYouTubeIframeAPIReady() {
        player = new YT.Player('player', {
            height: '360',
            width: '640',
            videoId: idList.pop(),
            events: {
                'onReady': onPlayerReady,
                'onStateChange': onPlayerStateChange
            }
        });
    }
    // 4. The API will call this function when the video player is ready.
    function onPlayerReady(event) {
        event.target.loadPlaylist({playlist: idList, name:'Videos'});
        event.target.playVideo();
    }
    function updateNowPlaying() {
        var vdata = player.getVideoData();
        document.getElementById('songName').textContent = vdata.title;
        var dj = details[vdata.video_id];
        document.getElementById('djName').textContent = dj.name;
        document.getElementById('djName').href = '/' + dj.name;
    }

    // 5. The API calls this function when the player's state changes.
    //    The function indicates that when playing a video (state=1),
    //    the player should play for six seconds and then stop.
    function onPlayerStateChange(event) {
        //if (event.data == 0 || event.data == 5) {
        updateNowPlaying();
        //}
    }
</script>
