<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\util;

/**
 * Description of SmsSend
 *
 * @author ytkj
 */
class SmsSend {

	//发送短信的接口地址
	const API_SEND_URL='http://sms.253.com/msg/send?';

	//查询余额的接口地址
	const API_BALANCE_QUERY_URL='http://sms.253.com/msg/balance?';

	const API_ACCOUNT='N746509_N6450344';//短信账号从 https://zz.253.com/site/login.html 里面获取。

	const API_PASSWORD='vyRMEapKsY422a';//短信密码从 from https://zz.253.com/site/login.html 里面获取。

	/**
	 * 发送短信需要的接口参数
	 *
	 * @param string $mobile 		手机号码
	 * @param string $msg 			想要发送的短信内容
	 * @param string $needstatus 	是否需要状态报告 '1'为需要 '0'位不需要。
	 */
	public function sendSMS( $mobile, $msg, $needstatus = 1) {
		
		//发送短信的接口参数
		$postArr = array (
				          'un' => self::API_ACCOUNT,
				          'pw' => self::API_PASSWORD,
				          'msg' => $msg,
				          'phone' => $mobile,
				          'rd' => $needstatus
                     );
		
		$result = $this->curlPost( self::API_SEND_URL , $postArr);
		return $result;
	}
	
	/**
	 * 
	 *
	 *  查询余额
	 */
	public function queryBalance() {
		
		// 查询接口参数
		$postArr = array ( 
		          'un' => self::API_ACCOUNT,
		          'pw' => self::API_PASSWORD,
		);
		$result = $this->curlPost(self::API_BALANCE_QUERY_URL, $postArr);
		return $result;
	}

	/**
	 * 处理接口返回值
	 * 
	 */
	public function execResult($result){
		$result=preg_split("/[,\r\n]/",$result);
		return $result;
	}

	/**
	 * @param string $url  
	 * @param array $postFields 
	 * @return mixed
	 */
	private function curlPost($url,$postFields){
		$postFields = http_build_query($postFields); 
		if(function_exists('curl_init')){

			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_POST, 1 );
			curl_setopt ( $ch, CURLOPT_HEADER, 0 );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_POSTFIELDS, $postFields );
			$result = curl_exec ( $ch );
			if(curl_errno($ch))
			{
				return 'Curl error: ' . curl_error($ch);
			}
			curl_close ( $ch );
		}elseif(function_exists('file_get_contents')){
			
			$result=file_get_contents($url.$postFields);

		}
		return $result;
	}
	
	//魔术获取
	public function __get($name){
		return $this->$name;
	}
	
	//魔术设置
	public function __set($name,$value){
		$this->$name=$value;
	}
}