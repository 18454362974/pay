<?php
namespace OsPay\Pay\Drivers\Etone;

use OsPay\Pay\Drivers\Etone\Process;

/**
 * 易通支付
 * @author _Haitao@追追网络 <[1597575273@qq.com]>
 */
class Pay
{

	private $merchantId = '888201901071120';

	private $bussId = 'ONL0004';

	private $datakey = 'J3667a750866s09C';

    // 提交地址（测试）
    private $url = 'http://58.56.23.89:7006/NetPay/BankSelect.action';
	
 	// 订单查询地址
    private $search_url = 'http://58.56.23.89:7006/NetPay/MerOrderQuery.action';
  
  	// 对账单文件下载地址
  	private $pay_file_url = 'http://58.56.23.89:7006/NetPay/loadTradeFile.action';

    public $app;

    public $request;

    public $backURL;

    public function __construct($request, $config, $redirect_url)
    {
        $this->app = app();
        $this->request = $request;
        $this->backURL = $config['etone_pay_notify_url'];
    }
	
    /**
     * 下单
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-01-08
     * @return   [type]       [description]
     */
    public function orderAdd ($order) 
    {
        $arr =  "version=1.0.0"
              . "&transCode=8888"
              . "&merchantId=" . $this->merchantId
              . "&merOrderNum=" . $order['order_sn']
              . "&bussId=ONL0004"
              . "&tranAmt=" . $order['money'] * 100
              // . "&tranAmt=" . 2
              . "&sysTraceNum=" . $order['order_sn']
              . "&tranDateTime=" . date('YmdHis', $order['add_time'])
              . "&currencyType=156"
              . "&merURL=http://merURL.com"
              . "&backURL=" . $this->backURL
              . "&orderInfo="
              . "&userId=" . $order['shop_user_id']
              . "&userIp="
              . "&bankId=888880601002900"
              . "&stlmId="
              . "&entryType=1"
              . "&authCode="
              . "&activeTime=3"
              . "&channel=" . $order['pay_type']

              . "&sub_appid=wx0a1f395b928a07db"
              . "&sub_openid=" . $order['sub_openid']
              . "&spbillCreateIp="
              . "&deviceInfo="
              . "&mchAppName="
              . "&mchAppId="

              . "&attach="
              . "&reserver1="
              . "&reserver2="
              . "&reserver3="
              . "&reserver4=7";
        $signArr = array(
            "version" => "1.0.0",
            "transCode" => "8888",
            "merchantId" => $this->merchantId,
            "merOrderNum" => $order['order_sn'],
            "bussId" => "ONL0004",
            "tranAmt" => $order['money'] * 100,
            "sysTraceNum" => $order['order_sn'],
            "tranDateTime" => date('YmdHis', $order['add_time']),
            "currencyType" => "156",
            "merURL" => "http://merURL.com",
            "backURL" => $this->backURL,
            "orderInfo" => "",
            "userId" => $order['shop_user_id'],
            "authCode" => '',
        );
        $arrStr = "txnString:" . $this->sortParam($signArr);

        $signValue = md5($this->sortParam($signArr) . $this->datakey);
        $arr .= '&signValue=' . $signValue;
        $p = new Process($this->url);
        // return json_encode($);
        return $p->send($arr);
    }

    /**
     * 订单查询
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-01-09
     * @return   [type]       [description]
     */
    public function orderSearch ($data) 
    {
        $arr = array(
            "merchantId" => $this->merchantId,
            "merOrderNum" => $data['merOrderNum'],
            'tranDate' => $data['tranDate'],
        );

        // $arrStr = "txnString:" . $this->sortParam($arr);

        // return $arr['merchantId'] . '|' . $arr['merOrderNum'] . '|' . $arr['tranDate'];

        $signValue = md5($this->merchantId . '|' . $arr['merOrderNum'] . '|' . $arr['tranDate'] . $this->datakey);
        // return $this->merchantId . '|' . $arr['merOrderNum'] . '|' . $arr['tranDate'] . $this->datakey;
        // echo "signValue:" . $signValue . '<br/>';

        // echo $arrStr;

        $arr['signValue'] = $signValue;
        // return $arr;
        $p = new Process($this->search_url, 'GET');
        return $p->send($arr);
        // return '123';
    }
  
  	public function payFile () 
    {
      	$arr = array(
            "merchantId" => $this->merchantId,
            'tranDate' => '20190222',
        );
        $arrStr = "txnString:" . $this->sortParam($arr);
        $signValue = md5($this->merchantId . '|' . $arr['tranDate'] . $this->datakey);

        $arr['signValue'] = $signValue;
        $p = new Process($this->pay_file_url);
        return $p->send($arr);
    }



    /**
     * 组合txnString
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-01-08
     * @param    [type]       $arr [description]
     * @return   [type]            [description]
     */
    public function sortParam ($arr) {
        return $arr["version"] . "|" .
               $arr["transCode"] . "|" .
               $arr["merchantId"] . "|" .
               $arr["merOrderNum"] . "|" .
               $arr["bussId"] . "|" .
               $arr["tranAmt"] . "|" .
               $arr["sysTraceNum"] . "|" .
               $arr["tranDateTime"] . "|" .
               $arr["currencyType"] . "|" .
               $arr["merURL"] . "|" .
               $arr["backURL"] . "|" .
               $arr["orderInfo"] . "|" .
               $arr["userId"] . "|" .
               $arr["authCode"];
    }


    /**
     * RSA加密(用的公众号支付所以没用到)
     * 
     * @Author   _HaiTao@追追网络
     * @DateTime 2019-01-08
     * @param    [type]       $data [description]
     */
    private function RSA_data($data){

        $public_key = file_get_contents("./rsa_public_key.pem");

        $pu_key = openssl_pkey_get_public($public_key);

        $encrypted = '';
        openssl_public_encrypt(json_encode($data),$encrypted,$pu_key);

        return base64_encode($encrypted);
    }
}
