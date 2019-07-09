<?php
namespace OsPay\Pay\Drivers\Etone;

use OsPay\Pay\Drivers\Etone\TCAES;
use OsPay\Pay\Drivers\Etone\OPAes;

/**
 * Created by PhpStorm.
 * User: guodp
 * Date: 2019/1/3
 * Time: 11:05
 */
// ini_set('date.timezone','Asia/Shanghai');
class ETone{
	/**
	 *请求报文格式：
	 *应用ID｜加密密钥｜报文体｜签名|随机数
	 *BASE64(app_id)｜BASE64(账户平台公钥加密(加密密钥))｜BASE64(AES(报文体))|BASE64(商户RSA私钥采用SHA1withRSA签名(报文体))|随机数

	 ①　商户RSA密钥：商户自行生成,并上送公钥给系统。
	②　报文加密密钥生成：由商户自行生成，不限定生成该对称密钥的具体生成方法。建议每笔交使用不同的加密密钥。(32长HEX)
	③　报文加密：商户使用加密密钥和AES算法对报文内容进行加密。平台解密出报文加密密钥后，使用AES算法解密报文。
	④　报文加密密钥加密：使用账户平台公钥加密报文加密密钥，账户平台收到报文后，使用自身私钥解密出报文加密密钥。
	⑤　签名：商户RSA私钥采用SHA1withRSA签名。待加签串为AES加密之前的明文串。
	⑥　RSA使用 RSA/ECB/PKCS1Padding 组合模式补位。
	⑦　AES使用AES/ECB/PKCS5Padding组合模式补位。
	⑧　随机数用于快速定位交易 （建议使用公共请求参数中交易流水client_trans_id）
	**/
	//自定义生成:应用ID
	public $appId = '965ad0447cad1580';

	//自定义生成:加密密钥
	public $key = 'Rg8O3ydM5WLYOI0d';

	//机构代码 
	public $instiCode = '888888888888888';//测试
	
	//报文体
	public $bodyData = array();

	//开户接口地址
	public $openAccUrl = 'http://ceshi4.sdykt.com.cn:1280/gateway/etone/api/ledger/open_acc';

	// 修改开户信息接口地址
	public $accModifyUrl = 'http://ceshi4.sdykt.com.cn:1280/gateway/etone/api/ledger/acc_modify';

	// 账户查询接口地址
	public $AccQueryUrl = 'http://ceshi4.sdykt.com.cn:1280/gateway/etone/api/ledger/acc_query';

	// 分账接口地址
	public $CutpayUrl = 'http://ceshi4.sdykt.com.cn:1280/gateway/etone/api/ledger/cutpay';

	// 提现接口地址
	public $WithdrawUrl = 'http://ceshi4.sdykt.com.cn:1280/gateway/etone/api/ledger/withdraw';
  
	// 大账户待结算余额查询
	public $SelectTotalUrl = 'http://ceshi4.sdykt.com.cn:1280/gateway/etone/api/ledger/settle_total';

	// 余额接口地址
	public $BalanceUrl = 'http://ceshi4.sdykt.com.cn:1280/gateway/etone/api/ledger/balance';
  
  	// 分账撤销
	public $CpRevokeUrl = 'http://ceshi4.sdykt.com.cn:1280/gateway/etone/api/ledger/cp_revoke';

	// 分账查询
	public $CutEqueryUrl = 'http://ceshi4.sdykt.com.cn:1280/gateway/etone/api/ledger/cut_equery';
  
	//平台公钥
	public $pubKey = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDMDAEcTxKUYejN8TB9Unl48TuerFpaH/8xwfqoeAR4sRWlII/Hac1cDemGMIOX0h8p1W9Hvalsaw1xUQyL6YkWjDVZTriZ+tT0rG/GCp/SwN/7rWWiztilLAZYKE+UR+rgshqWkDSglbXbRanMUUXEVeH4oLRcL+b2ecC/Ve/QyQIDAQAB';
	
