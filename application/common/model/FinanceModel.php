<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;

/**
 * Description of FinanceModel
 *
 * @author ytkj
 */
class FinanceModel extends \think\Model {
  protected  $table="yt_finance";
  
  public function financelist($where,$pagesize){
    $fix = \think\Config::get("database.prefix");
    $field="a.*,b.agentname,c.agentname as pagentname,d.username,o.sortid";
   
    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix."agent b","a.agentno=b.agentno","left")->join($fix."admins d","a.adminid=d.id","left")->
              join($fix."agent c","a.pagentno=c.agentno","left")->join($fix."orders o","o.id=a.orderid","left")->field($field)->where($where)->order("a.biztime desc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix."agent b","a.agentno=b.agentno","left")->join($fix."admins d","a.adminid=d.id","left")->
          join($fix."agent c","a.pagentno=c.agentno","left")->where($where)->join($fix."orders o","o.id=a.orderid","left")->field($field)->order("a.biztime desc")->paginate($pagesize);
    }
    
    return  $list;
  }
}
