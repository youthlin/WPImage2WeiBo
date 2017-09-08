=== WPImage2WeiBo ===
Contributors: YouthLin
Donate link: https://youthlin.com/
Tags: image, weibo, picture bed
Requires at least: 4.6
Tested up to: 4.8.1
Stable tag: 4.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Search image link automatically on post content and replace them of WeiBo link.

== Description ==

安装该插件后，将会在数据库中新建一张表，不过您不必关心这些细节，只需在设置里找到本插件设置页面，填入微博账号、密码信息即可。
保存后，可以在下方表单测试是否可以成功地上传图片到微博。
不出意外的话应该是可以的，然后就可以打开首页或文章，点开一篇含有图片的文章，观察页面源代码、或图片链接，看是否已经替换为微博外链。

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/WPImage2WeiBo` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. Save your WeiBo account info at settings page
1. Browser your post and see the `HTML Source` output

== Frequently Asked Questions ==

= 为什么需要微博账号和密码？ =

微博上传接口需要用户的 Cookies 信息，为了获取用户 Cookies 信息，插件需要用户名和密码进行自动登录。您的账号信息保存在 WordPress 设置中，
您需要自行保证您的数据库安全，若信息被盗，插件开发者将不承担任何责任。如果您不放心的话，可以注册一个新的账号使用。

= 图片传到哪里去了？ =

虽然图片是通过您的 Cookies 信息上传的，但图片将不会出现在您微博账号中，即上传图片后，既不会发布一条微博，也不会保存在您的微博相册中。
而仅仅是保存在微博服务器，我们可以通过返回的外链访问到图片。

== Screenshots ==

1. This screen shot description corresponds to screenshot-1.(png|jpg|jpeg|gif). Note that the screenshot is taken from
the /assets directory or the directory that contains the stable readme.txt (tags or trunk). Screenshots in the /assets
directory take precedence. For example, `/assets/screenshot-1.png` would win over `/tags/4.3/screenshot-1.png`
(or jpg, jpeg, gif).
2. This is the second screen shot

== Changelog ==

= 1.0 =
First Version.

== Upgrade Notice ==

= 1.0 =
Download the first version immediately.

== Acknowledgement ==

孟坤博客：[利用微博当图床 - php 语言实现](http://mkblog.cn/854/)
H1ac0k : [获取微博登录 Cookies 的几种方案](http://xrong.net/2016/10/19/%E8%8E%B7%E5%8F%96%E5%BE%AE%E5%8D%9A%E7%99%BB%E5%BD%95Cookies%E7%9A%84%E5%87%A0%E7%A7%8D%E6%96%B9%E6%A1%88/)
