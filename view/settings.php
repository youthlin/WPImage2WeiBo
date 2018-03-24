<?php
// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    header("HTTP/1.1 404 Not Found");
    header("Status: 404 Not Found");
    exit;
}
// 账号设置
function wp_image_to_weibo_settings()
{
    if (!is_admin()) {
        return;
    }
    global $wb_uploader;
    $wb_pic_url = '';
    $wb_pic_url2 = '';
    ?>
    <div class="wrap">
        <h2><?php _e('WeiBo Account Settings', 'wp-image-to-weibo'); ?></h2>
        <?php
        if ($_POST['update_options'] == 'true') {//若提交了表单，则保存变量
            update_option(LIN_WB_USERNAME, $_POST['username']);
            update_option(LIN_WB_PASSWORD, $_POST['password']);
            update_option(LIN_WB_TYPE, $_POST['type']);
            echo '<div id="message" class="updated below-h2"><p>' . __('Saved', 'wp-image-to-weibo') . '</p></div>';
            $wb_uploader = \Lin\WeiBoUploader::newInstance(get_option(LIN_WB_USERNAME), get_option(LIN_WB_PASSWORD));
            update_option(LIN_WB_COOKIE, $wb_uploader->getCookie());
        }
        $name = get_option(LIN_WB_USERNAME);
        $pass = get_option(LIN_WB_PASSWORD);
        $cook = get_option(LIN_WB_COOKIE);
        $wb_uploader = \Lin\WeiBoUploader::newInstance($name, $pass, $cook);
        if ($_POST['url']) {
            $url = $_POST['url'];
            if ($wb_uploader == null) {
                echo '<div class="error below-h2"><p>' . __('Please set your username and password first.', 'wp-image-to-weibo') . '</p></div>';
            } else {
                try {
                    $pid = $wb_uploader->upload($url, false);
                    $wb_pic_url = $wb_uploader->getImageUrl($pid);
                } catch (\Lin\WeiBoException $e) {
                    printf('<div  class="error below-h2"><p>' . __('Error: %1$s', 'wp-image-to-weibo') . '</p></div>', $e->getMessage());
                }
            }
        }
        if ($_FILES['file']['name']) {
            if ($_FILES["file"]["error"] > 0) {
                printf('<div class="error below-h2"><p>' . __('Error: %1$s', 'wp-image-to-weibo') . '</p></div>', $_FILES["file"]["error"]);
            } else {
                printf('<div class="notice below-h2"><p>'
                    . __('Uploaded : %1$s', 'wp-image-to-weibo') . "<br />"
                    . __('Type     : %2$s', 'wp-image-to-weibo') . "<br />"
                    . __('Size     : %3$s KB', 'wp-image-to-weibo') . "<br />"
                    . __('Stored in: %4$s', 'wp-image-to-weibo')
                    . '</p></div>',
                    $_FILES["file"]["name"],
                    $_FILES["file"]["type"],
                    $_FILES["file"]["size"] / 1024,
                    $_FILES["file"]["tmp_name"]
                );
            }
            if ($wb_uploader == null) {
                echo '<div class="error below-h2"><p>' . __('Please set your username and password first.', 'wp-image-to-weibo') . '</p></div>';
            } else {
                $file = $_FILES['file']['tmp_name'];
                try {
                    $pid = $wb_uploader->upload($file);
                    $wb_pic_url2 = $wb_uploader->getImageUrl($pid);
                } catch (\Lin\WeiBoException $e) {
                    printf('<div  class="error below-h2"><p>' . __('Error: %1$s', 'wp-image-to-weibo') . '</p></div>', $e->getMessage());
                }
            }
        }
        //下面开始界面表单
        ?>
        <form method="POST" action="">
            <input type="hidden" name="update_options" value="true"/>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="username"><?php _e('Username', 'wp-image-to-weibo'); ?></label></th>
                    <td><input name="username" type="text" id="username" class="regular-text"
                               value="<?php echo get_option(LIN_WB_USERNAME) ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="password"><?php _e('Password', 'wp-image-to-weibo'); ?></label></th>
                    <td><input name="password" type="password" id="password" class="regular-text"
                               value="<?php echo get_option(LIN_WB_PASSWORD) ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label><?php _e('Mode', 'wp-image-to-weibo'); ?></label></th>
                    <td>
                        <label><input type="radio" id="type1" name="type"
                                      <?php if (get_option(LIN_WB_TYPE) == LIN_WB_TYPE_NORMAL) {
                                          echo ' checked ';
                                      } ?>value="<?php echo LIN_WB_TYPE_NORMAL; ?>">
                            <?php _e('Normal (not modify url on database, real-time query.)', 'wp-image-to-weibo'); ?>
                        </label>
                        <p></p>
                        <label><input type="radio" id="type2" name="type"
                                <?php if (get_option(LIN_WB_TYPE) == LIN_WB_TYPE_MODIFY) {
                                    echo ' checked ';
                                } ?> value="<?php echo LIN_WB_TYPE_MODIFY; ?>">
                            <?php _e('Modify (modify url when update post, suitable when picture are much.)', 'wp-image-to-weibo'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            <p><input type="submit" class="button-primary" name="admin_options"
                      value="<?php _e('Update', 'wp-image-to-weibo'); ?>"/></p>
        </form>
        <?php if (get_option(LIN_WB_TYPE) == LIN_WB_TYPE_MODIFY) { ?>
            <hr>
            <h2><?php _e('Reset all the WeiBo url to original.', 'wp-image-to-weibo'); ?></h2>
            <form action="">
                <input type="submit" class="button" name="reset_url"
                       value="<?php _e('Reset URL', 'wp-image-to-weibo'); ?>">
            </form>
        <?php } ?>
        <hr>
        <h2><?php _e('Test upload to WeiBo', 'wp-image-to-weibo'); ?></h2>
        <form action="" method="POST" enctype="multipart/form-data">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="url"><?php _e('Image Url', 'wp-image-to-weibo'); ?></label></th>
                    <td><input name="url" type="text" id="url" class="regular-text"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="file"><?php _e('Image File', 'wp-image-to-weibo'); ?></label></th>
                    <td><input name="file" type="file" id="file" class="regular-text"></td>
                </tr>
            </table>
            <p><input type="submit" class="button-primary" name="admin_options"
                      value="<?php _e('Upload', 'wp-image-to-weibo'); ?>"/></p>
        </form>

        <?php if ($wb_pic_url) {
            echo '<p>' . $wb_pic_url . '</p><br>' . '<img src="' . $wb_pic_url . '" style="max-width:100%;;">';
        }
        if ($wb_pic_url2) {
            echo '<p>' . $wb_pic_url2 . '</p><br>' . '<img src="' . $wb_pic_url2 . '" style="max-width:100%;;">';
        }
        ?>
        <div class="donate">
            <div class="qr-code hidden" id="donate-qr">
                <img src="https://ws4.sinaimg.cn/mw690/be3d86b9gy1fl6bs4w8e9j20w00w040z.jpg" alt="微信赞赏">
                <img src="https://ws2.sinaimg.cn/mw690/be3d86b9gy1fl6c7p8s9sj20tz14qmzp.jpg" alt="微信捐助">
                <img src="https://ws4.sinaimg.cn/mw690/be3d86b9gy1fl6bubl84uj20go0p040l.jpg" alt="支付宝捐助">
            </div>
            <button class="button-primary" id="donate-btn">赞助开发者</button>
        </div>
    </div>
    <style>#donate-qr img {
            width: 30%;
            min-width: 300px;
        }</style>
    <script>
        jQuery(document).ready(function ($) {
            var $donate = $('#donate-qr');
            $('#donate-btn').click(function () {
                $donate.slideToggle();
            });
        });
    </script>
    <?php
}//wp_image_to_weibo_settings()

add_action('admin_menu', 'add_page');
function add_page()
{
    add_options_page(__('WPImage2WeiBo Settings', 'wp-image-to-weibo'), __('WPImage2WeiBo', 'wp-image-to-weibo'),
        'administrator', 'WPImage2WeiBo', 'wp_image_to_weibo_settings');
}
