<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;

/**
 * Description of BizModel
 *
 * @author ytkj
 */
class BizModel extends \think\Model{
  protected $table="yt_business";
  
  public function bizlist($where,$pagesize){
    $fix = \think\Config::get("database.prefix");
    $where["a.agentno"]=["exp",'!=d.agentno'];
    $field="a.*,o.agentno as newagentno,d.agentname as newagentname,b.agentname,b.agentbankcard,o.sortid,"
            . "o.platform,o.platformtel,o.platformid,b.agentarea,o.remituser,o.entrytime,o.recomeuser";
   
    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix."orders o","a.ordersid=o.id","left")->join($fix."agent b","a.agentno=b.agentno","left")
              ->join($fix."agent d","o.agentno=d.agentno","left")
              ->where($where)->field($field)->order("a.putintime desc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list  = $this->alias("a")->join($fix."orders o","a.ordersid=o.id","left")->join($fix."agent b","a.agentno=b.agentno","left")
              ->join($fix."agent d","o.agentno=d.agentno","left")
              ->where($where)->field($field)->order("a.putintime desc")->paginate($pagesize);
    }
    return  $list;
  }
  
}
