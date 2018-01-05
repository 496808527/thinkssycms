<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;

/**
 * Description of receipt
 *
 * @author ytkj
 */
class ReceiptModel extends \think\Model {
  protected  $table="yt_receipt";
  
   public function recelist($where,$pagesize){
    $fix = \think\Config::get("database.prefix");
    $field="a.*,b.username";
    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix."admins b","a.adminid=b.id","left")->field($field)->where($where)->order("a.entrytime asc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix."admins b","a.adminid=b.id","left")->where($where)->field($field)->order("a.entrytime asc")->paginate($pagesize);
    }
    return  $list;
  }
  
  
  public function receinfo($where){
    $fix = \think\Config::get("database.prefix");
    $field="a.*,b.nickname,c.remituser,c.bankuser";
    $list = $this->alias("a")->join($fix."admins b","a.adminid=b.id","left")->join($fix."orders c","a.orderid=c.id","left")->where($where)->field($field)->find();
    
    return  $list;
  }
}
