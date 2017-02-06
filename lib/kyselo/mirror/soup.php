<?php
class kyselo_mirror_soup
{
	private $_db = null;
	private $_post = null;
	private $_tags = null;
	private $_destination = null;
	
	function __construct(medoo $db)
	{
		$this->_db = $db;
	}
	
	function importFeed($file, $destinationUser)
	{
		$this->_destination = $destinationUser;
		$saxofon= new brick_saxofon;
		$saxofon->registerOpen("rss/channel/item", array($this, 'openItem'));
		$saxofon->registerText("rss/channel/item/guid", array($this, 'setGuid'));
		$saxofon->registerText("rss/channel/item/pubDate", array($this, 'setPubDate'));
		$saxofon->registerText("rss/channel/item/soup:attributes", array($this, 'setSoup'));
		$saxofon->registerClose("rss/channel/item", array($this, 'closeItem'));
		
		$saxofon->readFile($file);
	}
	
	function openItem()
	{
		$this->_post = array();
		$this->_post['author_id'] = $this->_destination;
		$this->_post['blog_id'] = $this->_destination;

		$this->_tags = array();
	}
	
	function setGuid($text)
	{
		$this->_post['guid'] = $text;
	}
	
	function setSoup($text)
	{
		$def = json_decode($text, true);
		
		// text
		if ($def['type']=='regular') {
			$this->_post['title'] = $def['title'];
			$this->_post['body'] = $def['body'];
			$this->_post['type'] = 1;
		}
		
		
		// link
		if ($def['type']=='link') {
			$this->_post['title'] = $def['title'];
			$this->_post['body'] = $def['body'];
			$this->_post['source'] = $def['source']; // samotný odkaz
			$this->_post['type'] = 2;
		}
		
		// quote
		if ($def['type']=='quote') {
			$this->_post['title'] = $def['title']; // koho citujeme
			$this->_post['body'] = $def['body'];
			$this->_post['source'] = $def['source'];
			$this->_post['type'] = 3;
		}
		
		// image
		if ($def['type']=='image') {
			$this->_post['body'] = $def['body']; // popisek
			$this->_post['url'] = $def['url']; // URL obrázku
			$this->_post['source'] = $def['source'];
			$this->_post['type'] = 4;
		}
		
		
		// video
		if ($def['type']=='video') {
			$this->_post['body'] = $def['body']; // popisek
			$this->_post['url'] = $def['embedcode_or_url']; // URL obrázku
			$this->_post['source'] = $def['source'];
			$this->_post['type'] = 5;
		}
		
		// file
		if ($def['type']=='file') {
			$this->_post['title'] = $def['title']; // titulek
			$this->_post['body'] = $def['body']; // popisek
			$this->_post['file_info'] = $def['info']; // info o souboru
			$this->_post['url'] = $def['url']; // URL souboru
			$this->_post['type'] = 6;
		}
		
		// review
		if ($def['type']=='review') {
			$this->_post['title'] = $def['title']; // titulek
			$this->_post['body'] = $def['body']; // popisek
			$this->_post['url'] = $def['embedcode_or_url']; // URL obrázku
			$this->_post['rating'] = $def['rating']; // rating
			$this->_post['source'] = $def['source']; // zdroj
			$this->_post['type'] = 7;
		}
		
		// event
		if ($def['type']=='event') {
			$this->_post['title'] = $def['title']; // titulek
			$this->_post['body'] = $def['body']; // popisek
			$this->_post['url'] = $def['url']; // URL obrázku
			$this->_post['start_date'] = $def['start_date']; // od
			$this->_post['end_date'] = $def['end_date']; // do
			$this->_post['location'] = $def['location']; // místo
			$this->_post['type'] = 8;
		}
		
		$this->_tags = $def['tags'];
	}
	
	function setPubDate($date)
	{
		$this->_post['datetime'] = strtotime($date);
	}
	
	function closeItem()
	{
		if (isset($this->_post['type'])) {
			if ($this->_db->has('posts', ['guid'=>$this->_post['guid']])) {
				echo 'found duplicate while import ' . $this->_post['guid'] . PHP_EOL;
			} else {
				$newPostId = $this->_db->insert('posts', $this->_post);
				foreach ($this->_tags as $tag) {
					$this->_db->insert('post_tags', ['post_id'=>$newPostId, 'tag'=>$tag]);
				}
			}
		}
	}
} 
