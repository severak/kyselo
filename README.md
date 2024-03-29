# project Kyselo

self-hosted Soup.io open source clone

public beta is at https://kyselo.eu

## Current status

I am able to import backup/archived RSS feed and display it in an ugly interface. It's also possible to post new content, repost, create & join groups and send DMs to other users.

Lot of features are missing or in development currently (see [`todo.txt`](todo.txt)).

See [public beta](https://kyselo.eu/all) (you need invitation code).

## Project goals

 - be able to import soup backups - done ✔ 
 - make self-hosted Soup.io clone - work in progress ⌛ 
 - create social network, which respect it's users - I am trying to ⌛ 
 - try to create distributed social network (via built-in RSS client and easy reposting from sister sites, namely [souper.io]())
 
## Milestones

 - v 0.5 - easy import and read only display of backup ✔ 
 - v 0.6 - it's possible to post updates ✔ 
 - v 1.0 - usable multiuser Soup.io clone ✔ 
 - v 2.0 - ~~distributed social network~~ integrated RSS reader

## Software equirements

 - PHP 7 (5.6 probably works too)
 - PDO with SQlite3
 - command line access to server or PHP at localhost
 - some web development/PHP knowledge

## Usage

1. run `php setup.php` command line wizard to setup your kyselo instance
2. (optional) run `php import.php yourbackupname.rss yourname` to load your backup
3. (optional) run `php download_images.php yourname` to download images from soup CDN
4. upload to web hosting (or run local web server)
5. visit `your-kyselo-instance.what/yourname`

## Credits

Made by [Severák](https://tilde.town/~severak/) of [http://severak.soup.io/](https://web.archive.org/web/20191225090201/http://severak.soup.io/), now at https://kyselo.eu/severak

I used following components:

 - [Flight framework](http://flightphp.com) by Mike Cao
 - [Flourish unframework](http://flourishlib.com) by Will Bond
 - [Medoo](http://medoo.in) database framework by Catfan (they are also [nice social network](https://catfan.me/))
 - [Pure CSS](https://purecss.io) framework by Yahoo! Inc
 - [Font Awesome](http://fontawesome.io) by Dave Gandy
 - [Medium Editor](https://yabwe.github.io/medium-editor/)
 - [Undraw](https://undraw.co/) illustrations
