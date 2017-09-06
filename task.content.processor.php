<?php
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
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
    $wb_uploader = \Lin\WeiBoPic::new_instance($name, $pass, $cook);
    if ($wb_uploader) {
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
//    $urls = array();
//    $index = 0;
//    foreach ($matches[2] as $url) {
//        if ($matches[3][$index] == "") {
//            $url = $_SERVER["REQUEST_SCHEME"] . ':' . $url;
//        }
//        //echo "[$index]$url<br>";
//        array_push($urls, $url);
//        $index++;
//    }
    $content = preg_replace_callback($pattern, function ($matches) {
        $url = $matches[2];
        if (!$matches[3]) {
            $url = $_SERVER["REQUEST_SCHEME"] . ':' . $url;
        }

        return $matches[1] . lin_weibo_pic_content_img_replace($url) . $matches[1] . $matches[6];
    }, $content);
    return $content;
}

function lin_weibo_img_replace($url)
{
    global $wb_uploader;
    //检查数据库是否有

    //如果没有则上传

    //替换

    return $url;
}

