    <footer class="footer">
    <p>Kyselo  - <a href="">opensource software</a> by <a href="">Severák</a>.
    <?php if (Flight::config('hosted_by')) { ?>
    This instance is hosted by <?php
    if (Flight::config('hosted_by_url')) echo '<a href="' . Flight::config('hosted_by_url') . '">'; 
    echo Flight::config('hosted_by'); 
    if (Flight::config('hosted_by_url')) echo '</a>'; 
    ?>.
    <?php } // if ?>
    </p>
    </footer>
</div>

<script src="/st/js/kyselo.js?v=2020-01-25"></script>
<?php if (Flight::config('footer_javascript')) {
    echo Flight::config('footer_javascript');

}?>
</body>
</html>