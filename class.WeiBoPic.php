<?php
/**
 * 微博类 含登录获取 cookie, 上传图片功能
 * 从设置里取出 username, password, cookie(可为空) 构造实例，
 * 调用 upload (cookie 过期会自动尝试重新登录)获取 pid,
 * 调用 getImageUrl 获取外链
 * Created by PhpStorm.
 * User: youthlin.chen
 * Date: 2017/9/4
 * Time: 22:51
 */

namespace Lin;

use CURLFile;

class WeiBoPic
{
    const LOGIN_URL = "https://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.15)";
    private $username;
    private $password;
    private $cookie_txt;
    private $cookie_str;
    private $cookie_arr;
    private $error;

    public static function new_instance($username, $password, $cookie = null)
    {
        if (!$username || !$password) {
            return null;
        }
        return new WeiBoPic($username, $password, $cookie);
    }

    private function __construct($username, $password, $cookie = null)
    {
        $this->username = $username;
        $this->password = $password;
        $this->cookie_str = $cookie;
    }

    public function get_error()
    {
        return $this->error;
    }

    public function get_cookie()
    {
        if (!$this->cookie_str) {
            $this->resetCookies();
        }
        return $this->cookie_str;
    }

    public function resetCookies()
    {
        $this->cookie_str = null;

        $this->cookie_txt = sys_get_temp_dir() . '/weibo.login.cookie';
        $login_json = $this->login($this->cookie_txt);
        if ($login_json->retcode == '0') {
            $cross_domain_url = $login_json->crossDomainUrlList[0];
            $this->cookie_arr = $this->getCookie($cross_domain_url, $this->cookie_txt);
            $this->cookie_str = $this->cookie2str($this->cookie_arr);
        } else {
            $this->error = '登录失败: ' . $login_json->reason;
        }
    }

    private function login($cookie_file)
    {
        $username = base64_encode($this->username);
        $password = $this->password;
        $post_data = [
            'entry' => 'sso',
            'gateway' => '1',
            'from' => 'null',
            'savestate' => '30',
            'userticket' => '0',
            'pagerefer' => '',
            'vsnf' => '1',
            'su' => $username,
            'service' => 'sso',
            'sp' => $password,
            'sr' => '1440*900',
            'encoding' => 'UTF-8',
            'cdult' => '3',
            'domain' => 'sina.com.cn',
            'prelt' => '0',
            'returntype' => 'TEXT',
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, WeiBoPic::LOGIN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9/youthlin.com");
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $result1 = curl_exec($ch);
        curl_close($ch);
        return json_decode($result1);
    }

    private function getCookie($cross_domain_url, $cookie_file)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $cross_domain_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.9) Gecko/20071025 Firefox/2.0.0.9/youthlin.com");
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
        $result = curl_exec($ch);
        // get cookie
        // multi-cookie variant contributed by @Combuster in comments
        preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
        $cookies = array();
        foreach ($matches[1] as $item) {
            parse_str($item, $cookie);
            $cookies = array_merge($cookies, $cookie);
        }
        return $cookies;
    }

    private function cookie2str($cookie_arr = array())
    {
        $ret = "";
        foreach ($cookie_arr as $k => $v) {
            $ret .= $k . '=' . $v . '; ';
        }
        return $ret;
    }

    /**
     * 上传图片到微博图床
     * @author mengkun  http://mkblog.cn/854
     * @param string $file 图片文件path / 图片 url
     * @param bool $multipart 是否采用 multipart 方式上传. 为 true 时只能是服务器本地的文件, file 此时应该是路径
     * @param int $try_count cookie 失效则重试，用于递归，调用者可忽略
     * @return null if fail, or string: pid if success.
     */
    public function upload($file, $multipart = true, $try_count = 0)
    {
        $this->error = null;
        $url = 'http://picupload.service.weibo.com/interface/pic_upload.php'
            . '?mime=image%2Fjpeg&data=base64&url=0&markpos=1&logo=&nick=0&marks=1&app=miniblog';
        if ($multipart) {
            $url .= '&cb=http://weibo.com/aj/static/upimgback.html?_wv=5&callback=STK_ijax_' . time();
            if (class_exists('CURLFile')) {     // php 5.5
                $post['pic1'] = new CURLFile(realpath($file));
            } else {
                $post['pic1'] = '@' . realpath($file);
            }
        } else {
            $post['b64_data'] = base64_encode(file_get_contents($file));
        }
        $cookie = $this->cookie_str;
        // Curl 提交
        $ch = curl_init($url);
        curl_setopt_array($ch, array(
            CURLOPT_POST => true,
            CURLOPT_VERBOSE => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array("Cookie: $cookie"),
            CURLOPT_POSTFIELDS => $post,
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        // 正则表达式提取返回结果中的 json 数据
        preg_match('/({.*)/i', $output, $match);
        $result = "";
        $cookie_fail = false;
        if (!isset($match[1])) {
            $cookie_fail = true;
        } else {
            $result = $match[1];
        }
        $upload_json = null;
        if (!$cookie_fail) {
            $upload_json = json_decode($result, true);
            $code = $upload_json['data']['pics']['pic_1']['ret'];
            if ($code != 1) {
                $cookie_fail = true;
            }
        }
        if ($cookie_fail && $try_count < 2) {
            $try_count++;
            $this->resetCookies();
            return $this->upload($file, $multipart, $try_count);
        }
        if ($cookie_fail) {
            $this->error = __('Invalidate cookie/url/file');
            return null;
        }
        return $pid = $upload_json['data']['pics']['pic_1']['pid'];
    }

    /**
     * 获取图片链接 (本函数修改自 https://github.com/consatan/weibo_image_uploader)
     *
     * @param string $pid 微博图床 pid，或者微博图床链接。传递的是链接的话，
     *     仅是将链接的尺寸更改为目标尺寸而已。
     * @param int $size 图片尺寸
     * @param bool $https (true) 是否使用 https 协议
     * @return string 图片链接
     * 当 $pid 既不是 pid 也不是合法的微博图床链接时返回空值
     */
    public function getImageUrl($pid, $size = 0, $https = true)
    {
        if (!$pid) {
            return '';
        }
        $sizeArr = array('large', 'mw1024', 'mw690', 'bmiddle', 'small', 'thumb180', 'thumbnail', 'square');
        $pid = trim($pid);
        $size = $sizeArr[$size];
        // 传递 pid
        if (preg_match('/^[a-zA-Z0-9]{32}$/', $pid) === 1) {
            return ($https ? 'https://ws' : 'http://ww') . ((crc32($pid) & 3) + 1) . ".sinaimg.cn/" . $size . "/$pid." . ($pid[21] === 'g' ? 'gif' : 'jpg');
        }
        // 传递 url
        $url = $pid;
        $imgUrl = preg_replace_callback(
            '/^(https?:\/\/[a-z]{2}\d\.sinaimg\.cn\/)'
            . '(large|bmiddle|mw1024|mw690|small|square|thumb180|thumbnail)'
            . '(\/[a-z0-9]{32}\.(jpg|gif))$/i',
            function ($match) use ($size) {
                return $match[1] . $size . $match[3];
            },
            $url,
            -1,
            $count
        );
        if ($count === 0) {
            return '';
        }
        return $imgUrl;
    }
}
