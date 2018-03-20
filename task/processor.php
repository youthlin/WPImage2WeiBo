<?php
/**
 * Created by PhpStorm.
 * User: youthlin.chen
 * Date: 2017/9/7
 * Time: 22:00
 */
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    exit;
}

add_filter('the_content', 'wp_image_to_weibo_content_img_replace');
add_filter('post_thumbnail_html','wp_image_to_weibo_content_img_replace');
global $wb_uploader, $processed;
$wb_uploader = \Lin\WeiBoUploader::newInstance(get_option(LIN_WB_USERNAME), get_option(LIN_WB_PASSWORD), get_option(LIN_WB_COOKIE));
$processed = array();   //cache same image
// 处理文章中的图片链接，替换为微博外链
function wp_image_to_weibo_content_img_replace($content)
{
    global $wb_uploader;
    if ($wb_uploader == null) {
        $content .= '<!--' . __('Please set your username and password of WeiBo first.', 'wp-image-to-weibo') . '-->';
        return $content;
    }
    $before = get_num_queries();
    $pattern = '#(https?:)?//([^\s]*?).(\.jpg|\.jpeg|\.png|\.gif|\.bmp)#i';
    $content = preg_replace_callback($pattern, 'wp_image_to_weibo_match_callback', $content);
    return $content . "<!-- [WPImage2WeiBo queries: " . (get_num_queries() - $before) . '] -->';
}

function wp_image_to_weibo_match_callback($matches)
{
    // echo $matches[0] . PHP_EOL;
    $url = $matches[0];
    if (!$matches[1]) {
        $url = $_SERVER["REQUEST_SCHEME"] . ':' . $url;
    }
    // return $matches[0];
    return wp_image_to_weibo_img_replace($url);
}

function wp_image_to_weibo_img_replace($url)
{
    global $wb_uploader, $wpdb, $post, $processed;
    if ($processed[$url]) { //hit cache
        return $processed[$url];
    }
    if (strpos($url, ".sinaimg.cn/") > 0) {
        return $url;
    }

    $table_name = LIN_WB_TABLE_NAME;
    //检查数据库是否有
    $data = $wpdb->get_results($wpdb->prepare("SELECT pid FROM $table_name WHERE src = %s", $url));
    $link = $pid = $url;
    if (!$data || count($data) == 0) { //如果没有则上传
        $file = $url;
        $home_path = home_url('/');
        $multipart = false;// whether is local file or not
        if (0 === strpos($url, $home_path)) {
            $multipart = true;
            $file = ABSPATH . substr($file, strlen($home_path));
        }
        try {
            $pid = $wb_uploader->upload($file, $multipart);
            $link = $wb_uploader->getImageUrl($pid);
            $in = array(
                'post_id' => $post->ID,
                'src' => $url,
                'pid' => $pid,
            );
            $wpdb->insert($table_name, $in);
        } catch (\Lin\WeiBoException $e) {
            echo "<!--ERROR[{$e->getMessage()}][$url]-->" . PHP_EOL;
        }
    } else {
        $pid = $data[0]->pid;
        $link = $wb_uploader->getImageUrl($pid);
    }
    $processed[$url] = $link;
    return $link;
}
