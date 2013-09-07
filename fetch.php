<?php
/*
 * ZhihuDaily RSS
 * by Malash(http://malash.me/)
 */

/* config */
$MAX_ITEMS = 20;
$MAX_DAYS = 2;

/**********************************************************************/
$str = '';
$items_fetched = 0;
date_default_timezone_set('PRC');
set_time_limit(180);

function fetch_item($item, $time) {
    global $items_fetched;
    $str = '';
    $content = json_decode(file_get_contents($item['url']), 1);
    //print_r($content);
    $str .= '<item>' . "\n";
    $str .= '<title><![CDATA[' . $item['title'] . ']]></title>' . "\n";
    $str .= '<link>' . $item['share_url'] . '</link>' . "\n";
    $str .= '<pubDate>' . date(DATE_RSS, $time) . '</pubDate>' . "\n";
    $str .= '<dc:creator>知乎日报</dc:creator>' . "\n";
    $str .= '<guid isPermaLink="false">' . $item['share_url'] . '</guid>' . "\n";
    $img = '<img src="' . $item['image'] . '" alt="' . $item['title'] . '" />' . "\n";
    $str .= '<description><![CDATA[' . $content['body'] . ']]></description>' . "\n";
    $str .= '<content:encoded><![CDATA[' . $content['body'] . ']]></content:encoded>' . "\n";
    $str .= '</item>' . "\n";
    $items_fetched++;
    return $str;
}

function fetch_day($day) {
    global $items_fetched;
    global $MAX_ITEMS;
    $str = '';
    if ($day === 0) {
        $api_url = 'http://news.at.zhihu.com/api/1.2/news/latest';
    } else {
        $api_url = 'http://news.at.zhihu.com/api/1.2/news/before/' . date('Ymd', time() - $day * 3600 * 24);
    }
    $content = json_decode(file_get_contents('http://news.at.zhihu.com/api/1.2/news/latest'), 1);
    $count_news = count($content['news']);
    for ($i = 0; $i < $count_news; $i++) {
        $str .= fetch_item($content['news'][$i], strtotime(date('Ymd', time() - $day * 3600 * 24)) + 3600 * ($count_news - $i));
        if ($items_fetched >= $MAX_ITEMS) {
            break;
        }
    }
    $days_fetched++;
    return $str;
}

$str .= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$str .= '<rss version="2.0"' . "\n";
$str .= 'xmlns:content="http://purl.org/rss/1.0/modules/content/"' . "\n";
$str .= 'xmlns:wfw="http://wellformedweb.org/CommentAPI/"' . "\n";
$str .= 'xmlns:dc="http://purl.org/dc/elements/1.1/"' . "\n";
$str .= 'xmlns:atom="http://www.w3.org/2005/Atom"' . "\n";
$str .= 'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"' . "\n";
$str .= 'xmlns:slash="http://purl.org/rss/1.0/modules/slash/"' . "\n";
$str .= '>' . "\n";
$str .= '<channel>' . "\n";
$str .= '<title>知乎日报</title>' . "\n";
$url = ($_SERVER['HTTPS'] == 'off' ? 'http://' : 'https://')  . $_SERVER['SERVER_NAME'] . ($_SERVER["SERVER_PORT"] == 80 ? '' : ':' . $_SERVER["SERVER_PORT"]) . $_SERVER["REQUEST_URI"]; 
$path = dirname($url);
$str .= '<atom:link href="' . $url . '" rel="self" type="application/rss+xml" />' . "\n";
$str .= '<link>http://daily.zhihu.com/</link>' . "\n";
$str .= '<description>知乎日报</description>' . "\n";
$now = date(DATE_RSS);
$str .= '<lastBuildDate>'. $now . '</lastBuildDate>' . "\n";
$str .= '<language>zh-CN</language>' . "\n";
$str .= '<sy:updatePeriod>hourly</sy:updatePeriod>' . "\n";
$str .= '<sy:updateFrequency>1</sy:updateFrequency>' . "\n";
$str .= '<generator>http://malash.me/</generator>' . "\n";

$day = 0;
while($items_fetched < $MAX_ITEMS && $day < $MAX_DAYS) {
    $str .= fetch_day($day);
    $day++;
}

$str .= '</channel>' . "\n";
$str .= '</rss>' . "\n";

$file = fopen("data.xml", "w");
fwrite($file, $str);
fclose($file);
?>