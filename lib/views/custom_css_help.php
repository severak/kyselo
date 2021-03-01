
<hr>
<div class="content">

<p><button id="reloadStyles" class="button is-warning">update custom CSS preview</button></p>

<h2 class="subtitle">custom CSS style help &amp; generator</h2>

<p>Please use these CSS selectors to target various parts of page:</p>

<ul>
    <li><code>body</code> - page background</li>
    <li><code>.kyselo-container</code> - main column of content (use <code>!important</code> if you want to set <code>max-width</code>)</li>
    <li><code>.kyselo-about</code> - about text of your blog</li>
    <li><code>.kyselo-post</code> - each individual post</li>
    <li><code>.kyselo-image</code> - images in your posts</li>
    <li><code>.kyselo-footer</code> - page footer</li>
    <li><code>.is-nsfw</code> - NSFW posts</li>
</ul>

<p>These are guaranteed to work even after future desing changes.</p>
<p>Keep in mind that there is already some mobile-friendly CSS.</p>

<p>Use images you uploaded to Kyselo before as backgrounds.</p>

<p>Those who cannot code their own CSS can use:</p>
</div>

<h2 class="subtitle">CSS generator 3000</h2>
<form>
    <div class="field">
        <label class="label">Page background URL</label>
        <div class="control">
            <input class="input" type="text" placeholder="https://kyselo.eu/st/img/undraw_different_love_a3rg.png" id="page_background_url">
        </div>
    </div>

    <div class="field">
        <label class="label">Page background color</label>
        <div class="control">
            <input class="input" type="color" id="page_background_color" value="#002b36">
        </div>
    </div>

    <div class="field">
        <label class="label">Content background color</label>
        <div class="control">
            <input class="input" type="color" id="content_background_color" value="#fdf6e3">
        </div>
    </div>

    <div class="field">
        <label class="label">Content text color</label>
        <div class="control">
            <input class="input" type="color" id="content_text_color" value="#000">
        </div>
    </div>

    <div class="field">
        <label class="label">Headers color</label>
        <div class="control">
            <input class="input" type="color" id="headers_color" value="#cb4b16">
        </div>
    </div>

    <div class="field">
        <label class="label">Links color</label>
        <div class="control">
            <input class="input" type="color" id="links_color" value="#268bd2">
        </div>
    </div>

    <div class="field">
        <label class="label">Font specification</label>
        <div class="control">
            <input class="input" type="text" id="font" placeholder="Comic Sans, cursive">
        </div>
    </div>

    <div class="field">
        <div class="control">
            <button class="button is-warning" id="generate_css">generate CSS code</button>
        </div>
    </div>

</form>

<script>
$('#reloadStyles').on('click', function () {
    $('style').remove();
    var newStyle = $('<style/>').html($('#custom_css').val());
    $(document.body).append(newStyle);
});

$('#generate_css').on('click', function (event) {
    event.preventDefault();
   if (!confirm('It will rewrite your current CSS. Do you want to continue?')) {
       return;
   }
   var css = '/* generated by CSS generator 3000 */\n';
   css += 'body { background-color: ' + $('#page_background_color').val() + ';\n';
    css += 'color: ' + $('#content_text_color').val() + ';\n';
   if ($('#page_background_url').val()) {
       css += ' background-image: url(' + $('#page_background_url').val() + ');\n';
   }
    if ($('#font').val()) {
        css += ' font-family: ' + $('#font').val() + ';\n';
    }
   css += '}\n';
   css += '.kyselo-container { background-color: ' + $('#content_background_color').val() + '; }\n';
   css += '.kyselo-container h1, .kyselo-container h2, .kyselo-container label, .kyselo-container hr { color: ' + $('#headers_color').val() + '; }\n';
   css += '.kyselo-container a { color: ' + $('#links_color').val() + '; }\n';


    $('#custom_css').val(css);
    $('style').remove();
    var newStyle = $('<style/>').html(css);
    $(document.body).append(newStyle);
});
</script>