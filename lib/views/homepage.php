<div class="columns is-vcentered">
    <div class="column content is-vcentered">
        <p>Welcome to</p>
        <h1><?=Flight::config('site_name'); ?></h1>
        <p>an <a href="https://github.com/severak/kyselo">opensource</a> social network inspired by Soup.io.</p>
        <p>Made with love by <a href="//tilde.town/~severak/">Severák</a>.</p>
    </div>
</div>
<div class="columns is-vcentered">
    <div class="column">
        <img src="/st/img/undraw_different_love_a3rg.png" alt="" class="image">
    </div>
    <div class="column content">
        <h2>You can</h2>
        <div class="buttons">
        <a href="/all" class="button is-fullwidth"><i class="fa fa-globe"></i>&nbsp;look around</a>
        <a href="/act/random" class="button is-fullwidth"><i class="fa fa-random"></i>&nbsp;find random gems</a>
        <a href="/act/register" class="button is-fullwidth"><i class="fa fa-sign-in"></i>&nbsp;join us</a>
        <a href="/act/post?type=4" class="button  is-fullwidth is-primary"><i class="fa fa-photo"></i>&nbsp;post some cats</a>
        <a href="https://bitbucket.org/severak/kyselo" class="button  is-fullwidth"><i class="fa fa-git"></i>&nbsp;see our code</a>
        <a href="https://paypal.me/severakcz" class="button  is-fullwidth"><i class="fa fa-money"></i>&nbsp;donate some money</a>
        </div>
    </div>
</div>

<hr>

<div class="columns is-vcentered" id="features" style="padding-top: 3em">
    <div class="column">
        <a href="https://xkcd.com/2699/">
            <img src="st/img/feature_comparison.png" class="image">
        </a>
    </div>
    <div class="column content">
        <h2>What makes us different?</h2>
        <p>from other social networks:</p>
        <ul>
            <li>hight quality memes - we don't add <a href="https://xkcd.com/1683/">another layer of JPEG artifacts</a> to your memes</li>
            <li>we have <a href="https://medium.com/@echohack/promise-theory-the-ethics-of-algorithmic-news-feeds-and-chronological-timelines-443a044d1221">chronological timeline</a></li>
            <li>but you can also <a href="https://kyselo.eu/updates/post/29621">hear those who are less talkative</a> </li>
            <li>everything is public and you can <a href="https://kyselo.eu/updates/post/9421">follow our users</a> from your RSS client</li>
            <li>RSS client built into Kyselo <a href="https://kyselo.eu/todo.txt">coming soon™</a></li>
            <li>you can <a href="https://kyselo.eu/updates/post/17852">customize your profile page</a></li>
            <li>Kyselo is <a href="https://kyselo.eu/updates/post/12328">multipurpose</a> - can be used as social network but also as personal meme storage, bookmark manager, journal/diary or youtube playlist</li>
            <li>you can use our magic dice™ to find <a href="https://kyselo.eu/updates/post/23837">random memes</a></li>
            <li><a href="https://kyselo.eu/updates/post/20199">stole memes from others</a> with our bookmarklet</li>
            <?php if (Flight::config('chat_websocket_url')) { ?>
                <li>we have <a href="https://kyselo.eu/updates/post/23369">realtime chat</a></li>
            <?php } ?>
            <li>we are not evil corporation but <a href="https://kyselo.eu/act/privacy-policy">random guy hosting meme dump for others</a></li>
            <li>you can run your own instance, it <a href="https://github.com/severak/kyselo#software-equirements">runs on every cheap hosting</a> where Wordpress works</li>
        </ul>
        <p>For detailed feature comparation with other Soup.io clones see <a href="https://gist.github.com/severak/40ff7eb6eec7a16dacfd67dfd5b69c4f">this gist</a>.</p>
    </div>
</div>

<hr>

<div class="columns is-vcentered">
    <div class="column content">
        <h2>History</h2>
        <p>Back then in 2011 I discovered <a href="https://web.archive.org/web/20200430192929/https://www.soup.io/">Soup.io</a>.</p>
        <p>It quickly became my favorite source of memes, polish jokes, pics of cute girls &amp; cats. I survived there a lot of hard times and <em title="AKA 2015">the year that never happened</em>.</p>
        <p>I started to keeping my diary there which turned to be bad idea - Soup was all the 502 errors and no stability. So I decided to create my own clone which I did.</p>
        <p>In 2020 as a part of ongoing apocalypse, <a href="https://web.archive.org/web/20200712165208/https://kitchen.soup.io/post/696483222/The-sadest-news-in-the-soup-history">Soup.io closed it's doors</a>. </p>
        <p>So I decided to <a href="https://kyselo.eu/severak/post/7">open my clone</a> for general public.</p>
    </div>
    <div class="column">
        <img src="/st/img/soup_was_best.png" alt="" class="image">
    </div>
</div>
