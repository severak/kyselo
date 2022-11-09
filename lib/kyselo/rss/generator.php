<?php
namespace kyselo\rss;
class generator
{
    public $urlPrefix = '';
    public $pathPrefix = '';
    public $mode = 'blog';

    public $tagged = null;

    function generate($blog, $posts)
    {
        $rss = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><rss version="2.0"/>');
        $rss->channel->title = $blog['title'] . ($this->tagged ? sprintf(' - tagged #%s', $this->tagged) : '');
        $rss->channel->link = $this->urlPrefix . $blog['name'];
        $rss->channel->description = strip_tags($blog['about']);


        foreach ($posts as $post)
        {
            $item = $rss->channel->addChild('item');
            $item->title = ($this->mode=='all' ? $post['slug_name'] . ': ' : '') . (empty($post['title']) ? '(no title)' : $post['title']);
            $item->link = $this->urlPrefix . $post['slug_name'] . '/post/' . $post['id'];
            $item->guid = $this->urlPrefix . $post['slug_name'] . '/post/' . $post['id'];
            $item->guid['isPermaLink'] = 'true';

            $desc = '';
            if ($post['type']==1 || $post['type']==3) {
                $desc = $post['body'];
            } elseif (in_array($post['type'], [2, 5, 6])) {
                // link, video, file
                $desc = '<a href="'.$post['url'].'">'.$post['url'].'</a>';
            } elseif ($post['type']==4) {
                $desc = '<img src="'.$this->urlPrefix . $post['url'].'">';
            }

            $item->description = $desc;

            if ($post['type']==4 && file_exists($post['url'])) {
                $item->enclosure['url'] = $this->urlPrefix . $post['url'];
                $item->enclosure['length'] = filesize($this->pathPrefix . $post['url']);
                $item->enclosure['type'] = \fFile::determineMimeType($this->pathPrefix . $post['url']);
            }

            $item->pubDate = date('r', $post['datetime']);
        }

        return $rss->asXML();
    }
}
