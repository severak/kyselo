<?php
namespace kyselo;
use severak\database\rows;

class filter
{
    public $mode = 'own'; // own, friends, all, thread
    public $blogId = 0;
    public $tags = [];
    public $since = null;

    public $moreLink = null;

    protected $_rows;

    function __construct(rows $rows)
    {
        $this->_rows = $rows;
    }

    public function posts()
    {
        // todo
    }
}