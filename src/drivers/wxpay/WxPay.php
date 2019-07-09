<?php
namespace OsPay\Pay\Drivers\Wxpay;

use Illuminate\Http\Request;
use OsPay\Pay\Drivers\Wxpay\WxPayLib;

/**
 * 微信支付
 */
class WxPay
{
	
	protected $app;
	protected $config;
	protected $request;

	public function __construct(Request $request, $config)
	{
    	$this->app = app();
    	$this->config = $config;
    	$this->request = $request;
	}

	/**
	 * 下单
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-05-14
	 * @return   [type]       [description]
	 */
	public function pay_h5 ($order = array()) 
	{
		$wxpay_lib = new WxPayLib($this->config, $order);
        return $wxpay_lib->unifiedOrder();
	}

	public function jsapi_pay ($order = array(), $config = false) 
	{
		if ($config['notify_url']) $this->config['notify_url'] = $config['notify_url'];
		$wxpay_lib = new WxPayLib($this->config, $order);
		$pay_info = $wxpay_lib->unifiedOrder();
		$timestamp = time();
		$data = [
        	'appId' => $pay_info['appid'],
        	'timeStamp' => "$timestamp",
        	// 'nonceStr' => $pay_info['nonce_str'],
        	'nonceStr' => $wxpay_lib->genRandomString(),
        	'package' => 'prepay_id=' . $pay_info['prepay_id'],
        	'signType' => 'MD5',
        	// 'paySign' => $pay_info['sign'],
        ];
        $data['paySign'] = $wxpay_lib->MakeSign($data);
        return $data;
	}

	public function notify () 
	{
		$config = array(
            'mch_id' => $this->config['mch_id'],
            'appid' => $this->config['appid'],
            'key' => $this->config['key'],
        );
        $postStr = file_get_contents('php://input');
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($postObj === false) {
            die('parse xml error');
        }
        if ($postObj->return_code != 'SUCCESS') {
            die($postObj->return_msg);
        }
        if ($postObj->result_code != 'SUCCESS') {
            die($postObj->err_code);
        }
        $arr = (array)$postObj;
        unset($arr['sign']);
		$wxpay_lib = new WxPayLib($this->config);
        if ($wxpay_lib->MakeSign($arr) == $postObj->sign) {
            echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            return $arr;
        } else {
        	return false;
        }
	}

}