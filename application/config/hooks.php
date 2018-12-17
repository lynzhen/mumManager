<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/../third_party/wxpay/WxPay.Api.php';
require_once __DIR__ . '/../helpers/cloud.php';

use \LeanCloud\Engine\LeanEngine;
use \LeanCloud\Engine\Cloud;
use \LeanCloud\Client;
use \LeanCloud\Storage\CookieStorage;
/*
 * Define cloud functions and hooks on LeanCloud
 */

// /1.1/functions/sayHello
Cloud::define("pay", function($params, $user) {
	// var_dump($user);
	$openid = $user->get('authData')["lc_weapp"]["openid"];
	// 		初始化值对象
	$input = new WxPayUnifiedOrder();
	// 		文档提及的参数规范：商家名称-销售商品类目
	$input->SetBody($params['body']);
	// 		订单号应该是由小程序端传给服务端的，在用户下单时即生成，demo中取值是一个生成的时间戳
	$input->SetOut_trade_no($params['tradeNo']);
	// 		费用应该是由小程序端传给服务端的，在用户下单时告知服务端应付金额，demo中取值是1，即1分钱
	$input->SetTotal_fee($params['totalFee']);
	$input->SetNotify_url("https://mumsystem.leanapp.cn/WXPay/notify");
	$input->SetTrade_type("JSAPI");
	// 		由小程序端传给服务端
	$input->SetOpenid($openid);
	// 		向微信统一下单，并返回order，它是一个array数组
	$order = WxPayApi::unifiedOrder($input);
	// 		json化返回给小程序端
	header("Content-Type: application/json");
	return getJsApiParameters($order);
	// return "hello {$params['name']}";
});


// ----------生成小程序码 云函数
Cloud::define("getImg", function($params, $user) {
	$ACCESS_TOKEN=getWxAccessToken();
	$url="https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=".$ACCESS_TOKEN['access_token'];
	$post_data=
	array(
		'page'=>'pages/Index/Index',
		'scene'=>'34,S853EE4QRP'//34%2CS853EE4QRP
	);
	$post_data=json_encode($post_data);
    $data=send_post($url,$post_data);
	$result=data_uri($data,'image/png');
	return $result;
});

function getWxAccessToken(){
	// 小程序appid
	$appid='wxbde3a54158b3bf14';
	// 小程序secret
	$appsecret='6008eb5ab12af1e8e784e64d58e7f8e3';
	
    // if($_SESSION['access_token_'.$appid]  && $_SESSION['expire_time_'.$appid]>time()){
    //     return $_SESSION['access_token_'.$appid];
    // }else{
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;
        $access_token = makeRequest($url);
		$access_token = json_decode($access_token['result'],true);
		// $_SESSION['access_token_'.$appid] = $access_token;
		// $_SESSION['expire_time_'.$appid] = time()+7000;
        return $access_token;
	// }
}

//二进制转图片image/png
function data_uri($contents, $mime)
{
$base64   = base64_encode($contents);
return ('data:' . $mime . ';base64,' . $base64);
}
// /**
//  * 发起http请求
//  * @param string $url 访问路径
//  * @param array $params 参数，该数组多于1个，表示为POST
//  * @param int $expire 请求超时时间
//  * @param array $extend 请求伪造包头参数
//  * @param string $hostIp HOST的地址
//  * @return array    返回的为一个请求状态，一个内容
//  */

function makeRequest($url, $params = array(), $expire = 0, $extend = array(), $hostIp = '')
{
    if (empty($url)) {
        return array('code' => '100');
    }

    $_curl = curl_init();
    $_header = array(
        'Accept-Language: zh-CN',
        'Connection: Keep-Alive',
        'Cache-Control: no-cache'
    );
    // 方便直接访问要设置host的地址
    if (!empty($hostIp)) {
        $urlInfo = parse_url($url);
        if (empty($urlInfo['host'])) {
            $urlInfo['host'] = substr(DOMAIN, 7, -1);
            $url = "http://{$hostIp}{$url}";
        } else {
            $url = str_replace($urlInfo['host'], $hostIp, $url);
        }
        $_header[] = "Host: {$urlInfo['host']}";
    }

    // 只要第二个参数传了值之后，就是POST的
    if (!empty($params)) {
        curl_setopt($_curl, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($_curl, CURLOPT_POST, true);
    }

    if (substr($url, 0, 8) == 'https://') {
        curl_setopt($_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    curl_setopt($_curl, CURLOPT_URL, $url);
    curl_setopt($_curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($_curl, CURLOPT_USERAGENT, 'API PHP CURL');
    curl_setopt($_curl, CURLOPT_HTTPHEADER, $_header);

    if ($expire > 0) {
        curl_setopt($_curl, CURLOPT_TIMEOUT, $expire); // 处理超时时间
        curl_setopt($_curl, CURLOPT_CONNECTTIMEOUT, $expire); // 建立连接超时时间
    }

    // 额外的配置
    if (!empty($extend)) {
        curl_setopt_array($_curl, $extend);
    }

    $result['result'] = curl_exec($_curl);
    $result['code'] = curl_getinfo($_curl, CURLINFO_HTTP_CODE);
    $result['info'] = curl_getinfo($_curl);
    if ($result['result'] === false) {
        $result['result'] = curl_error($_curl);
        $result['code'] = -curl_errno($_curl);
    }

    curl_close($_curl);
    return $result;
}

//  /**
//  * 消息推送http
//  * @param $url
//  * @param $post_data
//  * @return bool|string
//  */
function send_post( $url, $post_data ) {
	$options = array(
		'http' => array(
			'method'  => 'POST',
			'header'  => 'Content-type:application/json',
			//header 需要设置为 JSON
			'content' => $post_data,
			'timeout' => 60
			//超时时间
		)
	);
	$context = stream_context_create( $options );
	$result = file_get_contents( $url, false, $context );
	return $result;
}


//----------生成小程序码end

function getJsApiParameters($UnifiedOrderResult) {

	if(!array_key_exists("appid", $UnifiedOrderResult)
	|| !array_key_exists("prepay_id", $UnifiedOrderResult)
	|| $UnifiedOrderResult['prepay_id'] == "")
	{
		throw new WxPayException("参数错误");
	}
	$jsapi = new WxPayJsApiPay();
	$jsapi->SetAppid($UnifiedOrderResult["appid"]);
	$timeStamp = time();
	$jsapi->SetTimeStamp("$timeStamp");
	$jsapi->SetNonceStr(WxPayApi::getNonceStr());
	$jsapi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
	$jsapi->SetSignType("MD5");
	$jsapi->SetPaySign($jsapi->MakeSign());
	$parameters = json_encode($jsapi->GetValues());
	// var_dump($parameters);
	return $parameters;
}

class CIEngine extends LeanEngine {
	function __invoke() {
		$this->dispatch($_SERVER['REQUEST_METHOD'],
			$_SERVER['REQUEST_URI']);
	}
}

$hook['pre_system'] = function() {
	// 参数依次为 AppId, AppKey, MasterKey
	Client::initialize("YFzggloQWOnyQPwmXGnRHnGW-gzGzoHsz", "5pJ2hDHl7FOTWElqoEADa6kR" ,"bfQPDGLeIM8jFBakOJpPgoTA");
	Client::useMasterKey(true);
	Client::setDebug(true);
	Client::setStorage(new CookieStorage());
	$engine = new CIEngine();
	// 以下是核心语句，直接像使用函数那样在对象上调用
	$engine();
};
