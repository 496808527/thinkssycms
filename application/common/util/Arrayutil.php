<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/11
 * Time: 12:09
 */

namespace app\common\util;

/**
 * Class arrayutil  数组操作类
 *
 */
class Arrayutil {

  /**
   * 合并数组
   * @param $data  索引二维数组
   */
  static function merge_array($data) {
    if (sizeof($data) == 1) {
      return $data;
    } else {
      return  array_merge($data);
    }
  }

  /**
   * 数组乱序
   * @param array $data 索引数组
   * @return array|bool  false表示空数组 或者 排序后的数组
   */
  static function rand_array($data = []) {
    if (empty($data)) {
      return false;
    } else {
      shuffle($data);
      return $data;
    }
  }

  /**
   * 截取数组  (可用于数组分页)
   * @param array $data  处理数组
   * @param int $start   截取数组开始的位置
   * @param int $lenght  截取长度 正数表示长度，负数表示倒数第几个位置
   */
  static function sub_array($data = [], $start = 0, $lenght = "") {
    if (empty($data))
      return false;
    if (empty($lenght)) {
      return array_slice($data, $start);
    } else {
      return array_slice($data, $start, $lenght);
    }
  }

  /**
   * 索引数组排序
   * @param array $data  需要排序的数组
   * @param bool $isAsc  是否升序 默认是升序排序
   * @return array|bool  空数组返回false  不是空数组返回排序后的数组
   */
  static function order_array($data = [], $isAsc = true) {
    if (empty($data))
      return false;
    if ($isAsc) {
      sort($data);
    } else {
      rsort($data);
    }
    return $data;
  }

  /**
   * 根据数组的键 对关联数组排序
   * @param array $data  需要排序的数组
   * @param bool $isAsc  是否升序 默认是升序排序
   * @return array|bool  空数组返回false  不是空数组返回排序后的数组
   */
  static function order_arraybykey($data = [], $isAsc = true) {
    if (empty($data))
      return false;
    if ($isAsc) {
      ksort($data);
    } else {
      krsort($data);
    }
    return $data;
  }

  /**
   * 根据数组的值，对关联数组排序
   * @param array $data  需要排序的数组
   * @param bool $isAsc  是否升序
   * @return array|bool  如果是  空数组返回false 否者返回 排序后的数组
   */
  static function order_arraybyvalue($data = [], $isAsc = true) {
    if (empty($data))
      return false;
    if ($isAsc) {
      asort($data);
    } else {
      arsort($data);
    }
    return $data;
  }

  /**
   * 判断数组是否为空
   * @param $data  需要检测的数组
   * @return bool
   */
  static function is_Emepty($data) {
    return empty($data);
  }

  /**
   * @param $data  需要处理的一维数组
   * @param $separator  分隔符
   * @return string  处理完成，返回的字符串
   */
  static function arrayTostring($data, $separator = ";") {
    if (empty($data))
      return "";
    if (is_array($data)) {
      return implode($separator, $data);
    } else {
      return false;
    }
  }

  /**
   * @param $data 需要处理的字符串
   * @param $separator 分隔符
   */
  static function stringToArray($data, $separator = ";") {
    if (empty($data)) {
      return [];
    } else {
      return explode($separator, $data);
    }
  }

  /**
   * 数组转字符串
   * @param $data 需要处理的数组
   * @return string 处理后的字符窜
   */
  static function arrayToJson($data) {
    return json_encode($data);
  }

  /**
   * 奖json数据转换成 数组
   * @param $json  需要处理的json
   */
  static function jsonToArray($json) {
    return json_decode($json, true);
  }

  /**
   * 比较2个数组值的差异，并返回差异集合
   * @param [] $array_1 数据库已经存在的集合
   * @param [] $array_2 提交过来的数据
   */
  static function compare_Array($array_1, $array_2) {
    $Arr = [];
    $tempa = array_values($array_1);
    $tempb = array_values($array_2);
    foreach ($tempa as $temp) {
      (!in_array($temp, $tempb)) ? $Arr["Del"][] = $temp : "";
    }
    foreach ($tempb as $temp) {
      (!in_array($temp, $tempa)) ? $Arr["Add"][] = $temp : "";
    }
    return $Arr;
  }
  
  /**
   * 格式化数组，形成以某个 key 键的 新的数组  如  【key=>[]】
   */
  static function arrayToHash($array,$key){
    $Arr=[];
    foreach ($array as $value){
      $Arr[$value[$key]]=$value;
    }
    return $Arr;
  }

}
