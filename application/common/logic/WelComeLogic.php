<?php

namespace app\common\logic;

use think\Cache;
use app\common\model\OrdersModel;
use app\common\model\AgentModel;
use app\common\model\Ztorders;



class WelComeLogic {
  private  $Yesterday;
  private  $FinanceDay;
  
  public function __construct() {
    $temptime=date("Y-m-d",strtotime("-1 day"));
    $this->Yesterday=[$temptime." 00:00:00",$temptime." 23:59:59"];
    $day= date("d");
    $temptime= date("Y-m");
    $days= date("t");
    if($day<15){
        $this->FinanceDay=[$temptime."-1 00:00:00",$temptime."-14 23:59:59"];
    }else{
        $this->FinanceDay=[$temptime."-15 00:00:00",$temptime.="-".$days." 23:59:59"];
    }

  }
  
  /**
   * 昨天的报单情况
   */
  public function YesterdayOrders(){
    $orders=new OrdersModel();
    $where=["putintime"=>["between", $this->Yesterday]];
    $total=$orders->where($where)->count();
    $where["state"]=["neq",0];
    $doorders=$orders->where($where)->count();
    $notdo=$total-$doorders;
    return [
        "TotalOrders"=>$total,
        "DoOrders"=>$doorders,
        "NotDoOrders"=>$notdo
    ];
  } 
  
  /**
   * 昨天注册的代理商
   */
  public function YesterdayAgents(){
    $agent=new AgentModel();
    $cacheName="YesterdayAgents_".date("Y-m-d");
    if(Cache::has($cacheName)){
       $result=Cache::get($cacheName);
    }else{
      $where=["regtime"=>["between", $this->Yesterday]];
       $tempcount = $agent->where($where)->count();
       $result= ["PutInAgents"=>$tempcount];
       Cache::set($cacheName, $result,24*60*60);
    }
   
    return $result;
  }
  
  
  public function YesterdayCars(){
    $ztorder=new Ztorders();
    $where=["putintime"=>["between", $this->Yesterday]];
    $temp = $ztorder->where($where)->count();
    return ["PutInCars"=>$temp];
  }
  
  /**
   * 报单排序报表
   */
  public function YesterdaySort(){
    $orders=new OrdersModel();
    $where=["putintime"=>["between", $this->Yesterday]];
    $where["state"]=["eq",1]; 
    $total=$orders->where($where)->count();
    $where["sortid"]=["null",null];
    $sortscount=$orders->where($where)->count(); //没有排序的报单
    return [
        "total"=>$total,
        "nosort"=>$sortscount,
        "sortcount"=>$total-$sortscount
    ];
  }
  
  public function YesterdayFinance(){
    $orders=new OrdersModel();
    $where=[
          "entrytime"=>["between", $this->Yesterday],
          "state"=>1,
          "isrecome"=>0,
        ];
    $temp=$orders->where($where)->count();
    $totalmoney=$temp*1000;
    $totalInfo=["totalcount"=>$temp,"totalmoney"=>$totalmoney];
    $where=[
          "entrytime"=>["between", $this->Yesterday],
          "state"=>1,
          "isrecome"=>0,
          "financeend"=>1
        ];
     $temp=$orders->where($where)->count();
     $sendmoney=$temp*1000;
     $sendinfo=["sendcount"=>$temp,"sendmoney"=>$sendmoney];
     return [
          "total"=>$totalInfo,
          "send"=>$sendinfo
     ];
     
  }
  
}

