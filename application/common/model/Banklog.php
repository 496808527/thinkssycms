<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;

/**
 * Description of banklog
 *
 * @author ytkj
 */
class Banklog extends \think\Model{
  protected  $table="yt_banklog";
  
  public function bankloglist($where,$pagesize){
    $fix = \think\Config::get("database.prefix");
    $field="a.*,b.agentname,b.agentarea";
   
    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix."agent b","a.agentno=b.agentno","left")->field($field)->where($where)->order("a.id desc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix."agent b","a.agentno=b.agentno","left")->field($field)->where($where)->order("a.id desc")->paginate($pagesize);
    }
    return  $list; 
  }
  
  
  
}
