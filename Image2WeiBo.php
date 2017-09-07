<?php
/*
Plugin Name: 微博图床
Plugin URI: https://youthlin.chen
Description: 提取文章图片链接并上传至微博图床，使用微博外链替换图片链接
Version: 1.0
Author: youthlin.chen
Author URI: https://youthlin.chen
License: GPL2
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    exit;
}

global $wpdb;
define(LIN_WB_TB_PREFIX, 'weibo_');
define(LIN_WB_TABLE_NAME, $wpdb->prefix . LIN_WB_TB_PREFIX . 'image');
define('LinWPWeiBoPic_PLUGIN_DIR', plugin_dir_path(__FILE__));

require_once(LinWPWeiBoPic_PLUGIN_DIR . '/task/lifecycle.php');
require_once(LinWPWeiBoPic_PLUGIN_DIR . '/exception/WeiBoException.php');
require_once(LinWPWeiBoPic_PLUGIN_DIR . '/service/WeiBoUploader.php');
require_once(LinWPWeiBoPic_PLUGIN_DIR . '/view/settings.php');
require_once(LinWPWeiBoPic_PLUGIN_DIR . '/task/processor.php');

register_activation_hook(__FILE__, 'lin_weibo_pic_on_activation');
register_deactivation_hook(__FILE__, 'lin_weibo_pic_on_deactivation');
register_uninstall_hook(__FILE__, 'lin_weibo_pic_on_uninstall');

