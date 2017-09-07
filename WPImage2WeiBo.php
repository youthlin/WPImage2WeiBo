<?php
/*
Plugin Name: WPImage2WeiBo
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
define(LIN_WB_USERNAME, 'lin_weibo_username');
define(LIN_WB_PASSWORD, 'lin_weibo_password');
define(LIN_WB_COOKIE, 'lin_weibo_cookie');

require_once(LinWPWeiBoPic_PLUGIN_DIR . '/exception/WeiBoException.php');
require_once(LinWPWeiBoPic_PLUGIN_DIR . '/service/WeiBoUploader.php');
require_once(LinWPWeiBoPic_PLUGIN_DIR . '/view/settings.php');
require_once(LinWPWeiBoPic_PLUGIN_DIR . '/task/processor.php');

register_activation_hook(__FILE__, 'lin_weibo_pic_on_activation');
register_deactivation_hook(__FILE__, 'lin_weibo_pic_on_deactivation');
register_uninstall_hook(__FILE__, 'lin_weibo_pic_on_uninstall');

function lin_weibo_pic_on_activation()
{
    global $wpdb;
    $table_name = LIN_WB_TABLE_NAME;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `post_id` bigint(20) unsigned NOT NULL DEFAULT 0,
            `src` VARCHAR(255) NOT NULL DEFAULT '',
            `pid` VARCHAR (50) NOT NULL DEFAULT '',
            `create_time` timestamp NOT NULL DEFAULT NOW(),
            PRIMARY KEY (`id`),
            UNIQUE KEY uniq_post_id_src(`post_id`,`src`)
           )$charset_collate";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

function lin_weibo_pic_on_deactivation()
{

}

function lin_weibo_pic_on_uninstall()
{

}