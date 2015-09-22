CREATE TABLE post (
	id INTEGER PRIMARY KEY,
	blog_id INT,
	author_id INT,
	datetime INT, -- pro lepší řazení
	type INT,
	title TEXT,
	body TEXT,
	source TEXT,
	url TEXT,
	info TEXT,
	rating INT,
	start_date TEXT,
	end_date TEXT,
	location TEXT
);