-- Kyselo/Resoup DB schema

-- public part

CREATE TABLE blogs (
	id INTEGER PRIMARY KEY,
	name TEXT UNIQUE,
	title TEXT,
	about TEXT,
	avatar_url TEXT,
	since TEXT,
	is_group INT DEFAULT 0,
	user_id INT NOT NULL,
	is_visible INT DEFAULT 1,
	is_nsfw INT DEFAULT 0,
	is_spam INT DEFAULT 0
);

-- todo: blog_skins

CREATE TABLE posts (
	id INTEGER PRIMARY KEY,
	blog_id INT NOT NULL,
	author_id INT NOT NULL,
	datetime INT,
	guid TEXT,
	type INT,
	title TEXT,
	body TEXT,
	source TEXT,
	url TEXT,
	file_info TEXT,
	rating INT,
	start_date TEXT,
	end_date TEXT,
	location TEXT,
	preview_html TEXT,
	tags TEXT,
	reposts_count INT DEFAULT 0,
	comments_rount INT DEFAULT 0,
	is_visible INT DEFAULT 1,
	is_nsfw INT DEFAULT 0,
	is_spam INT DEFAULT 0
);

CREATE TABLE post_tags (
	post_id INT,
	blog_id INT,
	tag TEXT
);

CREATE TABLE reposts (
	post_id INT NOT NULL,
	repost_id INT NOT NULL,
	reposted_by INT NOT NULL
);

CREATE TABLE comments (
	id INTEGER PRIMARY KEY,
	post_id INT NOT NULL,
	author_id INT NOT NULL,
	datetime INT,
	text TEXT,
	is_visible INT DEFAULT 1,
	is_spam INT DEFAULT 0
);

CREATE TABLE friendships (
	id INTEGER PRIMARY KEY,
	from_blog_id INT NOT NULL,
	to_blog_id INT NOT NULL,
	is_bilateral INT DEFAULT 0,
	since TEXT
);

CREATE TABLE memberships (
	id INTEGER PRIMARY KEY,
	blog_id INT NOT NULL,
	member_id INT NOT NULL,
	is_admin INT DEFAULT 0,
	is_founder INT DEFAULT 0,
	since TEXT
);

-- private part

CREATE TABLE users (
	id INTEGER PRIMARY KEY,
	blog_id INT NOT NULL,
	email TEXT,
	password TEXT,
	is_active INT DEFAULT 0,
	activation_token TEXT,
	token_expires TEXT
);

-- todo: notification

CREATE TABLE messages (
	id INTEGER PRIMARY KEY,
	id_from INT NOT NULL,
	id_to INT NOT NULL,
	text TEXT,
	image_url TEXT,
	datetime INT NOT NULL,
	is_read INT NOT NULL DEFAULT 0
);