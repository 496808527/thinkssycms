<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;

/**
 * Description of OrdersModel
 *
 * @author ytkj
 */
class OrdersModel extends \think\Model {

  protected $table = "yt_orders";

  //审核订单
  public function orderlist($where, $pagesize) {
    $fix = \think\Config::get("database.prefix");
    $field = "a.*,b.agentname,b.agentarea,b.agentbankcard,b.id as uid";

    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.isrecome asc,a.putintime desc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.isrecome asc,a.putintime desc")->paginate($pagesize);
    }
    return $list;
  }

  //前端报单列表
  public function mainlist($where, $pagesize) {
    $fix = \think\Config::get("database.prefix");
    $field = "a.*,b.agentname,b.agentarea,b.agentbankcard,b.id as uid";

    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.id desc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.id desc")->paginate($pagesize);
    }
   
    foreach ($list as $key=>$item){
      $time= time();
      if(empty($item["state"])){
        $isshow=0;
      }else{
        $itemtime = empty($item["entrytime"])?$item["putintime"]:$item["entrytime"];
        $itemtime= strtotime($itemtime);
        if($time-$itemtime>=24*60*60){
           $isshow=1;
        }else{
          $isshow=0;
        }
      }
      $item["isshow"]=$isshow;
      $list[$key]=$item;
      
    }
    return $list;
  }

  //报单排序
  public function ordersort($where, $pagesize) {
    $fix = \think\Config::get("database.prefix");
    $field = "a.*,b.agentname,b.agentarea,b.agentbankcard,b.id as uid";

    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.entrytime asc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.entrytime asc")->paginate($pagesize);
    }
    return $list;
  }

  //财务报单列表
  public function financeorder($where, $pagesize) {
    $fix = \think\Config::get("database.prefix");
    $field = "a.*,b.agentname,b.agentarea,b.agentbankcard,b.id as uid,c.agentname as parentname,b.agentparent as pagentno";

    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->join($fix . "agent c", "c.agentno=b.agentparent", "left")->
                      field($field)->where($where)->order("a.putintime desc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->join($fix . "agent c", "c.agentno=b.agentparent", "left")->
                      field($field)->where($where)->order("a.putintime desc")->paginate($pagesize);
    }
    return $list;
  }

  //报单复投
  public function addredelivery( $info, $type = 0) {
    unset($info->sortid);
    unset($info->state);
    unset($info->id);
    unset($info->entrytime);
    unset($info->agentname);
    unset($info->agentarea);
    unset($info->agentbankcard);
    unset($info->uid);
    $orders = new OrdersModel($info->data);
    $orders->isredelivery = 1;
    if ($type == 0) {
      $orders->isspecial = 1;
    } else {
      $orders->isspecial = 0;
    }
    $orders->putintime = date("Y-m-d H:i:s");
    $orders->isputin=isputin;
    $orders->issend = 0;
    $orders->save();
    return $orders;
  }
  
  public function copyorder(OrdersModel $info){
    if(empty($info)){
      return ;
    }
    unset($info->sortid);
    unset($info->state);
    unset($info->id);
    $orders=new OrdersModel($info->data);
    return  $orders->save();
    
  }
  
  //报单信息
  public function orderinfo($id) {
    $fix = \think\Config::get("database.prefix");
    if (empty($id)) {
      return [];
    } else {
      $field = "a.*,b.agentname,b.agentarea,b.agentbankcard,b.id as uid,b.agenttel";
      return $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->
                      field($field)->where("a.id=$id")->find();
    }
  }
  
   //审核订单
  public function sortList($where, $pagesize) {
    $fix = \think\Config::get("database.prefix");
    $field = "a.*,b.agentname,b.agentarea,b.agentbankcard,b.id as uid";

    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.sortid asc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.sortid asc")->paginate($pagesize);
    }
    return $list;
  }
  
  public function detaillist($where,$pagesize){
    $fix = \think\Config::get("database.prefix");
    
    $field="";
     if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.agentno asc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->order("a.agentno asc")->paginate($pagesize);
    }
    return $list;
  }

}
