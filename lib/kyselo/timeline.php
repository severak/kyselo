<?php
namespace kyselo;
use severak\database\rows;
use PDO;

class timeline
{
    public $mode = 'own'; // own, friends, all, thread
    public $blogId = 0;
    public $tags = null;
    public $type = null;
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
        // todo - name má být group nebo author?
        $rows = $this->_rows;
        $Q = $rows->query('SELECT a.name, a.avatar_url, g.name AS group_name, g.avatar_url AS group_avatar_url, p.*
        FROM posts p 
        INNER JOIN blogs a ON p.author_id=a.id
        LEFT OUTER JOIN blogs g ON p.blog_id=g.id AND p.author_id!=p.blog_id');

        if ($this->mode=='friends') {
            $Q = $Q->add('INNER JOIN friendships f ON f.to_blog_id=p.blog_id AND f.from_blog_id=?', [$this->blogId]);
        }

        if (!empty($this->tags)) {
            $Q = $Q->add('INNER JOIN post_tags t ON p.id=t.post_id AND t.blog_id=p.blog_id AND t.tag=?', [$this->tags]);
        }

        $Q = $Q->add('WHERE p.is_visible=1');
        
        if ($this->mode=='own') {
            $Q = $Q->add('AND p.blog_id=?', [$this->blogId]);
        }

        if ($this->since) {
            if (!is_numeric($this->since)) $this->since = strtotime($this->since);
            $Q = $Q->add(' AND p.datetime <= ?', [$this->since]);
        }

        $type2code = ['text'=>1, 'link'=>2, 'quote'=>3, 'image'=>4, 'video'=>5, 'file'=>6, 'review'=>7, 'event'=>8];
        if ($this->type && isset($type2code[$this->type])) {
            $Q = $Q->add(' AND p.type = ?', [$type2code[$this->type]]);
        }

        $Q = $Q->add('ORDER BY p.datetime DESC LIMIT 31');

        //echo $Q->interpolate(); die;

        $posts = $rows->execute($Q)->fetchAll(PDO::FETCH_ASSOC);

        if (count($posts)==31) {
            $lastPost = array_pop($posts);

            $moreParams = ['since'=>date('Y-m-d\TH:i:s', $lastPost['datetime'])];

            if ($this->type) {
                $moreParams['type'] = $this->type;
            }

            if ($this->tags) {
                $moreParams['tags'] = $this->tags;
            }

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