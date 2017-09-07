<?php
/**
 * Created by PhpStorm.
 * User: youthlin.chen
 * Date: 2017/9/7
 * Time: 22:04
 */
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    exit;
}

function lin_weibo_pic_on_activation()
{
    global $wpdb;
    $table_name = LIN_WB_TABLE_NAME;
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name(
            id BIGINT NOT NULL AUTO_INCREMENT,
            post_id BIGINT NOT NULL DEFAULT 0,
            src VARCHAR(255) NOT NULL DEFAULT '',
            pid VARCHAR (50) NOT NULL DEFAULT ''
            create_time DATETIME DEFAULT now(),
            PRIMARY KEY id,
            INDEX KEY idx_src(src)
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