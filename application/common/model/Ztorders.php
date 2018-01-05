<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;

/**
 * Description of Ztorders
 *
 * @author ytkj
 */
class Ztorders extends \think\Model {
  protected  $table="yt_ztorders";
  
  public function ztlist($where,$pagesize){
    $fix = \think\Config::get("database.prefix");
    $field="a.*,c.agentname,d.username,c.agenttel";
   
    if ($pagesize === TRUE) {
      $list =$this->alias("a")->join($fix."agent c","a.agentno=c.agentno","left")->
              join($fix."admins d","a.adminid=d.id","left")->where($where)->field($field)->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix."agent c","a.agentno=c.agentno","left")->
          join($fix."admins d","a.adminid=d.id","left")->
          where($where)->field($field)->order("a.putintime desc")->paginate($pagesize);
    }
    return  $list;
  }
}
