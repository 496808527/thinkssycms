<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/11
 * Time: 12:09
 */

namespace app\common\util;

use think\Config;

class curlutil {

  /**
   * 获取gzip加密的数据
   */
  public static function file_get_curl_b($url, $gzip = false) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if ($gzip)
      curl_setopt($curl, CURLOPT_ENCODING, "gzip"); // 关键在这里
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    curl_close($curl);
    return $data;
  }

  public static function file_get_curl_c($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($curl, CURLOPT_TIMEOUT, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
      $of1 = fopen('./xh/res.txt', 'a');
      fwrite($of1, "\r\n" . date('Y-m-d h:i:s') . ':' . curl_error($curl));
    }
    curl_close($curl);
    return $data;
  }

  public static function smspost($url, $data, $proxy = null, $timeout = 20) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); //在HTTP请求中包含一个"User-Agent: "头的字符串。        
    curl_setopt($curl, CURLOPT_HEADER, 0); //启用时会将头文件的信息作为数据流输出。   
    curl_setopt($curl, CURLOPT_POST, true); //发送一个常规的Post请求  
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); //Post提交的数据包  
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); //启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量。     
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); //文件流形式         
    curl_setopt($curl, CURLOPT_TIMEOUT, $timeout); //设置cURL允许执行的最长秒数。   
    $content = curl_exec($curl);
    curl_close($curl);
    unset($curl);
    return $content;
  }

}
