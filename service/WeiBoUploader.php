<?php
/**
 * Created by PhpStorm.
 * User: youthlin.chen
 * Date: 2017/9/7
 * Time: 21:07
 */

namespace Lin;

use CURLFile;

class WeiBoUploader
{
    const LOGIN_URL = "https://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.15)";
    private $username;
    private $password;
    private $cookie;

    public static function newInstance($username, $password, $cookie = null)
    {
        if (!$username || !$password) {
            return null;
        }
        return new WeiBoUploader($username, $password, $cookie);
    }

    private function __construct($username, $password, $cookie)
    {
        $this->username = $username;
        $this->password = $password;
        $this->cookie = $cookie;
    }

    /**
     * Upload a image to weibo.
     * @author mengkun  http://mkblog.cn/854
     * @param string $file path to image, or url of image
     * @param bool $multipart if true, $file is path to a image at server
     * @return null if fail, or return pid(string) if success.
     * @throws WeiBoException if login fail
     */
    public function upload($file, $multipart = true)
    {
        $pid = $this->upload0($file, $multipart);
        if ($pid == null) { // if cookie expire, try again.
            $this->resetCookies();
            $pid = $this->upload0($file, $multipart);
        }
        return $pid;
    }

    /**
     * Get image link from pid
     * @see https://github.com/consatan/weibo_image_uploader
     * @param string $pid 微博图床 pid，或者微博图床链接。传递的是链接的话，仅是将链接的尺寸更改为目标尺寸而已。
     * @param int $size 图片尺寸
     * <pre>
     * array(
     * 'large',         //original
     * 'mw1024',        //max width 1024px
     * 'mw690',         //max width 690px
     * 'bmiddle',       //max width 440px
     * 'small',         //the larger edge is 200px
     * 'thumb180',      //180px*180px
     * 'thumbnail',     //the larger edge is 120px
     * 'square'         //80*80
     * );
     * </pre>
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
        if (is_numeric($size)) {
            $size = $sizeArr[$size];
        }
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

    private function upload0($file, $multipart = true)
    {
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
            $img = @file_get_contents($file);
            if (!$img) {
                throw new WeiBoException('Invalid Url');
            }
            $post['b64_data'] = base64_encode($img);
        }
        $cookie = $this->cookie;
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
        if (!isset($match[1])) {
            return null;
        } else {
            $result = $match[1];
            $upload_json = json_decode($result, true);
            $code = $upload_json['data']['pics']['pic_1']['ret'];
            if ($code != 1) {
                return null;
            }
            $pid = $upload_json['data']['pics']['pic_1']['pid'];
            return $pid;
        }
    }

    // success - true
    // fail    - WeiBoException: reason
    private function resetCookies()
    {
        $cookie_file = sys_get_temp_dir() . 'weibo.login.cookie';
        $login_json = $this->login($cookie_file);
        if ($login_json->retcode == '0') {
            $cross_domain_url = $login_json->crossDomainUrlList[0];
            $cookie_arr = $this->fetchCookie($cross_domain_url, $cookie_file);
            $this->cookie = $this->cookie2str($cookie_arr);
        } else {
            throw new WeiBoException($login_json->reason);
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
        curl_setopt($ch, CURLOPT_URL, WeiBoUploader::LOGIN_URL);
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

    private function fetchCookie($cross_domain_url, $cookie_file)
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

    //region getters
    public function getUsername()
    {
        return $this->username;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getCookie()
    {
        return $this->cookie;
    }
    //endregion

}
