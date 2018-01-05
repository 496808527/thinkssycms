<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;

/**
 * Description of agent
 *
 * @author ytkj
 */
class AgentModel extends \think\Model{
  protected $table="yt_agent";
  
  
  public function  Agentlist($where,$pagesize){
    $fix = \think\Config::get("database.prefix");
    $field="a.*,b.agentname as parentname,b.agentidcard as pidcard,b.agentbankcard as pbankcard";
    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix."agent b","a.agentparent=b.agentno","left")->field($field)->where($where)->order("a.state desc,a.regtime desc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix."agent b","a.agentparent=b.agentno","left")->field($field)->where($where)->order("a.state desc,a.regtime desc")->paginate($pagesize);
    }
    
    return  $list;
  }
  
  public function Agentinfo($agentno){
    $fix = \think\Config::get("database.prefix");
    $field="a.*,b.agentname as parentname,b.agentidcard as pidcard,b.agentbankcard as pbankcard";
   if(empty($agentno)){
     return null;
   }else{
     $info = $this->alias("a")->join($fix."agent b","a.agentparent=b.agentno","left")->field($field)->where("a.agentno='$agentno'")->field($field)->find();
     $count=$this->where("agentparent='".$info["agentparent"]."'")->count();
     $info->count=$count;
   }
    return  $info;
  }
  
  public function  AgentMoney($agentno,$ordercount,$remark=""){
      $agentinfo=$this->Agentinfo($agentno);
      $agentinfo->account=1000*$ordercount;
      if($agentinfo->count==0){
        $percent=0;
      }else if ($agentinfo->count==1){
         $percent=0.02;
      }else if($agentinfo->count==2){
           $percent=0.06;
      }else{
        $percent=0.1;
      }
      $agentinfo->paccount= $agentinfo->account*$percent;  
      $agentinfo->ordercount=$ordercount;
      if($remark!=""){
         $agentinfo->agentbankcard=$remark;
      }
     
      return $agentinfo;
  }
  
  /**
   * 判断代理商是否是具备发放奖励的条件（判断条件 是超级管理代理，或者具备10个代理底单）
   * @return boolean
   */
  public function checkAgent($agentno){
    $info= $this->get(["agentno"=>$agentno]);
    if($info["isvip"]==1){
      return TRUE;
    }else{
      $count= (new OrdersModel())->where("agentno='$agentno' and isrecome=2")->count();
      if($count>=10){
        return TRUE;
      }else{
        return false;
      }
    }
  }
  
  public function GetAgentInfo($where){
    $fix = \think\Config::get("database.prefix");
    $field="a.*,b.agentname as parentname,b.agentidcard as pidcard,b.agentbankcard as pbankcard";
   if(empty($agentno)){
     return null;
   }else{
     $info = $this->alias("a")->join($fix."agent b","a.agentparent=b.agentno","left")->field($field)->where($where)->field($field)->find();
     $count=$this->where("agentparent='".$info["agentparent"]."'")->count();
     $info->count=$count;
   }
    return  $info;
  }
  
  
  
}
