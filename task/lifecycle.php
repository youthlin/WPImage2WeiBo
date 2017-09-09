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

add_action('plugins_loaded', 'wp_image_to_weibo_load_plugin_textdomain');
function wp_image_to_weibo_load_plugin_textdomain()
{
    load_plugin_textdomain('wp-image-to-weibo', FALSE, basename(dirname(LIN_WB_MAIN_FILE)) . '/languages/');
}

register_activation_hook(LIN_WB_MAIN_FILE, 'wp_image_to_weibo_on_activation');
register_deactivation_hook(LIN_WB_MAIN_FILE, 'wp_image_to_weibo_on_deactivation');
register_uninstall_hook(LIN_WB_MAIN_FILE, 'wp_image_to_weibo_on_uninstall');
function wp_image_to_weibo_on_activation()
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

function wp_image_to_weibo_on_deactivation()
{

}

function wp_image_to_weibo_on_uninstall()
{

}
