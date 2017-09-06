<?php
/*
Plugin Name: WPWeibo Pic
Plugin URI: https://youthlin.chen
Description: WeiBo Picture Bed for WordPress.
Version: 1.0
Author: youthlin.chen
Author URI: https://youthlin.chen
License: A "Slug" license name e.g. GPL2
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('LinWPWeiBoPic_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once(LinWPWeiBoPic_PLUGIN_DIR . 'class.WeiBoPic.php');
require_once(LinWPWeiBoPic_PLUGIN_DIR . 'view.settings.php');
require_once(LinWPWeiBoPic_PLUGIN_DIR . 'task.content.processor.php');

