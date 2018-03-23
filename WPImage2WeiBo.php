<?php
/*
Plugin Name: WPImage2WeiBo
Plugin URI: https://youthlin.com
Description: 提取文章图片链接并上传至微博图床，使用微博外链替换图片链接
Version: 1.0
Author: youthlin.chen
Author URI: https://youthlin.com
Text Domain: wp-image-to-weibo
License: GPLv2 or later.
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    exit;
}

global $wpdb;
define('LIN_WB_TB_PREFIX', 'weibo_');
define('LIN_WB_TABLE_NAME', $wpdb->prefix . LIN_WB_TB_PREFIX . 'image');
define('LIN_WB_USERNAME', 'lin_weibo_username');
define('LIN_WB_PASSWORD', 'lin_weibo_password');
define('LIN_WB_COOKIE', 'lin_weibo_cookie');
define('LIN_WB_TYPE', 'lin_weibo_type');
define('LIN_WB_TYPE_NORMAL', 'normal');
define('LIN_WB_TYPE_MODIFY', 'modify');

define('LIN_WB_MAIN_FILE', __FILE__);
define('LIN_WB_DIR', plugin_dir_path(__FILE__));
require_once(LIN_WB_DIR . 'task/lifecycle.php');
require_once(LIN_WB_DIR . 'exception/WeiBoException.php');
require_once(LIN_WB_DIR . 'service/WeiBoUploader.php');
require_once(LIN_WB_DIR . 'view/settings.php');
require_once(LIN_WB_DIR . 'task/processor.php');