	//商户私钥-自己生成
	public $priKey = 'MIICXQIBAAKBgQDBzbspwS1JQCqo760+aO5h8K5h1BBRniDsPdzCl61rhh5Ks75AiA2X/rfm67ff5YjAEVQrRS8WGZUwaAUuwhGr10XIMo4BgF4m+tfn+6SKaJUgILdTRc3b5Lv5QVWM2+4RfUkvhJjUgDPfBXsU3T/rGIeasLOqLZtv7qeqvVlvBQIDAQABAoGBAItKkW22QRU3wDGBahPEGSldcggwAbaXn3QMFmHp4CO61oS8YU5COvr2gTjATHzonXvmOIeNBwWsR3TuHmN36nhGQw8fnJZ5OjRgm0tqeQU6TFT//E/hCZTD6atfQJkzX7zArS5Ko0a5GHlA9JwdGB8QkMlCC1Ja/oZBW+aoOx31AkEA5W5IwWuor6On3fP/eZGWDCTlR8KEh5Z22cbSgDKmQi0d7oUAx9hbGuc1LBzphlwOaqCfD1GOzvI8TEh+huKD+wJBANg/PhQFBnNSFr9eSoh+53OTwDDkJ+JjeQj84jnqjgMMvYhU75GHzBBcn7Xu3LmoPuqyprjPVzvDnSt0kd8uaP8CQD1oQDbs3tBr6DFC7l0Wd2e2tFt5l8lGn3b4fTzs0Y0i0EEX/jZ/FRtlSNkOv/5DQ2SMqyYJeUSwlyz/tkXZ+OMCQQCb6rWCz8C/4189aeoJkp6lKdH4LnlHdPtu7I7cvW7ZahU6OCtn3ebXoUESd4A0aMe8h0VImU7Ha5pmG62VoqvXAkAKHjoi50YD1BrQEKODNA7F4OGvjbhN/RuxYDOigSRsjeDBF/VfmTLgkoGe91Zl831/lFsGaZy9l5jX9szhOJni';

	//拼装报文体数据
	public function dataCommon(){
		$this->bodyData['version'] = '1.0';
		$this->bodyData['instiCode'] = $this->instiCode;
		$this->bodyData['reqTime'] = date('YmdHis');
		// $this->bodyData['trxType'] = 'OPERN_ACC';//开户
		$bodyJson = json_encode($this->bodyData);
		// $res_json = preg_replace("#\\\u([0-9a-f]{4})#ie", "iconv('UCS-2BE', 'UTF-8', pack('H4', '\\1'))", $bodyJson);
		// $res_json = preg_replace_callback("/\\\\u([0-9a-f]{4})/i", create_function('$matches', 'return iconv("UCS-2BE","UTF-8",pack("H*", $matches[1]));'), $bodyJson);
		$res_json = preg_replace_callback("/\\\\u([0-9a-f]{4})/i", function ($matches) {
			return iconv("UCS-2BE","UTF-8",pack("H*", $matches[1]));
		}, $bodyJson);
		return $res_json;
		
	}

	// public function create_matches ($matches) {
	// 	return iconv("UCS-2BE","UTF-8",pack("H*", $matches[1]));
	// }

	//AES加密
	public function aes($string){
		// dd(MCRYPT_RIJNDAEL_256);
		// $tcaes = new TCAES($this->key);
		// 加密
		// $encodeString = $tcaes->encode($string);
		$tcaes = new OPAes('AES-128-ECB', $this->key);
		$encodeString = $tcaes->encrypt($string);
		return $encodeString;
	}

