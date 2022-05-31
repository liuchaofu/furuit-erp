<?php

namespace app\api\controller;

use app\common\controller\Api;
use app\common\exception\UploadException;
use app\common\library\Upload;
use app\common\model\Area;
use app\common\model\Version;
use fast\Random;
use think\Config;
use think\Db;
use think\Env;
use think\Hook;

/**
 * 公共接口
 */
class Common extends Api
{
    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    private $appId = "";
    private $appSecret = "";


    /*
     * 加载初始化
     *
     * @param string $version 版本号
     * @param string $lng     经度
     * @param string $lat     纬度
     */
    protected function init()
    {
        if ($version = $this->request->request('version')) {
            $lng = $this->request->request('lng');
            $lat = $this->request->request('lat');

            //配置信息
            $upload = Config::get('upload');
            //如果非服务端中转模式需要修改为中转
            if ($upload['storage'] != 'local' && isset($upload['uploadmode']) && $upload['uploadmode'] != 'server') {
                //临时修改上传模式为服务端中转
                set_addon_config($upload['storage'], ["uploadmode" => "server"], false);

                $upload = \app\common\model\Config::upload();
                // 上传信息配置后
                Hook::listen("upload_config_init", $upload);

                $upload = Config::set('upload', array_merge(Config::get('upload'), $upload));
            }

            $upload['cdnurl'] = $upload['cdnurl'] ? $upload['cdnurl'] : cdnurl('', true);
            $upload['uploadurl'] = preg_match("/^((?:[a-z]+:)?\/\/)(.*)/i", $upload['uploadurl']) ? $upload['uploadurl'] : url($upload['storage'] == 'local' ? '/api/common/upload' : $upload['uploadurl'], '', false, true);

            $content = [
                'citydata'    => Area::getCityFromLngLat($lng, $lat),
                'versiondata' => Version::check($version),
                'uploaddata'  => $upload,
                'coverdata'   => Config::get("cover"),
            ];
            $this->success('', $content);
        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /*
     * 上传文件
     * @ApiMethod (POST)
     * @param File $file 文件流
     */
    protected function upload()
    {
        Config::set('default_return_type', 'json');
        //必须设定cdnurl为空,否则cdnurl函数计算错误
        Config::set('upload.cdnurl', '');
        $chunkid = $this->request->post("chunkid");
        if ($chunkid) {
            if (!Config::get('upload.chunking')) {
                $this->error(__('Chunk file disabled'));
            }
            $action = $this->request->post("action");
            $chunkindex = $this->request->post("chunkindex/d");
            $chunkcount = $this->request->post("chunkcount/d");
            $filename = $this->request->post("filename");
            $method = $this->request->method(true);
            if ($action == 'merge') {
                $attachment = null;
                //合并分片文件
                try {
                    $upload = new Upload();
                    $attachment = $upload->merge($chunkid, $chunkcount, $filename);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
            } elseif ($method == 'clean') {
                //删除冗余的分片文件
                try {
                    $upload = new Upload();
                    $upload->clean($chunkid);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            } else {
                //上传分片文件
                //默认普通上传文件
                $file = $this->request->file('file');
                try {
                    $upload = new Upload($file);
                    $upload->chunk($chunkid, $chunkindex, $chunkcount);
                } catch (UploadException $e) {
                    $this->error($e->getMessage());
                }
                $this->success();
            }
        } else {
            $attachment = null;
            //默认普通上传文件
            $file = $this->request->file('file');
            try {
                $upload = new Upload($file);
                $attachment = $upload->upload();
            } catch (UploadException $e) {
                $this->error($e->getMessage());
            }

            $this->success(__('Uploaded successful'), ['url' => $attachment->url, 'fullurl' => cdnurl($attachment->url, true)]);
        }

    }



    /*
     * CURL请求
     * @param $url string 请求url地址
     * @param $primeval bool 是否返回原始数据，否则会json_decode
     * @param $method string 请求方法 get post
     * @param null $postfields post数据数组
     * @param array $headers 请求header信息
     * @param bool|false $debug 调试开启 默认false
     * @return mixed
     */
    protected function httpRequest($url, $primeval = false, $method = "GET", $postfields = null, $headers = array(), $debug = false)
    {
        $method = strtoupper($method);
        $ci = curl_init();
        /* Curl settings */
        curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
        curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        switch ($method) {
            case "POST":
                curl_setopt($ci, CURLOPT_POST, true);
                if (!empty($postfields)) {
                    $tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
                }
                break;
            default:
                curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
                break;
        }
        $ssl = preg_match('/^https:\/\//i', $url) ? TRUE : FALSE;
        curl_setopt($ci, CURLOPT_URL, $url);
        if ($ssl) {
            curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
            curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
        }
        //curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
        curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
        curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ci, CURLINFO_HEADER_OUT, true);
        /*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
        $response = curl_exec($ci);
        $requestinfo = curl_getinfo($ci);
        $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
        if ($debug) {
            echo "=====post data======\r\n";
            var_dump($postfields);
            echo "=====info===== \r\n";
            print_r($requestinfo);
            echo "=====response=====\r\n";
            print_r($response);
        }
        curl_close($ci);
        if ($primeval) {
            return $response;
        }
        $response = json_decode($response, true);//转数组
        return $response;
        //return array($http_code, $response,$requestinfo);
    }

    //把请求发送到微信服务器换取二维码
    protected function http($url, $data = '', $method = 'GET')
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
        if ($method == 'POST') {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data != '') {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
        }

        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }


    // 获取用户openid  session_key 如果关注了公众号则有unionid
    protected function getSessionkey($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . $this->appId . "&secret=" . $this->appSecret . "&js_code=" . $code . "&grant_type=authorization_code";
        $result = $this->httpRequest($url);

        return $result;
    }

    /*
     * 请求过程中因为编码原因+号变成了空格
     * 需要用下面的方法转换回来
     */
    protected  function define_str_replace($data)
    {
        return str_replace(' ', '+', $data);
    }


    //对象转数组
    protected function object_array($array)
    {
        if (is_object($array)) {
            $array = (array)$array;
        }
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $array[$key] = self::object_array($value);
            }
        }
        return $array;
    }


    /*
     * base64转图片存本地
     */
    protected function base64_image_content($base64_image_content, $path)
    {
        //匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];
            $new_file = $path . "/" . date('Ymd', time()) . "/";
            if (!file_exists($new_file)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700,true);
            }
            $new_file = $new_file . time() . ".{$type}";
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                return '/' . $new_file;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
     * 生成小程序分享二维码带参数
     */

    protected function send_post($url, $post_data, $method = 'POST')
    {
        $postdata = http_build_query($post_data);
        $options = array(
            'http' => array(
                'method' => $method, // or GET
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        return $result;
    }

    // 获取accesstoken
    protected function getAccesstoken()
    {
        //缓存access_token
        session_start();

        if (!isset($_SESSION['access_token']) || (isset($_SESSION['expires_in']) && time() > $_SESSION['expires_in'])) {

            $appId = Env::get('wx.appId');
            $appSecret = Env::get('wx.appSecret');
            //获取access_token
            $access_token = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;

            $json = $this->http($access_token);
            $json = json_decode($json, true);
            $_SESSION['access_token'] = $json['access_token'];
            $_SESSION['expires_in'] = time() + 7200;
            $token = $json["access_token"];
        } else {
            $token = $_SESSION["access_token"];
        }
//        halt($token);
        //2
//        $tokenUrl = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $srcret;
//        $getArr = array();
//        $tokenArr = json_decode($this->send_post($tokenUrl, $getArr, "GET"));
//        $access_token = $tokenArr->access_token;

        return $token;
    }

    protected function api_notice_increment($url, $data)
    {
        $ch = curl_init();
        $header = array("Content-type: application/json;charset=UTF-8", "Accept: application/json", "Cache-Control: no-cache", "Pragma: no-cache");

//        $header = array(
//            'Accept-Language:zh-CN',
//            'x-appkey:114816004000028',
//            'x-apsignature:933931F9124593865313864503D477035C0F6A0C551804320036A2A1C5DF38297C9A4D30BB1714EC53214BD92112FB31B4A6FAB466EEF245710CC83D840D410A7592D262B09D0A5D0FE3A2295A81F32D4C75EBD65FA846004A42248B096EDE2FEE84EDEBEBEC321C237D99483AB51235FCB900AD501C07A9CAD2F415C36DED82',
//            'x-apversion:1.0',
//            'Content-Type:application/x-www-form-urlencoded',
//            'Accept-Charset: utf-8',
//            'Accept:application/json',
//            'X-APFormat:json'
//        );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_exec($ch);
//         var_dump($tmpInfo);
        if (curl_errno($ch)) {
            return false;
        } else {
            // var_dump($tmpInfo);
            return $tmpInfo;
        }
    }


    /* 上面生成的是数量限制10万的二维码，下面重写数量不限制的码 */
    /* getWXACodeUnlimit */
    /* 码一，圆形的小程序二维码，数量限制一分钟五千条 */
    /*
     * 45009 调用分钟频率受限(目前5000次/分钟，会调整)，如需大量小程序码，建议预生成。
     * 41030 所传page页面不存在，或者小程序没有发布
     */
    protected function mpcode($page, $cardid)
    {
        // 参数
        // $postdata['scene']="nidaodaodao";
        $postdata['scene'] = "id={$cardid}";//"id/{$cardid}"
        //$param = json_encode(array("scene"=>"id={$cardid}","page"=>"pages/index/index","check_path"=>false,"env_version"=>"release","width"=> 150,"is_hyaline"=>true));
        // 宽度
        $postdata['width'] = 430;
        // 页面
        $postdata['page'] = $page;
        // $postdata['page']="pages/postcard/postcard";a
        // 线条颜色
        $postdata['auto_color'] = false;
        // auto_color 为 false 时生效
        $postdata['line_color'] = [
            'r' => '0',
            'g' => '0',
            'b' => '0'
        ];

        // 是否有底色为true时是透明的
        $postdata['is_hyaline'] = true;

        $post_data = json_encode($postdata);
        //拿access_token
        $access_token = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : $this->getAccesstoken();

        $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . $access_token;
        $result = $this->api_notice_increment($url, $post_data);

        $data = 'data:image/png;base64,' . base64_encode($result);
//        echo '<img src="data:'.$data.'">';
        return $data;

    }

    /* 码二，正方形的二维码，数量限制调用十万条 */
    protected function qrcodes($cardid)
    {
//        $path = "http://www.shikexu.com/archives/774";
        $path = "pages/index/index";
        $postdata['scene'] = "id={$cardid}";
        // 宽度
        $postdata['width'] = 300;
        // 页面
        $postdata['path'] = $path;
        $post_data = json_encode($postdata);

        $access_token = isset($_SESSION['access_token']) ? $_SESSION['access_token'] : $this->getAccesstoken();
        $url = "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=" . $access_token;
        $result = $this->api_notice_increment($url, $post_data);

        $data = 'data:image/png;base64,' . base64_encode($result);

        echo '<img src="data:' . $data . '">';

        return $data;
    }


    //str_filter 过滤字符串，防止sql注入
    protected function str_filter($str)
    {
        $str = trim($str);
        if (!get_magic_quotes_gpc()) {
            $str = addslashes($str);
        }
        $str = htmlspecialchars($str);
        return $str;
    }



    /**
     * 1小写，2小写+数字，3大写+数字，4小写+大写+数字，5大写
     * @param unknown $flag
     * @param unknown $length
     * @return string
     */
    protected function createCode($flag,$length)
    {
        // 生成随机字符串
        $result = '';
        switch ($flag){
            case 1:
                $str = 'qwertyuiopasdfghjklzxcvbnm';
                $maxlength = 25;
                break;
            case 2:
                $str = '0123456789qwertyuiopasdfghjklzxcvbnm';
                $maxlength = 35;
                break;
            case 3:
                $str = '0123456789QWERTYUIOPASDFGHJKLZXCVBNM';
                $maxlength = 35;
                break;
            case 4:
                $str = '0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
                $maxlength = 61;
                break;
            case 5:
                $str = 'QWERTYUIOPASDFGHJKLZXCVBNM';
                $maxlength = 25;
                break;
            default:
                $str = 'qwertyuiopasdfghjklzxcvbnm';
                break;
        }

        for ($i = 0; $i < $length; $i++) {
            $result .= $str[rand(0, $maxlength)];
        }
        return $result;
    }

    /**
     * 创建优惠券码
     */
    protected function couponCode(){

        $code = $this->createCode(2, 10);
        $code = "SG-".$code;
        $coupondTable = Db::name("coupond");
        $coupondInfo = $coupondTable
            ->where("code","=",$code)
            ->find();
        if($coupondInfo){
            return $this->couponCode();
        }else{
            return $code;
        }
    }

    /**
     * 按符号截取字符串的指定部分
     * @param string $str 需要截取的字符串
     * @param string $sign 需要截取的符号
     * @param int $number 如是正数以0为起点从左向右截 负数则从右向左截
     * @return string 返回截取的内容
     */
    protected function cut_str($str,$sign,$number){
        $array=explode($sign, $str);
        $length=count($array);
        if($number<0){
            $new_array=array_reverse($array);
            $abs_number=abs($number);
            if($abs_number>$length){
                return 'error';
            }else{
                return $new_array[$abs_number-1];
            }
        }else{
            if($number>=$length){
                return 'error';
            }else{
                return $array[$number];
            }
        }
    }

    /**
     * 转换数组里面的时间戳
     * @param array $array 需要转换的二维数组
     * @param array $filed 需要转换的字段F
     * @param string $formate
     * @return string
     */
    protected function dateformate($array,$filed,$formate="Y-m-d H:i:s"){
        foreach ($array as $key=>$val){
            foreach ($filed as $key1=>$val1){
                if($val[$val1]<1000){
                    $array[$key][$val1] = null;
                }else{
                    $array[$key][$val1] = date($formate,$val[$val1]);
                }

            }
        }
        return $array;
    }

    /**
     * 转换数组里面的时间戳
     * @param array $array 需要转换的一维数组
     * @param array $filed 需要转换的字段
     * @param string $formate
     * @return string
     */
    protected function dateformatesingle($array,$filed,$formate="Y-m-d H:i:s"){
        foreach ($filed as $key=>$val){
            if($array[$val] < 1000){
                $array[$val] = null;
            }else{
                $array[$val] = date($formate,$array[$val]);
            }
        }
        return $array;
    }


    /**
     * 德高地图计算二个坐标 的距离
     * @param string $lat1
     * @param string $lng1
     * @param string $lat2
     * @param string $lng2
     * @param int $len_type
     * @param int $decimal
     * @return float
     */
    protected function GetDistance($lat1, $lng1, $lat2, $lng2, $len_type = 1, $decimal = 2)
    {
        $radLat1 = $lat1 * PI ()/ 180.0; //PI()圆周率
        $radLat2 = $lat2 * PI() / 180.0;
        $a = $radLat1 - $radLat2;
        $b = ($lng1 * PI() / 180.0) - ($lng2 * PI() / 180.0);
        $s = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $s = $s * 6378.137;
        $s = round($s * 1000);
        if ($len_type --> 1)
        {
            $s /= 1000;
        }
        return round($s, $decimal);
    }

    /**
     * 根据当天得到开始和结束时间
     * @return mixed
     */
    protected function getTime()
    {
        $data['s'] = strtotime(date("Y-m-d",time()));       //今天的开始时间
        $data['e'] = $data['s']+24*60*60-1;
        return $data;
    }
}
