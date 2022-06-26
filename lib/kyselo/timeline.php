<?php
namespace kyselo;
use severak\database\rows;
use PDO;

class timeline
{
    public $mode = 'own'; // own, friends, all, one
    public $blogId = 0;
	public $postId = null;
    public $tag = null;
    public $type = null;
    public $since = null;
    public $name = null;
    public $limit = 30;

    public $withComments = false;

    public $moreLink = null;

    public $moreSince = null;

    public $currentParams = '';

    /** @var rows */
    protected $_rows;

    function __construct(rows $rows)
    {
        $this->_rows = $rows;
    }

    public function filter($params, $canSetSince=true)
    {
        if ($canSetSince && !empty($params['since'])) {
            $this->since = $params['since'];
        } else {
            unset($params['since']);
        }
        if (!empty($_GET['tag'])) {
            $this->tag = $params['tag'];
        }
        if (!empty($_GET['type'])) {
            $this->type = $params['type'];
        }

        if (!empty($params)) {
            $this->currentParams = '?' . http_build_query($params);
        }
    }

    public function posts()
    {
        // todo - name má být group nebo author?
        $rows = $this->_rows;
        $Q = $rows->query('SELECT a.name, a.avatar_url, COALESCE(g.name, a.name) AS slug_name, g.name AS group_name, g.avatar_url AS group_avatar_url, p.*
        FROM posts p 
        INNER JOIN blogs a ON p.author_id=a.id
        LEFT OUTER JOIN blogs g ON p.blog_id=g.id AND p.author_id!=p.blog_id');

        if ($this->mode=='friends') {
            $Q = $Q->add('INNER JOIN friendships f ON f.to_blog_id=p.blog_id AND f.from_blog_id=?', [$this->blogId]);
        }

        if (!empty($this->tag)) {
            $Q = $Q->add('INNER JOIN post_tags t ON p.id=t.post_id AND t.blog_id=p.blog_id AND t.tag=?', [$this->tag]);
        }

        $Q = $Q->add('WHERE p.is_visible=1');

        if ($this->mode=='own') {
            $Q = $Q->add('AND p.blog_id=?', [$this->blogId]);
        }

		if ($this->mode=='one') {
			$Q = $Q->add('AND p.id=? AND p.blog_id=?', [$this->postId, $this->blogId]);
		}

        if ($this->mode=='all') {
            $Q = $Q->add('AND p.repost_of IS NULL');
        }

        if ($this->since) {
            if (!is_numeric($this->since)) $this->since = strtotime($this->since);
            $Q = $Q->add(' AND p.datetime <= ?', [$this->since]);
        }

        $type2code = ['text'=>1, 'link'=>2, 'quote'=>3, 'image'=>4, 'video'=>5, 'file'=>6, 'review'=>7, 'event'=>8];
        if ($this->type && isset($type2code[$this->type])) {
            $Q = $Q->add(' AND p.type = ?', [$type2code[$this->type]]);
        }

        $Q = $Q->add('ORDER BY p.datetime DESC LIMIT ?', [$this->limit+1]);

        //echo $Q->interpolate(); die;

        $posts = $rows->execute($Q)->fetchAll(PDO::FETCH_ASSOC);

        $this->moreSince = null;

		if (count($posts)==31) {
            $lastPost = array_pop($posts);

            $moreParams = ['since'=>date('Y-m-d\TH:i:s', $lastPost['datetime'])];
            $this->moreSince = $lastPost['datetime'];

            if ($this->type) {
                $moreParams['type'] = $this->type;
            }

            if ($this->tag) {
                $moreParams['tag'] = $this->tag;
            }

            if ($this->mode=='own') {
                $this->moreLink = '/' . $lastPost['slug_name'] . '?' . http_build_query($moreParams);
            }
            if ($this->mode=='friends') {
                $this->moreLink = '/' . $this->name . '/friends?' . http_build_query($moreParams);
            }
            if ($this->mode=='all') {
                $this->moreLink = '/all?' . http_build_query($moreParams);
            }

        }

        foreach ($posts as $ord=>$post) {
            if (!empty($post['repost_of'])) {
                $posts[$ord]['reposted_from'] = $rows
                    ->with('blogs', 'blog_id')
                    ->one('posts', $post['repost_of']);
            }

            if ($post['reposts_count']>0) {
                $posts[$ord]['reposted_by'] = $rows
                    ->with('blogs', 'reposted_by')
                    ->more('reposts', ['post_id'=>$post['id']]);
            }

            $posts[$ord]['comments'] = [];
            if ($this->withComments && $post['comments_count']>0) {
                $posts[$ord]['comments'] = $rows
                    ->with('blogs', 'author_id')
                    ->more('comments', ['post_id'=>$post['id'], 'is_visible'=>'1'], ['datetime'=>'asc'], 999);
            }

        }

        return $posts;
    }

    public function countPages()
    {
        if ($this->mode != 'own') {
            throw new \BadMethodCallException('countPageAndPages only works in own mode now');
        }

        if ($this->tag || $this->type) {
            return []; // we cannot count this now
        }

        $postsTotal = $this->_rows->count('posts', $this->_rows->fragment('blog_id=? AND is_visible=1', [$this->blogId]));
        if ($this->since) {
            $postsRemains = $this->_rows->count('posts', $this->_rows->fragment('blog_id=? AND is_visible=1 AND datetime <= ?', [$this->blogId, $this->since]));
        } else {
            $postsRemains = $postsTotal;
        }

        return ['total'=>ceil($postsTotal/30), 'remains'=>floor($postsRemains/30)];
    }

    public function moveToNextPage()
    {
        if ($this->moreSince) {
            $this->since = $this->moreSince;
            return true;
        }
        return false;
    }

    public function lastPostBy()
    {
        /*
SELECT blog_id, MAX(datetime) as maxdt, id
FROM posts
GROUP BY blog_id
         */

        $rows = $this->_rows;


        $Q = $rows->query('SELECT a.name, a.avatar_url, COALESCE(g.name, a.name) AS slug_name, g.name AS group_name, g.avatar_url AS group_avatar_url, p.*
FROM (
SELECT blog_id, MAX(datetime) as maxdt, id as post_id
FROM posts
GROUP BY blog_id
ORDER BY maxdt DESC
) AS lsu
INNER JOIN posts p ON lsu.post_id=p.id
INNER JOIN blogs a ON p.author_id=a.id
LEFT OUTER JOIN blogs g ON p.blog_id=g.id AND p.author_id!=p.blog_id
LIMIT 30');


        // TODO - stránkování



        return $rows->execute($Q)->fetchAll(PDO::FETCH_ASSOC);
    }
}