	//读取公钥
	public function getPukey()
	{  
	   $encryptionKey4Server = $this->pubKey;
	   $pem = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($encryptionKey4Server, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
	   return $pem;
	}

	//RSA公钥加密
    function pubkeyEncrypt($source_data, $pu_key) {
		$data = "";
		$dataArray = str_split($source_data, 117);
		foreach ($dataArray as $value) {
			$encryptedTemp = ""; 
			openssl_public_encrypt($value,$encryptedTemp,$pu_key);//公钥加密
			$data .= base64_encode($encryptedTemp);
		}
		return $data;
    }

	//RSA私钥采用SHA1withRSA签名
	public function rsaSign($str){
        $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->priKey, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
        openssl_sign($str, $sign, $res);
        return base64_encode($sign);
    }

	//RSA验签
	public function verifySign($string,$sign){
		$res = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->pubKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
		$result = (bool)openssl_verify($string, base64_decode($sign), $res);
        if ($result == false) {
            return false;
        }
        return true;
	}
	
	//post
	public function post($post_data,$url)
    {
        $headers = array("Content-type: application/json;charset='utf-8'","Accept: application/json","Cache-Control: no-cache","Pragma: no-cache");
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60); //设置超时
        if(0 === strpos(strtolower($url), 'https')) {
            　　curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //对认证证书来源的检查
            　　curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $rtn = curl_exec($ch);//CURLOPT_RETURNTRANSFER 不设置  curl_exec返回TRUE 设置  curl_exec返回json(此处) 失败都返回FALSE
        curl_close($ch);
        return $rtn;
    }

	/*
	 * 报文解密
	 */
	public function stringDencrypt($str){
		$data = explode("|",$str);
		if($data[0] == '0'){
			return $data;
		}
		$tcaes = new OPAes('AES-128-ECB', $this->key);
		//解密
		$encodeString = $tcaes->decrypt($data[1]);
		//验签
		$rsaVerify = $this->verifySign($encodeString,$data[2]);
		if(!$rsaVerify){
			return array('0','签名错误');
		}
		return json_decode($encodeString,true);
	}
  
  	/*
	 *	提现通知
	 */
 	public function withdrawDencrypt($str){
	    $data = explode("|",$str);
	    $flag = $data[0];
	    $rsaStr = $data[1];
	    $msgStr = $data[2];
	    $signStr = $data[3];

	    if($flag == '0') return false;
	    $priKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
                  wordwrap($this->priKey , 64, "\n", true) .
          		  "\n-----END RSA PRIVATE KEY-----";
	    //解出动态密钥
	    openssl_private_decrypt(base64_decode($rsaStr), $key, $priKey);
      	if (!openssl_private_decrypt(base64_decode($rsaStr), $key, $priKey)) {
        	echo "<br/>" . openssl_error_string() . "<br/>";
        }
	    $tcaes = new TCAES($key);
	    $encodeString = $tcaes->decode(base64_decode($msgStr));

	    //验签
	    $rsaVerify = $this->verifySign($encodeString,base64_decode($signStr));
	    if(!$rsaVerify) return false;
	    return json_decode($encodeString,true);
	}

	/**
	 * 查询
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-01-07
	 * @param    [type]       $accountNo [description]
	 * @return   [type]                  [description]
	 */
	public function accQuery ($accountNo) {
		//拼接报文数据
		$openString = base64_encode($this->appId).'|';
		//公钥加密
		$keyEncrypt = $this->pubkeyEncrypt($this->key, $this->getPukey());
		$openString .= $keyEncrypt.'|';
		$this->bodyData['trcNo'] = time();
		$this->bodyData['trxType'] = 'ACC_QUERY';
		$this->bodyData['data']['accountNo'] = $accountNo;
		$bodyString = $this->dataCommon();
		//报文体AES加密
		$aesBody = $this->aes($bodyString);
		$openString .= $aesBody.'|';
		//RSA签名
		$rsaSting = $this->rsaSign($bodyString);
		$openString .= $rsaSting.'|'.mt_rand();
		$data = $this->post($openString, $this->AccQueryUrl);
		//报文解密
		return $this->stringDencrypt($data);
	}

	/**
	 * 开户接口（企业）
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-01-07
	 * @param    [type]       $arr [description]
	 * @return   [type]            [description]
	 */
	public function openAccCompany($arr){
		//拼接报文数据
		$openString = base64_encode($this->appId).'|';
		//公钥加密
		$keyEncrypt = $this->pubkeyEncrypt($this->key,$this->getPukey());
		$openString .= $keyEncrypt.'|';
		$this->bodyData['trcNo'] = time();
		$this->bodyData['data']['custNo'] = $arr['cust_no'];
		$this->bodyData['data']['accType'] = $arr['acc_type'];
		$this->bodyData['data']['accBank'] = $arr['acc_bank'];
		if ($arr['acc_type'] == '01') {
			$this->bodyData['data']['corpName'] = $arr['corp_name'];
			$this->bodyData['data']['corpCode'] = $arr['corp_code'];
			$this->bodyData['data']['corpAccNo'] = $arr['corp_acc_no'];
			$this->bodyData['data']['interBankNo'] = $arr['inter_bank_no'];
			$this->bodyData['data']['bankArea'] = $arr['bank_area'];
		} else if ($arr['acc_type'] == '00') {
			$this->bodyData['data']['cardNo'] = $arr['card_no'];
			$this->bodyData['data']['cardName'] = $arr['card_name'];
			$this->bodyData['data']['idNo'] = $arr['id_no'];
			$this->bodyData['data']['mobileNo'] = $arr['mobile_no'];
		}

		$this->bodyData['data']['addiData'] = $arr['addi_data'];
		$this->bodyData['data']['reserved'] = $arr['reserved'];
		$this->bodyData['trxType'] = 'OPERN_ACC';
		$bodyString = $this->dataCommon();
		// return json_encode($this->bodyData);
		//报文体AES加密
		$aesBody = $this->aes($bodyString);
		$openString .= $aesBody.'|';
		//RSA签名
		$rsaSting = $this->rsaSign($bodyString);
		$openString .= $rsaSting.'|'.mt_rand();
		$data = $this->post($openString, $this->openAccUrl);
		//报文解密
		return $this->stringDencrypt($data);
	}
	// 个人
	// public function openAcc($arr){
	// 	//拼接报文数据
	// 	$openString = base64_encode($this->appId).'|';
	// 	//公钥加密
	// 	$keyEncrypt = $this->pubkeyEncrypt($this->key,$this->getPukey());
	// 	$openString .= $keyEncrypt.'|';
	// 	$this->bodyData['trcNo'] = time();
	// 	$this->bodyData['data']['custNo'] = $arr['custNo'];
	// 	$this->bodyData['data']['accType'] = $arr['accType'];
	// 	$this->bodyData['data']['accBank'] = $arr['accBank'];
	// 	$this->bodyData['data']['cardNo'] = $arr['cardNo'];
	// 	$this->bodyData['data']['cardName'] = $arr['cardName'];
	// 	$this->bodyData['data']['idNo'] = $arr['idNo'];
	// 	$this->bodyData['data']['mobileNo'] = $arr['mobileNo'];
	// 	$this->bodyData['data']['addiData'] = $arr['addiData'];
	// 	$this->bodyData['data']['reserved'] = $arr['reserved'];
	// 	$bodyString = $this->dataCommon();
	// 	//报文体AES加密
	// 	$aesBody = $this->aes($bodyString);
	// 	$openString .= $aesBody.'|';
	// 	//RSA签名
	// 	$rsaSting = $this->rsaSign($bodyString);
	// 	$openString .= $rsaSting.'|'.mt_rand();
	// 	$data = $this->post($openString,$this->openAccUrl);
	// 	//报文解密
	// 	return $this->stringDencrypt($data);
	// }
	
	/**
	 * 修改开户信息
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-01-07
	 * @param    [type]       $arr [description]
	 * @return   [type]            [description]
	 */
	public function accModify ($arr) 
	{
		//拼接报文数据
		$openString = base64_encode($this->appId).'|';
		//公钥加密
		$keyEncrypt = $this->pubkeyEncrypt($this->key,$this->getPukey());
		$openString .= $keyEncrypt.'|';
		$this->bodyData['trcNo'] = time();
		$this->bodyData['data']['accountNo'] = $arr['accountNo'];
		$this->bodyData['data']['accType'] = $arr['accType'];
		$this->bodyData['data']['accBank'] = $arr['accBank'];
		if ($arr['accType'] == '01') {
			$this->bodyData['data']['corpAccNo'] = $arr['corpAccNo'];
		} else if ($arr['accType'] == '00') {
			$this->bodyData['data']['cardNo'] = $arr['cardNo'];
			$this->bodyData['data']['mobileNo'] = $arr['mobileNo'];
		}
		$this->bodyData['data']['addiData'] = $arr['addiData'];
		$this->bodyData['data']['reserved'] = $arr['reserved'];
		$this->bodyData['trxType'] = 'OPERN_ACC';
		$bodyString = $this->dataCommon();
		//报文体AES加密
		$aesBody = $this->aes($bodyString);
		$openString .= $aesBody.'|';
		//RSA签名
		$rsaSting = $this->rsaSign($bodyString);
		$openString .= $rsaSting.'|'.mt_rand();
		$data = $this->post($openString, $this->accModifyUrl);
		//报文解密
		return $this->stringDencrypt($data);
	}

	/**
	 * 分账
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-01-09
	 */
	public function Cutpay ($data) 
	{
		//拼接报文数据
		$openString = base64_encode($this->appId).'|';
		//公钥加密
		$keyEncrypt = $this->pubkeyEncrypt($this->key,$this->getPukey());
		$openString .= $keyEncrypt.'|';
		$this->bodyData['trcNo'] = time();
		$this->bodyData['data']['paymentOrderNo'] = $data['mentOrderNo'];
		$this->bodyData['data']['data'][] = array(
			'orderNo' => $data['orderNo'],
			'accountNo' => $data['accountNo'],
			'amount' => $data['amount'],
			'addiData' => $data['addiData'],
			'reserved' => $data['reserved'],
		);

		$this->bodyData['trxType'] = 'CUTPAY';
		$bodyString = $this->dataCommon();
		// return json_encode($this->bodyData);
		//报文体AES加密
		$aesBody = $this->aes($bodyString);
		$openString .= $aesBody.'|';
		//RSA签名
		$rsaSting = $this->rsaSign($bodyString);
		$openString .= $rsaSting.'|'.mt_rand();
		$data = $this->post($openString, $this->CutpayUrl);
		//报文解密
		return $this->stringDencrypt($data);
	}

	/**
	 * 提现
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-01-10
	 * @param    [type]       $data [description]
	 */
	public function Withdraw ($data) 
	{
		//拼接报文数据
		$openString = base64_encode($this->appId).'|';
		//公钥加密
		$keyEncrypt = $this->pubkeyEncrypt($this->key,$this->getPukey());
		$openString .= $keyEncrypt.'|';
		$this->bodyData['trcNo'] = time();
		$this->bodyData['data']['orderNo'] = $data['orderNo'];
		$this->bodyData['data']['accountNo'] = $data['accountNo'];
		$this->bodyData['data']['amount'] = $data['amount'];
		$this->bodyData['data']['backNotifyUrl'] = $data['backNotifyUrl'];
		$this->bodyData['data']['addiData'] = $data['addiData'];
		$this->bodyData['data']['reserved'] = $data['reserved'];

		$this->bodyData['trxType'] = 'WITHDRAW';
		$bodyString = $this->dataCommon();
		// return json_encode($this->bodyData);
		//报文体AES加密
		$aesBody = $this->aes($bodyString);
		$openString .= $aesBody.'|';
		//RSA签名
		$rsaSting = $this->rsaSign($bodyString);
		$openString .= $rsaSting.'|'.mt_rand();
		$data = $this->post($openString, $this->WithdrawUrl);
		//报文解密
		return $this->stringDencrypt($data);
	}
  
  	/**
	 * 大账户待结算余额查询
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-02-21
	 */
	public function SelectTotal () 
	{
		//拼接报文数据
		$openString = base64_encode($this->appId).'|';
		//公钥加密
		$keyEncrypt = $this->pubkeyEncrypt($this->key,$this->getPukey());
		$openString .= $keyEncrypt.'|';
		$this->bodyData['trcNo'] = time();
		$this->bodyData['trxType'] = 'SETTLE_TOTAL';
		$bodyString = $this->dataCommon();
		// return json_encode($this->bodyData);
		//报文体AES加密
		$aesBody = $this->aes($bodyString);
		$openString .= $aesBody.'|';
		//RSA签名
		$rsaSting = $this->rsaSign($bodyString);
		$openString .= $rsaSting.'|'.mt_rand();
		$data = $this->post($openString, $this->SelectTotalUrl);
		//报文解密
		return $this->stringDencrypt($data);
	}

	
  	/**
	 * 子账户余额查询
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-01-14
	 * @param    [type]       $data [description]
	 */
	public function Balance ($account) 
	{
		//拼接报文数据
		$openString = base64_encode($this->appId).'|';
		//公钥加密
		$keyEncrypt = $this->pubkeyEncrypt($this->key,$this->getPukey());
		$openString .= $keyEncrypt.'|';
		$this->bodyData['trcNo'] = time();
		$this->bodyData['data']['accountNo'] = $account;

		$this->bodyData['trxType'] = 'BALANCE';
		$bodyString = $this->dataCommon();
		// return json_encode($this->bodyData);
		//报文体AES加密
		$aesBody = $this->aes($bodyString);
		$openString .= $aesBody.'|';
		//RSA签名
		$rsaSting = $this->rsaSign($bodyString);
		$openString .= $rsaSting.'|'.mt_rand();
		$data = $this->post($openString, $this->BalanceUrl);
		//报文解密
		return $this->stringDencrypt($data);
	}
  	
	/**
	 * 分账撤销
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-02-20
	 * @param    [type]       $data [description]
	 */
	public function CpRevoke ($data) 
    {
    	//拼接报文数据
		$openString = base64_encode($this->appId).'|';
		//公钥加密
		$keyEncrypt = $this->pubkeyEncrypt($this->key,$this->getPukey());
		$openString .= $keyEncrypt.'|';
		$this->bodyData['trcNo'] = time();
		$this->bodyData['data']['orderNo'] = 'cpr' . $data['trans_no'];
		$this->bodyData['data']['cpOrderNo'] = $data['trans_no'];
		$this->bodyData['data']['accountNo'] = $data['account_no'];
		$this->bodyData['data']['addiData'] = '';
		$this->bodyData['data']['reserved'] = '';
		$this->bodyData['trxType'] = 'CP_REVOKE';
		$bodyString = $this->dataCommon();
		// return json_encode($this->bodyData);
		//报文体AES加密
		$aesBody = $this->aes($bodyString);
		$openString .= $aesBody.'|';
		//RSA签名
		$rsaSting = $this->rsaSign($bodyString);
		$openString .= $rsaSting.'|'.mt_rand();
		$data = $this->post($openString, $this->CpRevokeUrl);
		//报文解密
		return $this->stringDencrypt($data);
    }
  
  	
    /**
	 * 分账查询
	 * 
	 * @Author   _HaiTao@追追网络
	 * @DateTime 2019-02-20
	 * @param    [type]       $data [description]
	 */
	public function CutEquery ($data) 
    {
    	//拼接报文数据
		$openString = base64_encode($this->appId).'|';
		//公钥加密
		$keyEncrypt = $this->pubkeyEncrypt($this->key,$this->getPukey());
		$openString .= $keyEncrypt.'|';
		$this->bodyData['trcNo'] = time();
		$this->bodyData['data']['otherOrderNo'] = $data['order_no'];
		$this->bodyData['data']['accountNo'] = $data['account_no'];
		$this->bodyData['trxType'] = 'CUT_EQUERY';
		$bodyString = $this->dataCommon();
		// return json_encode($this->bodyData);
		//报文体AES加密
		$aesBody = $this->aes($bodyString);
		$openString .= $aesBody.'|';
		//RSA签名
		$rsaSting = $this->rsaSign($bodyString);
		$openString .= $rsaSting.'|'.mt_rand();
		$data = $this->post($openString, $this->CutEqueryUrl);
		//报文解密
		return $this->stringDencrypt($data);
    }

}

// header('content-type:text/html;charset=utf-8');
// $fenzhang = new fenzhang();


// //开户请求报文
// $arr = array(
// 	'custNo' => mt_rand(),//客户号
// 	'accType' => '00',
// 	'accBank' => '浦东发展银行',
// 	'cardNo' => '6217921763570291',
// 	'cardName' => '刘海涛',
// 	'idNo' => '372321199403270254',
// 	'mobileNo' => '17753181078',
// 	'addiData' => '',
// 	'reserved' => ''
// );
// // var_dump($fenzhang->dataCommon());
// // die();
// $bodyString = $fenzhang->openAcc($arr);
// echo '<pre>';print_r($bodyString);exit;





// //报文解密验签

// //返回示例报文
// $str = '1|71YLv/wQgnyTiLj00YiDGgK0z4GqktwKj+Y3jcdNmEDj7hLP5n2Vfp8MVIzwhaYGUHJhA4aR1HZB1u1wqzlUyXitZTDl4JZjNmQTe2ims4tEbxSVV50aZ/O1hMTTa/RMnk8sMAYpijFeNaMjzWfJUZvKAIkkoZ5UIcrnylQe2qm/2+e2/2dm4hzl+pfqA05jNVWuYPu+GDzhiVVTvgdfwbnI1HDzMyZeZSQNs3C+ZABIWtDv+2ii1ySa1/1FRuLTRV8W/hFaU1CSSBo8XEUwUv0vwTJXU2DDq1d6PLqoZe7IPDHjsOJmEAINg1on+6R78KNDXq4gE5Xa6nBWJBxlNA==|RBo5IbEPesLqIWRmihrIk+5lqaeGpXPyYYKh7gRZ7uzbiy8ULXvynxCjgwCcZzwAut3wCZHgYw+ndQjkvBsLv0l4jhNkwzAyMbG6iOL7GQdcbdHY9VTVBgCUfocKihd8J3/yz9lH+/g6OWqejRRP+UwO4RTvuiCvsCMemRjPZ5k=';

// $bodyString = $fenzhang->stringDencrypt($str);
// echo '<pre>';print_r($bodyString);exit;
