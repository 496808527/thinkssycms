<?php

/*
 * 
 * 
 * 
 * 
 * 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\controller;
use app\common\controller\AdminCommon;
use app\common\util\CometUtil;
use app\common\model\OrdersModel;
use app\common\model\AgentModel;
use think\Cache;

/**
 * Description of Export
 *
 * @author ytkj
 */
class Export extends AdminCommon{
  private $financeday;

  public  function __construct(\think\Request $request = null) {
    parent::__construct($request);
    $day = date("d");
    if ($day >= 15) {
      //这个月的上半个月
      $financedaybegin = date("Y-m") . "-1 00:00:00";
      $financedayend = date("Y-m") . "-15 23:59:59";
    } else {
      //上个月的下半个月
      $monthbegin = date("Y-m", mktime(0, 0, 0, date("m") - 1, 1, date("Y")));
      $financedaybegin = $monthbegin . "-16 00:00:00";
      $financedayend = $monthbegin . "-" . date("t", strtotime($monthbegin)) . " 23:59:59";
    }
    $this->financeday = [$financedaybegin, $financedayend];
  }


  public function WeekFinanceExport(){
    $cacheName = "WeekFinance" . $this->financeday[0];
    if(Cache::has($cacheName)){
      $list= Cache::get($cacheName);
    }else{
      $order = new OrdersModel();
      $agent= new AgentModel();
        $where = [
            "a.entrytime" => ["BETWEEN", $this->financeday],
            "a.state" => 1,
            "a.isrecome" => 0,
            "a.financeend" => 0
        ];
        $orderlist = $order->alias("a")->where($where)->group("agentno,remark")->field("count(1) totalcount,agentno,remark")->select();
        foreach ($orderlist as $temp) {
          $agentno = $temp["agentno"];
          $agentinfo = $agent->AgentMoney($agentno, $temp["totalcount"], $temp["remark"]);
          $list[] = $agentinfo;
        }
        Cache::set($cacheName, $list, 3 * 24 * 60 * 60);
    }
    $header=["代理商","代理地区","报单数量","银行类型","银行卡号","奖金额度","持卡人"];
    $data=[];
    foreach ($list as $value){
      $item=[];
      $item[]=$value["agentname"]."\t";
      $item[]=$value["agentarea"]."\t";
      $item[]=$value["ordercount"];
      $item[]=$value["agentbankcard"]."\t";
      $item[]=$value["bankname"]."\t";
      $item[]=$value["account"];
      $item[]=$value["bankowner"]?$value["bankowner"]:$value["agentname"];
      $data[]=$item;
    }
    $filename = time();
    $tempname = "奖金报表";
    $displayname= $this->financeday[0]."到". $this->financeday[1]."奖金报表";
    $files =CometUtil::export($header, $data, $tempname);
    $filename=export($files, $filename);
    $this->assign("displayname",$displayname);
    $this->assign("downname",$filename);
    return $this->fetch("orders/export");
    
  }
  
  public function exportdetail(){
    $cacheName = "Exportdetail" . $this->financeday[0];
    if(Cache::has($cacheName)){
      $list= Cache::get($cacheName);
    }else{
      $order = new OrdersModel();
        $where = [
            "a.entrytime" => ["BETWEEN", $this->financeday],
            "a.state" => 1,
            "a.isrecome" => 0,
            "a.financeend" => 0
        ];
        $list = $order->detaillist($where, TRUE);
        Cache::set($cacheName, $list, 3 * 24 * 60 * 60);
    }
   
    $header=["序号","入单人","推荐人","代理地区","打款人","打款时间"];
    $data=[];
    foreach ($list as $value){
      $item=[];
      $item[]=$value["sortid"]."\t";
      $item[]=$value["remituser"]."\t";
      $item[]=$value["recomeuser"];
      $item[]=$value["agentarea"]."\t";
      $item[]=$value["bankuser"]."\t";
      $item[]=$value["entrytime"]."\t";
      $data[]=$item;
    }
    unset($list);
    $filename = time();
    $tempname = "奖金详细报表";
    $displayname= $this->financeday[0]."到". $this->financeday[1]."奖金详细报表";
    $files =CometUtil::export($header, $data, $tempname);
    $filename=export($files, $filename);
    $this->assign("displayname",$displayname);
    $this->assign("downname",$filename);
    return $this->fetch("orders/export");
  }
  
          
          
}
