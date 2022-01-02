# Kyselo exchange/backup format

Kyselo can generate backup of users blogs in [JSON lines](https://jsonlines.org/) format formatted using these rules:

First line is metadata. It's used to check if all was properly imported. It's JSON object in this format:

- `is_metadata` = `true`
- `count` - number of exported posts
- `blog` - info about exported blog 

Following lines are backup itself where each line is JSON object representing one posted post.

Every post has following metadata:

- `id` - unique ID of post
- `posted_by` - username of user who posted this (this is because one day we will be able to backup groups)
- `datetime` - date and time of post as UNIX timestamp
- `tags` - tags of post (as array)
- `is_repost` - if the post was reposted from someone
- `type` - type of post

Additional data are present according to types of post.

## supported types of posts

### `text`

- `title` - title of the text post (can be empty)
- `html` - post itself as HTML

### `link`

- `link` - URL of link
- `title` - title of link
- `description` - (optional) description as HTML

### `quote`

- `quote` - quote itself as HTML
- `byline` - [by-line](https://en.wikipedia.org/wiki/Byline) of quote

### `image`

- `url` - relative URL to image itself
- `description` - (optional) description of image as HTML
- `source` - (optional) source (URL) where image came from

### `video`

- `source` - source URL of video (link to youtube mostly)
-  `title` - title of video
- `description` - (optional) description of video as HTML
- `preview_html` - (optional) HTML to embed video player

## TODO

- how to backup where the post was reposted from?
- how to backup comments? it's good idea after all?
- it's properly implemented?
- it's useful to have this in header? (for cross-instance reposting purposes)
- add feedback from Zorp
