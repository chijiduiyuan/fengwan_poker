<?php
/**
 * 验证码操作类
 *
 * @author HJH
 * @version  2017-6-6
 */


// include_once($global['path']['lib'] . '/aws/aws-autoloader.php');
include_once($global['path']['lib'] . 'dysms/vendor/autoload.php');

use Aliyun\Core\Config;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Api\Sms\Request\V20170525\SendSmsRequest;

// 加载区域结点配置
Config::load();

// namespace Aliyun\DySDKLite\Sms;
// require_once "./SignatureHelper.php";
// use Aliyun\DySDKLite\SignatureHelper;
/**
 * 验证码发送类
 */
class Verify{

	//构造函数
	function __construct( ) {
		
	}

	static $acsClient = null;

	/**
     * 取得AcsClient
     *
     * @return DefaultAcsClient
     */
    public static function getAcsClient() {
        //产品名称:云通信流量服务API产品,开发者无需替换
        $product = "Dysmsapi";

        //产品域名,开发者无需替换
        $domain = "dysmsapi.aliyuncs.com";

        // TODO 此处需要替换成开发者自己的AK (https://ak-console.aliyun.com/)
		$accessKeyId = "LTAISyQq9iIV46pl"; // AccessKeyId
		$accessKeySecret = "zHTkLk301gVCkxspOBUrrov4lN0Ldy"; // AccessKeySecret

        // 暂时不支持多Region
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";


        if(static::$acsClient == null) {

            //初始化acsClient,暂不支持region化
            $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

            // 增加服务结点
            DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

            // 初始化AcsClient用于发起请求
            static::$acsClient = new DefaultAcsClient($profile);
        }
        return static::$acsClient;
    }

      public static function sendSms( $phoneNumbers, $templateParam, $outId = null) {

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        //可选-启用https协议
        //$request->setProtocol("https");

        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($phoneNumbers);

        // 必填，设置签名名称
        $request->setSignName('疯玩网络');

        // 必填，设置模板CODE
        $request->setTemplateCode('SMS_133978841');

        //$request->setAcceptFormat('JSON');

        // 可选，设置模板参数
        if($templateParam) {
            $request->setTemplateParam(json_encode($templateParam));
        }

        // 可选，设置流水号
        if($outId) {
            $request->setOutId($outId);
        }

        // 发起访问请求
        $acsResponse = static::getAcsClient()->getAcsResponse($request);

        //打印请求结果
        //var_dump($acsResponse);
        //return $acsResponse;
        return $acsResponse->Code;

    }
	//发送验证码
    public static function send($cid,$mobile,$smsTplNote) {

		//生成验证码
		$code = mt_rand(1000, 9999); //四位数字验证码(随机)

		//发送手机短信
        $acsResponse = Verify::sendSms($mobile,Array("code" =>$code));
		if($statusCode!='OK') {
			return $statusCode;
        }
		//保存到缓存
		$cache = getCache();
		return $cache->set('verify_code_'.$cid.'_'.$mobile, $code,600);
	}
	//获取验证码
	public static function get($cid,$mobile) {
		$cache = getCache();
		return $cache->get('verify_code_'.$cid.'_'.$mobile);
	}


	//清除验证码  
    public static function delete($cid,$mobile) {

		$cache = getCache();
		$cache->delete('verify_code_'.$cid.'_'.$mobile);
		$cache->delete('verify_error_'.$cid.'_'.$mobile);
		return true;
	}

	//错误次数累计
	public static function error($cid,$mobile, $add=false) {

		$cache = getCache();
		$num = (int)$cache->get('verify_error_'.$cid.'_'.$mobile);
		if($add) {
			$num++;
			$cache->set('verify_error_'.$cid.'_'.$mobile, $num,600);
		}
		return $num;
    }


    //轻量版
    public static function sendLitle($cid,$phone){
        $params = array ();

    // *** 需用户填写部分 ***

    // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
    $accessKeyId = "LTAISyQq9iIV46pl";
    $accessKeySecret = "zHTkLk301gVCkxspOBUrrov4lN0Ldy";

    // fixme 必填: 短信接收号码
    $params["PhoneNumbers"] = $phone;

    // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
    $params["SignName"] = "疯玩网络";

    // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
    $params["TemplateCode"] = "SMS_133978841";

    // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
    $params['TemplateParam'] = Array (
        "code" => mt_rand(1000,9999),
        "product" => "阿里通信"
    );

    // fixme 可选: 设置发送短信流水号
    //$params['OutId'] = "12345";

    // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
    //$params['SmsUpExtendCode'] = "1234567";


    // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
    if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
        $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
    }

    // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
    $helper = new SignatureHelper();

    // 此处可能会抛出异常，注意catch
    $content = $helper->request(
        $accessKeyId,
        $accessKeySecret,
        "dysmsapi.aliyuncs.com",
        array_merge($params, array(
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ))
        // fixme 选填: 启用https
        // ,true
    );

    return $content;
    }
}