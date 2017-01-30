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
	}
	
	function setGuid($text)
	{
		$this->_guid = $text;
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
			$this->_post['info'] = $def['info']; // info o souboru
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
		if ($def['type']=='file') {
			$this->_post['title'] = $def['title']; // titulek
			$this->_post['body'] = $def['body']; // popisek
			$this->_post['url'] = $def['url']; // URL obrázku
			$this->_post['start_date'] = $def['start_date']; // od
			$this->_post['end_date'] = $def['end_date']; // do
			$this->_post['location'] = $def['location']; // místo
			$this->_post['type'] = 8;
		}
		
		$this->tags = $def['tags'];
		
	}
	
	function setPubDate($date)
	{
		$this->_post['datetime'] = date('Y-m-d H:i:s', strtotime($date));
	}
	
	function closeItem()
	{
		var_export($this->_post);
		echo PHP_EOL;
		if (isset($this->_post['type'])) {
			$this->_db->insert('post', $this->_post);
			var_dump($this->_db->error());
		}
	}
} 
