<?php
namespace kyselo;

class embed
{
    static function embed($url)
    {
        if (strpos($url, 'youtube.com')!==false || strpos($url, 'youtu.be')!==false) {
            return self::_youtube($url);
        } elseif (strpos($url, 'loforo.com/')!==false) {
            return self::_loforo($url);
        } elseif (strpos($url, 'souper.io/')!==false) {
            return self::_souper($url);
        } elseif (strpos($url, 'twitter.com/')!==false) {
            return self::_twitter($url);
        }

        return null;
    }

    protected static function _youtube($url)
    {
        $json = file_get_contents('https://www.youtube.com/oembed?url='.urlencode($url).'&format=json');
        $data = json_decode($json, true);
        return (object) [
            'type'=>'video',
            'title'=>$data['title'],
            'code'=>$data['html'],
            'url'=>$url
        ];
    }

    protected static function _loforo($url)
    {
        $html = file_get_contents($url);
        if (!$html) return null;
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xml = simplexml_import_dom($dom);
            $articles = $xml->xpath('//article');
            if (count($articles)==1) {
                $img = $articles[0]->xpath('//img[@loading]');
                if ($img) {
                    $src = (string) $img[0]['src'];
                    if (strpos($src, '/')==0) {
                        // it's hosted on loforo
                        // it can be also hosted elsewhere (e.g. tumblr)
                        $src = 'https://loforo.com' . $src;
                    }
                    return (object) [
                        'type'=>'photo',
                        'url'=>$url,
                        'image'=>$src
                    ];
                }
            }

        } catch (\Exception $e) {

        }

        return null;
    }


    protected static function _souper($url)
    {
        $html = file_get_contents($url);
        if (!$html) return null;
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xml = simplexml_import_dom($dom);
            $posts = $xml->xpath('//div[@class="post"]');
            if (count($posts)==1) {
                $img = $posts[0]->xpath('//img[@alt]');
                if ($img) {
                    return (object) [
                        'type'=>'photo',
                        'url'=>$url,
                        'image'=>$img[0]['src']
                    ];
                }
            }
        } catch (\Exception $e) {

        }
        return null;
    }

    protected static function _twitter($url)
    {
        $html = file_get_contents($url);
        if (!$html) return null;
        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML($html);
            $xml = simplexml_import_dom($dom);
            $metas = [];
            foreach ($xml->xpath('//meta') as $meta) {
                $metas[(string) $meta['property'] ] = (string) $meta['content'];
            }

            if (strpos($metas['og:image'], '/profile_images/')!==false || strpos($metas['og:image'], '/card_imag/')!==false) {
                dump($metas);
                return (object) [
                    'type'=>'article',
                    'title'=> $metas['og:title'],
                    'description'=>$metas['og:description'],
                    'url'=>$url
                ];
            } else {
                return (object) [
                    'type'=>'photo',
                    'url'=>$url,
                    'image'=>$metas['og:image'],
                    'description'=>(strpos($metas['og:description'], '“https://t.co/')!==false ? $metas['og:title'] :  $metas['og:description'])
                ];
            }
        } catch (\Exception $e) {

        }

        return null;
    }
}
