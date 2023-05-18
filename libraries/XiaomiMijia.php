<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class XiaomiMijia {



    const  deviceId = "rraYG18XoUKkod1i";// 小爱音箱设备id,可由java api生成
    const  userId = "18470888";// 小米账号id
    //java api登录一次即可，如果token失效再重新获取,token可以使用很长时间
    const  serviceToken = "HLVTbmo+XGxExxxxxxVLu7RthyyjNhKGXF/EnmnSM02RTpgr0XT3zxRvitSolBxxnMJjNYsnJJNU/qE5E4LPkCIXeTFfVGAubQMdG/SawQPUUca24tlyWTkAyo2/iOqzSntj/TQVgMkQ7CwY8k9EINsYIK+sc6swQI=";
    const  securityToken = "Prdfaaaaaaaa1HynDg==";


    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * e.g http://www.myweb.com/test
     * 访问url 关闭电视测试
     */
    public function test(){
        $msg = "关闭电视";
        $uri = "/miotspec/action";
        $data = "{\"params\":{\"did\":\"606917653\",\"siid\":5,\"aiid\":4,\"in\":[\"".$msg."\",false]}}";
        $nonce = $this->generateNonce();
        $signedNonce = XiaomiMijia::generateSignedNonce(self::securityToken,$nonce);
        $sign = XiaomiMijia::generateSignature($uri,$signedNonce,$nonce,$data);
        echo "nonce=".$nonce."<br/>";
        echo "signedNonce=".$signedNonce."<br/>";
        echo "java-result=Y9b9hlq8xOX+WtWtPHuPpQTap9KyaO/YSuN1gR9yhYU="."<br/>";
        echo "sign=".$sign."<br/>";


        $data = array(
            '_nonce'=>$nonce,
            'data'=>$data,
            'signature'=>$sign
        );
        $uri = '/miotspec/action';
        $rjson = $this->request_post($uri,$data);
        echo $rjson;

    }

    // 生成16 位 随机数
    public function generateNonce(){
        return random_string('alnum',16);
    }

    /**
     * 生成随机数签名
     * @param secret 密码
     * @param nonce 随机数
     * @return
     *
     */
    public static function generateSignedNonce($secret, $nonce)
    {
        $ctx = hash_init('sha256');
        $v1 = base64_decode($secret);// 先base64 解密
        $v2 = base64_decode($nonce);
        hash_update($ctx, $v1);
        hash_update($ctx, $v2);
        $hash_fine = hash_final($ctx,true);// 生成二进制hash值
        $res = base64_encode($hash_fine);// 后base64 加密
        return $res;

    }


    /**
     * 生成签名
     * @param url
     * @param signedNonce 随机数签名
     * @param nonce 随机数
     * @param data 数据
     * @return
     *
     */
    public static function generateSignature($url, $signedNonce, $nonce, $data) {
        $sign = $url."&".$signedNonce."&".$nonce."&data=".$data;
        $key = base64_decode($signedNonce);// 先解密
        $hmac = hash_hmac('sha256', $sign, $key, true);
        $signature = base64_encode($hmac);// 后base64 加密
        return $signature;

    }



    /**
     * 使用curl发送请求post到小米服务器,服务器会将指令发送给小爱音箱执行
     * @param string $url
     * @param string $param
     * @return bool|string
     */
    function request_post($uri,$data) {
        if (empty($uri) || empty($data)) {
            return false;
        }

        $postUrl = "https://api.io.mi.com/app" . $uri;
        $header = array(
            'User-Agent:APP/com.xiaomi.mihome APPV/6.0.103 iosPassportSDK/3.9.0 iOS/14.4 miHSTS',
            'x-xiaomi-protocal-flag-cli:PROTOCAL-HTTP2',
            'Cookie:PassportDeviceId="'.self::deviceId.'";userId="'.self::userId.'";serviceToken="'.self::serviceToken.'";');
        $postdata = http_build_query($data);
        $ch = curl_init();//初始化curl

        curl_setopt($ch, CURLOPT_URL, $postUrl);     // 要访问的地址
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);  // 对认证证书来源的检查   // https请求 不验证证书和hosts
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);  // 从证书中检查SSL加密算法是否存在
        curl_setopt($ch, CURLOPT_POST, true); // 发送一个常规的Post请求
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);     // Post提交的数据包
        //curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);     // 获取的信息以文件流的形式返回
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header); //模拟的header头

        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $data;
    }

    /**
     * 统一处理请求 指令 消息
     * @param $uri
     * @param $msg
     * @return bool|string
     */
    public function post_request($uri,$msg,$type=1){

        if($type == 1){
            // 消息
            $data = "{\"params\":{\"did\":\"606917653\",\"siid\":5,\"aiid\":3,\"in\":[\"".$msg."\"]}}";
        }else{
            $data = "{\"params\":{\"did\":\"606917653\",\"siid\":5,\"aiid\":4,\"in\":[\"".$msg."\",false]}}";
        }

        $nonce = $this->generateNonce();
        $signedNonce = XiaomiMijia::generateSignedNonce(XiaomiMijia::securityToken,$nonce);
        $sign = XiaomiMijia::generateSignature($uri,$signedNonce,$nonce,$data);

        $dataform = array(
            '_nonce'=>$nonce,
            'data'=>$data,
            'signature'=>$sign
        );

        $data = $this->request_post($uri,$dataform);
        $rjson = json_decode($data,true);
        return $rjson;
    }

}


