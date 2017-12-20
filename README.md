# project Kyselo

self-hosted Soup.io open source clone

**[download here](https://bitbucket.org/severak/kyselo/get/0.5.zip)**

## Requirements

 - PHP 5.6
 - PDO with SQlite3
 - command line access to server or PHP at localhost
 - some web development/PHP knowledge

## Usage

1. run `php setup.php` command line wizard to setup your kyselo instance
2. (optional) run `php import.php yourbackupname.rss yourname` to load your backup
3. (optional) run `php download_images.php yourname` to download images from soup CDN
4. upload to web hosting (or run local web server)
5. visit `your-kyselo-instance.what/yourname`

## Project goals

 - be able to import soup backups - done ✔ 
 - make self-hosted Soup.io clone - work in progress ⌛ 
 - create social network, which respect it's users
 - try to create distribute social network
 
## Milestones

 - v 0.5 - easy import and read only display of backup ✔ 
 - v 0.6 - it's possible to post updates ✔ 
 - v 1.0 - usable multiuser Soup.io clone ⌛ 
 - v 2.0 - distributed social network

## Current status

I am able to import backup/archived RSS feed and display it in an ugly interface.

See examples of imported content:

 - [severak's soup](http://resoup.svita.cz/severak) (made from my own soup backup from September 2015)
 - [cat's soup](http://resoup.svita.cz/cats) (made from [RSS fragments](http://web.archive.org/web/*/http://cats.soup.io/rss) acquired via archive.org)

## Credits

Made by [Severák](https://severak.neocities.org) of http://severak.soup.io/

I used following components:

 - [Flight framework](http://flightphp.com) and [Sparrow](https://github.com/mikecao/sparrow) database toolkit by Mike Cao
 - [Flourish unframework](http://flourishlib.com) by Will Bond
 - [Medoo](http://medoo.in) database framework by Catfan
 - [Pure CSS](https://purecss.io) framework by Yahoo! Inc
 - [Font Awesome](http://fontawesome.io) by Dave Gandy
 - [Medium Editor](https://yabwe.github.io/medium-editor/)
 