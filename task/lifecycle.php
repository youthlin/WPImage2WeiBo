<?php
/**
 * Created by IntelliJ IDEA.
 * User: lin
 * Date: 17-9-8
 * Time: 下午2:52
 */
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    exit;
}

register_activation_hook(LIN_WB_MAIN_FILE, 'lin_weibo_pic_on_activation');
register_deactivation_hook(LIN_WB_MAIN_FILE, 'lin_weibo_pic_on_deactivation');
register_uninstall_hook(LIN_WB_MAIN_FILE, 'lin_weibo_pic_on_uninstall');

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
