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
            $out['preview_html'] = $post['preview_html'];
        }

        return json_encode($out) . PHP_EOL;
    }

    public function backup2post($line)
    {
        $in = json_decode($line, true);

        if (isset($in['is_metadata'])) {
            return $in; // metadata logic is in import itself
        }

        // guid
        $post = ['datetime'=>$in['datetime'], 'tags'=>implode(' ', $in['tags'] ? $in['tags'] : []), 'guid'=>generate_uuid()];

        if ($in['type']=='text') {
            $post['type'] = 1;
            $post['title'] = $in['title'];
            $post['body'] = $in['html'];
        } else if ($in['type']=='link') {
            $post['type'] = 2;
            $post['title'] = $in['title'];
            $post['body'] = $in['description'];
            $post['source'] = $in['url'];
        } else if ($in['type']=='quote') {
            $post['type'] = 3;
            $post['title'] = $in['byline'];
            $post['body'] = $in['quote'];
        } else if ($in['type']=='image') {
            $post['type'] = 4;
            $post['url'] = $in['url'];
            $post['body'] = $in['description'];
            $post['source'] = $in['source'];
        } else if ($in['type']=='video') {
            $post['type'] = 5;
            $post['title'] = $in['title'];
            $post['body'] = $in['body'];
            $post['source'] = $in['source'];
            $post['url'] = $in['source'];
            $info = get_info($post['url']);
            if ($info->type=='video') {
                $post['preview_html'] = $info->code;
            }
        }

        if (!empty($post['body']) && detect_xss($post['body'])) {
            return null; // we don't want to import this as it's probably some virus or other nasty stuff
        }

        return $post;
    }
}
