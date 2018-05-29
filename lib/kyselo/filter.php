<?php
namespace kyselo;
use severak\database\rows;
use PDO;

class filter
{
    public $mode = 'own'; // own, friends, all, thread
    public $blogId = 0;
    public $tags = [];
    public $since = null;

    public $moreLink = null;

    /** @var rows */
    protected $_rows;

    function __construct(rows $rows)
    {
        $this->_rows = $rows;
    }

    public function posts()
    {
        // todo
        // rovnou zapracovat skupiny

        $rows = $this->_rows;
        $Q = $rows->query('SELECT a.name, a.avatar_url, g.name AS group_name, g.avatar_url AS group_avatar_url, p.*
        FROM posts p 
        INNER JOIN blogs a ON p.author_id=a.id
        LEFT OUTER JOIN blogs g ON p.blog_id=g.id AND p.author_id!=p.blog_id');

        // todo: zde tags

        $Q = $Q->add('WHERE p.is_visible=1');
        
        if ($this->mode=='own') {
            $Q = $Q->add('AND p.blog_id=?', [$this->blogId]);
        }

        if ($this->since) {
            // todo: nefunguje stránkování, proč?
            if (!is_numeric($this->since)) $this->since = strtotime($this->since);
            $Q = $Q->add(' AND p.datetime <= ?', [$this->since]);
        }

        $Q = $Q->add('ORDER BY p.datetime DESC LIMIT 31');

        //echo $Q->interpolate(); die;

        $posts = $rows->execute($Q)->fetchAll(PDO::FETCH_ASSOC);

        if (count($posts)==31) {
            $lastPost = array_pop($posts);

            $moreParams = ['since'=>date('Y-m-d\TH:i:s', $lastPost['datetime'])];

            if ($this->mode=='own') {
                $this->moreLink = '/' . $lastPost['name'] . '?' . http_build_query($moreParams);
            }
            if ($this->mode=='all') {
                $this->moreLink = '/all?' . http_build_query($moreParams);
            }

        }

        return $posts;
    }
}