<?php
namespace kyselo\backup;

class format
{
    public function post2backup($post)
    {
        $tags = [];
        if (!empty($post['tags'])) {
            $tags = explode(' ', $post['tags']);
        }

        $out = ['id'=>$post['id'], 'posted_by'=>$post['name'], 'datetime'=>$post['datetime'], 'tags'=>$tags, 'is_repost'=>$post['repost_of'] ? 1 : 0];

        if ($post['type']==1) {
            $out['type'] = 'text';
            $out['title'] = $post['title'];
            $out['html'] = $post['body'];
        } else if ($post['type']==2) {
            $out['type'] = 'link';
            $out['title'] = $post['title'];
            $out['description'] = $post['body'];
            $out['url'] = $post['source'];
        } else if ($post['type']==3) {
            $out['type'] = 'quote';
            $out['byline'] = $post['title'];
            $out['quote'] = $post['body'];
        } else if ($post['type']==4) {
            $out['type'] = 'image';
            $out['url'] = $post['url'];
            $out['description'] = $post['body'];
            $out['source'] = $post['source'];
        } else if ($post['type']==5) {
            $out['type'] = 'video';
            $out['title'] = $post['title'];
            $out['body'] = $post['body'];
            $out['source'] = $post['source'];
        }

        echo json_encode($out) . PHP_EOL;
    }
}
