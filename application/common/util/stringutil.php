<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/11
 * Time: 12:09
 */
namespace app\common\util;

class stringutil{

    /**
     * @param $str 需要截取的字符串
     * @param $start  开始截取的位置 （负数表示从尾部开始算）
     * @param $length 需要截取的长度
     */
    static function substr($str,$start=0,$length=""){
        if($length==""){
            return substr($str,$start);
        }else{
            return substr($str,$start,$length);
        }
    }

    /**
     * @param $str  需要处理的字符串
     * @return int  返回字符串长度
     */
    static function strLen($str){
        return strlen($str);
    }


    /**
     * @param $str  需要处理的字符串
     * @param $start  开始截取的位置 （负数表示从尾部开始算）
     * @param string $length  需要截取的长度
     * @param string $encoding 字符编码
     * @return string  处理后的字符串
     */
    static function mb_substr($str,$start=0,$length="",$encoding="utf-8"){
            if($length==""){
                return mb_substr($str,$start,null,$encoding);
            }else{
                return mb_substr($str,$start,$length,$encoding);
            }
    }

    /**
     * @param $str 需要处理的字符串
     * @param string $encoding 字符编码
     * @return int  返回字符串长度
     */
    static function  mb_strlen($str,$encoding="utf-8"){
        return mb_strlen($str,$encoding);
    }

    /**
     * @param 需要处理的字符串
     * @return bool|mixed|string
     */
    static function str_encoding($str){
        $encode = mb_detect_encoding($str, array("ASCII",'UTF-8',"GB2312","GBK","BIG5"));
        return $encode;
    }

    /**
     * 将字符串进行编码转换  gbk to utf-8
     * @param $str 需要处理的字符串
     */
    static function  str_gbk_to_utf8($str){
       return iconv("gbk","utf-8//IGNORE",$str);
    }

    /**
     * 将字符串进行编码转换  utf-8 to gbl
     *@param $str 需要处理的字符串
     */
    static function str_utf8_to_gbk($str){
        return iconv("utf-8","GBK//IGNORE",$str);
    }


    /**
     * @param $str  搜索的字符串
     * @param $find 需要查找指定的字符
     * @param bool $isLeft 是否第一次出现的位置  true 表示第一次出现的位置， false 表示最后一次出现的位置
     */
    static  function  find_strIndex($str,$find,$isLeft=true){
        if($isLeft){
            return strpos($str,$find);
        }else{
            return  strrpos($str,$find);
        }
    }

    /**
     *判断字符串中是否存在某个字符
     * @param $str 搜索的字符串
     * @param $find   需要查找指定的字符串
     */
    static  function  str_exist($str,$find){
        return  (strpos($str, $find) !== false)?true:false;
    }

    static function  trimString($str){
      $str= str_replace("'", "", $str);
      $str= str_replace('"', "", $str);
      $trimString="";
      $len= strlen($str);
      for($i=0;$i<$len;$i++){
        $temp= trim(substr($str,$i,1));
        if((strlen($temp)&&!empty($temp))||$temp=="0"){
          $trimString.=substr($str,$i,1);
        }
      }
      return $trimString;
    }
    
    static function simpletrim($str){
      $str= str_replace("'", "", $str);
      $str= str_replace('"', "", $str);
      return $str;  
    }
    
    






}