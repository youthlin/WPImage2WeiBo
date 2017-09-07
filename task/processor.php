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

add_filter('the_content', 'lin_weibo_pic_content_img_replace');
// 处理文章中的图片链接，替换为微博外链
function lin_weibo_pic_content_img_replace($content)
{
    $name = get_option(LIN_WB_USERNAME);
    $pass = get_option(LIN_WB_PASSWORD);
    $cook = get_option(LIN_WB_COOKIE);
    global $wb_uploader;
    $wb_uploader = \Lin\WeiBoUploader::newInstance($name, $pass, $cook);
    if ($wb_uploader == null) {
        $content .= '<!--' . __('Please set your username and password of WeiBo first.', 'lin_weibo_pic') . '-->';
        return $content;
    }
    /*

      #                            用#而不是/ 可以不用对 / 转义
       ('|\")                    1 属性都是引号括起来的
       (                         2 整个URL 可能 // 开头
        (https?:)?               3 协议
        //                         双斜线
        (.*?)                    4 路径
        .                          后缀名
        (jpg|jpeg|png|gif|bmp)   5 后缀名
       )
       \1                          引号
       (\s|/|>)                  6 空白 或 标签结尾
      #i                           忽略大小写

     */
    $pattern = '#(\'|\")((https?:)?//(.*?).(jpg|jpeg|png|gif|bmp))\1(\s|/|>)#i';
    preg_match_all($pattern, $content, $matches);
    $content = preg_replace_callback($pattern, function ($matches) {
        $url = $matches[2];
        if (!$matches[3]) {
            $url = $_SERVER["REQUEST_SCHEME"] . ':' . $url;
        }
        return $matches[1] . lin_weibo_img_replace($url) . $matches[1] . $matches[6];
    }, $content);
    return $content;
}

function lin_weibo_img_replace($url)
{
    global $wb_uploader, $wpdb, $post;
    $table_name = LIN_WB_TABLE_NAME;
    //检查数据库是否有
    $data = $wpdb->get_results($wpdb->prepare("SELECT pid FROM $table_name WHERE post_id = %d AND src = %s", $post->ID, $url));
    $link = $pid = $url;
    if (!$data || count($data) == 0) {
        //如果没有则上传
        try {
            //todo local file use multipart
            $pid = $wb_uploader->upload($url, false);
            $link = $wb_uploader->getImageUrl($pid);
            $in = array(
                'post_id' => $post->ID,
                'src' => $url,
                'pid' => $pid,
                'create_time' => time()
            );
            $success = $wpdb->insert($table_name, $in);
            if ($success) {
                echo "<!--[$url][$pid]-->" . PHP_EOL;
            }
        } catch (\Lin\WeiBoException $e) {
            //var_dump($e);
            echo "<!--ERROR[{$e->getMessage()}][$url]-->" . PHP_EOL;
        }
    } else {
        $pid = $data[0]->pid;
        $link = $wb_uploader->getImageUrl($pid);
    }
    return $link;
}
