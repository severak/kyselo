    <footer class="footer kyselo-footer">
    <p>Kyselo  - <a href="https://bitbucket.org/severak/kyselo">opensource software</a> by <a href="http://tilde.town/~severak/">Severák</a>.
    <?php if (Flight::config('hosted_by')) { ?>
    Hosted by <?php
    if (Flight::config('hosted_by_url')) echo '<a href="' . Flight::config('hosted_by_url') . '">';
    echo Flight::config('hosted_by');
    if (Flight::config('hosted_by_url')) echo '</a>';
    ?>.
    <?php } // if ?>
    </p>

    <?php if (Flight::config('tos_post')) {
        echo '</p><a href="/act/tos">Terms of service</a></p>';
    } ?>
    <?php if (Flight::config('gdpr_post')) {
        echo '</p><a href="/act/privacy-policy">Privacy policy</a></p>';
    } ?>

    </footer>
</div>

<script src="/st/js/kyselo.js?v=2023-09-24"></script>
<?php if (Flight::config('footer_javascript')) {
    echo Flight::config('footer_javascript');

}?>
</body>
</html>
