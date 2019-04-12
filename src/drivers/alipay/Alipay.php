<?php
namespace OsPay\Pay\Drivers\Alipay;

use OsPay\Pay\Drivers\Alipay\Aop\AopClient;
use OsPay\Pay\Drivers\Alipay\Aop\Request\AlipayTradeWapPayRequest;
use OsPay\Pay\Drivers\Alipay\Wappay\Buildermodel\AlipayTradeWapPayContentBuilder;
use OsPay\Pay\Drivers\Alipay\Wappay\Service\AlipayTradeService;

/**
 * 支付宝支付
 */
class Alipay
{

    protected $app;
	protected $request;

    public function __construct($request, $config)
    {
    	$this->app = app();
    	$this->request = $request;
    	$this->config = $config;
        if (!defined("AOP_SDK_WORK_DIR"))
        {
            define("AOP_SDK_WORK_DIR", "/tmp/");
        }
    }
	
	/**
	 * 支付宝h5支付
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-04-11
	 * @return   [type]       [description]
	 */
	public function pay_h5 ($config = false) 
	{
	    //商户订单号，商户网站订单系统中唯一订单号，必填
	    $out_trade_no = time().rand(1000, 9999);

	    //订单名称，必填
	    $subject = '测试商品';

	    //付款金额，必填
	    $total_amount = 1;

	    //商品描述，可空
	    $body = '测试商品';

	    //超时时间
	    $timeout_express="1m";

	    if (isset($config['app_id'])) $this->config['app_id'] = $config['app_id'];
	    if (isset($config['redirect'])) $this->config['redirect'] = $config['redirect'];
	    if (isset($config['auth_url'])) $this->config['auth_url'] = $config['auth_url'];
	    if (isset($config['gatewayUrl'])) $this->config['gatewayUrl'] = $config['gatewayUrl'];
	    if (isset($config['notify_url'])) $this->config['notify_url'] = $config['notify_url'];
	    if (isset($config['return_url'])) $this->config['return_url'] = $config['return_url'];
	    if (isset($config['charset'])) $this->config['charset'] = $config['charset'];
	    if (isset($config['sign_type'])) $this->config['sign_type'] = $config['sign_type'];
	    if (isset($config['merchant_private_key'])) $this->config['merchant_private_key'] = $config['merchant_private_key'];
	    if (isset($config['alipay_public_key'])) $this->config['alipay_public_key'] = $config['alipay_public_key'];

	    $payRequestBuilder = new AlipayTradeWapPayContentBuilder();
	    $payRequestBuilder->setBody($body);
	    $payRequestBuilder->setSubject($subject);
	    $payRequestBuilder->setOutTradeNo($out_trade_no);
	    $payRequestBuilder->setTotalAmount($total_amount);
	    $payRequestBuilder->setTimeExpress($timeout_express);
	    $payResponse = new AlipayTradeService($this->config);
	    $result=$payResponse->wapPay($payRequestBuilder,$this->config['return_url'],$this->config['notify_url']);

	    return ;
	}
}