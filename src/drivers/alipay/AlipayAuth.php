<?php
namespace OsPay\Pay\Drivers\Alipay;

use OsPay\Pay\Drivers\Alipay\Aop\AopClient;
use OsPay\Pay\Drivers\Alipay\Aop\Request\AlipayUserInfoShareRequest;
use OsPay\Pay\Drivers\Alipay\Aop\Request\AlipaySystemOauthTokenRequest;
use Illuminate\Http\RedirectResponse;
use OsPay\Pay\Drivers\Alipay\Lotusphp\Logger\LtLogger;


/**
 * 支付宝用户认证
 *
 * @author _Haitao@追追网络
 */
class AlipayAuth
{
    
    protected $app;
    protected $request;
    protected $app_id;
    protected $redirect_url;
    protected $private_key;
    protected $public_key;

    // 请求地址
    public $auth_url;

    // 网关地址
    public $gateway;

    public function __construct($request, $config, $redirect_url)
    {
        $this->app = app();
        $this->request = $request;
        $this->app_id = $config['app_id'];
        $this->redirect_url = $config['redirect'];
        $this->private_key = $config['merchant_private_key'];
        $this->public_key = $config['alipay_public_key'];
        $this->auth_url = $config['auth_url'];
        $this->gateway = $config['gatewayUrl'];
        if (!defined("AOP_SDK_WORK_DIR"))
        {
            define("AOP_SDK_WORK_DIR", "/tmp/");
        }
    }


    public function auth () 
    {
        // return new RedirectResponse('http://www.baidu.com');
        return redirect($this->auth_url());
    }

    /**
     * 获取url
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-04-10
     * @return   [type]       [description]
     */
    public function auth_url () 
    {
        return $this->auth_url . '/oauth2/publicAppAuthorize.htm?app_id=' . $this->app_id . '&scope=auth_user&redirect_uri=' . $this->redirect_url;
    }

    /**
     * 回调
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-04-10
     * @return   function     [description]
     */
    public function callback () 
    {
        $aop = new AopClient ();
        $aop->gatewayUrl = $this->gateway;
        $aop->appId = $this->app_id;
        $aop->rsaPrivateKey = $this->private_key;
        $aop->alipayrsaPublicKey= $this->public_key;
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset ='UTF-8';
        $aop->format ='json';
        $request = new AlipaySystemOauthTokenRequest();
        $request->setGrantType("authorization_code");
        $request->setCode($this->app['request']->input('code'));
        $request->setRefreshToken('');
        $result = $aop->execute($request); 
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        if (isset($result->$responseNode)) {
            return $this->user_info($aop, $result->$responseNode->access_token);
        } else {
            dd($result);
        }
    }

    public function user_info ($aop, $accessToken) {
        $request = new AlipayUserInfoShareRequest ();
        $result = $aop->execute($request, $accessToken); 

        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if (!empty($resultCode) && $resultCode == 10000) {
            return $result->$responseNode;
        }
    }
}