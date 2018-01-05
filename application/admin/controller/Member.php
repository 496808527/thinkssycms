<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\controller;

use app\common\controller\AdminCommon;
use app\common\model\AgentModel;
use app\common\util\Arrayutil;
use app\common\model\Banklog;
use app\common\util\CometUtil;
use app\common\util\curlutil;
use app\common\model\BizModel;
use app\common\model\OrdersModel;
use think\Request;
use think\Cache;

/**
 * Description of Member
 * @author ytkj
 */
class Member extends AdminCommon {

  private $isvip = ["1" => "是", "0" => "不是"];
  private $statelist = ["1" => "已审核", "0" => "待审核"];
  private $stateselect = ["1" => "已审核", "2" => "待审核"];
  private $searchtype = ["1" => "标记", "2" => "底单", "3" => "普通报单"];
  private $orderstype = ["1" => "标记", "2" => "底单", "0" => "普通报单"];

  public function __construct(Request $request = null) {
    parent::__construct($request);
    $this->assign("stateselect", $this->stateselect);
    $this->assign("statelist", $this->statelist);
    $this->assign("isvip", $this->isvip);
    $this->assign("searchtype", $this->searchtype);
    $this->assign("orderstype", $this->orderstype);
  }

  public function index() {
    $parm= Request::instance()->param();
    $parms= CometUtil::trimparms($parm);
    $pageno = Request::instance()->param("page");
    $sessionid = session_id();
    $cachename = "member_where_$sessionid";
    $cacheexport = "member_export_where" . $sessionid;
    $agent = new AgentModel();
    if (!empty($pageno)) {
      if (Cache::has($cachename)) {
        $where = Cache::get($cachename);
      } else {
        $where = [];
      }
    } else {
      Cache::rm($cachename); //此处必须删除，否则会影响分页结果 
      Cache::rm($cacheexport); //此处必须删除，否则会影响分页结果 
      $where = [];
    }
    if (Request::instance()->isPost()) {
      $_REQUEST["page"]=1;
      if (strlen($parms["agentno"])) {
        $where["a.agentno|a.agentname|a.id"] = ["eq", $parms["agentno"]];
      }
      if (strlen($parms["pagentno"])) {
        $where["b.agentno|b.agentname|b.id"] = ["eq", $parms["pagentno"]];
      }
      if (strlen($parms["agentname"])) {
        $where["a.agentname"] = ["like", "%" . $parms["agentname"] . "%"];
      }
      if (strlen($parms["agentarea"])) {
        $where["a.agentarea"] = ["like", "%" . $parms["agentarea"] . "%"];
      }
      if (strlen($parms["agenttel"])) {
        $where["a.agenttel"] = ["eq",  $parms["agenttel"] ];
      }
      if (strlen($parms["state"])) {
        $temp = ($parms["state"] == 2) ? 0 : $parms["state"];
        $where["a.state"] = ["eq", $temp];
      }
      Cache::set($cacheexport, $where, 120);
      Cache::set($cachename, $where);
    }else{
      if(isset($where["a.agentno|a.agentname|a.id"])){
        $parms["agentno"]=$where["a.agentno|a.agentname|a.id"][1];
      }
      if(isset($where["b.agentno|b.agentname|b.id"])){
        $parms["pagentno"]=$where["b.agentno|b.agentname|b.id"][1];
      }
      if(isset( $where["a.agenttel"])){
        $parms["agenttel"]= str_replace("%", "", $where["a.agenttel"][1]) ;
      }
      
      if(isset( $where["a.agentname"])){
        $parms["agentname"]= str_replace("%", "", $where["a.agentname"][1]) ;
      }
      
      if(isset( $where["a.agentarea"])){
        $parms["agentarea"]= str_replace("%", "", $where["a.agentarea"][1]) ;
      }
      if(isset( $where["a.state"])){
        $parms["state"]= str_replace("%", "", $where["a.state"][1]) ;
      }
      
    }

    $list = $agent->Agentlist($where, 15);
    $page = $list->render();
    
    $this->assign("list", $list);
    $this->assign("page", $page);
    $this->assign("parm",$parms);
    return $this->fetch();
  }

  public function setvip() {
    $id = Request::instance()->param("orderid");
    if (empty($id)) {
      $id = Request::instance()->param("id");
    }
    $id = Arrayutil::stringToArray($id, ";");
    $agent = new AgentModel();
    $temp["id"] = ["in", $id];
    if ($agent->where($temp)->update(["isvip" => 1])) {
      return ["state" => "ok", "msg" => "操作成功"];
    } else {
      return ["state" => "err", "msg" => "系统繁忙，稍后再试"];
    }
  }

  public function cancelvip() {
    $id = Request::instance()->param("orderid");
    if (empty($id)) {
      $id = Request::instance()->param("id");
    }
    $id = Arrayutil::stringToArray($id, ";");
    $agent = new AgentModel();

    $temp["id"] = ["in", $id];
    if ($agent->where($temp)->update(["isvip" => 0])) {
      return ["state" => "ok", "msg" => "操作成功"];
    } else {
      return ["state" => "err", "msg" => "系统繁忙，稍后再试"];
    }
  }

  public function delmem() {
    $id = Request::instance()->param("id");
    $state = Request::instance()->param("isdel");
    $state = $state ? 0 : 1;
    $agent = AgentModel::get($id);
    $agent->state = $state;
    if ($agent->save()) {
      return ["state" => "ok", "msg" => "操作成功"];
    } else {
      return ["state" => "err", "msg" => "系统繁忙，稍后再试"];
    }
  }

  public function setrecome() {
    $id = Request::instance()->param("mid");
    $agentparent = Request::instance()->param("agentparent");
    $info = AgentModel::get($id);
    if (Request::instance()->isPost()) {
      if (!empty($info)) {
        $temp = AgentModel::get(["agentno" => $agentparent]);
        if ($info["agentno"] == $agentparent) {
          return ["state" => "err", "msg" => "推荐人不能是本人"];
        } else if ($info["agentno"] == $temp["agentparent"]) {
          return ["state" => "err", "msg" => "推荐人不能是他的下级"];
        } else {
          $info->agentparent = $agentparent;
          if ($info->save()) {
            return ["state" => "ok", "msg" => "设置成功！"];
          } else {
            return ["state" => "err", "msg" => "系统繁忙， 请稍后再试"];
          }
        }
      } else {
        return ["state" => "err", "msg" => "系统繁忙， 请稍后再试"];
      }
    } else {
      $this->assign("info", $info);
      return $this->fetch();
    }
  }

  public function searchmember() {
    $keyword = Request::instance()->param("keyword");
    $agent = new AgentModel();
    if (strlen($keyword)) {
      $where = [
          'a.agentname|a.agentarea|a.agentno' => ["like", "%" . $keyword . "%"],
      ];
      $list = $agent->Agentlist($where, true);
      return $list;
    } else {
      return [];
    }
  }

  public function checkbank() {
    $pageno = Request::instance()->param("page");
    $parm = Request::instance()->param();
    $sessionid = session_id();
    $cachename = "checkbank_where_$sessionid";
    if (!empty($pageno)) {
      if (Cache::has($cachename)) {
        $where = Cache::get($cachename);
      } else {
        $where = [];
      }
    } else {
      Cache::rm($cachename); //此处必须删除，否则会影响分页结果
      $where = [];
    }

    $banklog = new Banklog();
    $pagesize = 15;
    if (Request::instance()->isPost()) {
      if (strlen($parm["agentno"])) {
        $where ["b.agentno|b.agentname|b.id"] = ["eq", $parm["agentno"]];
      }
      if (strlen($parm["agentarea"])) {
        $where ["b.agentarea"] = ["like", "%" . $parm["agentarea"] . "%"];
      }
      if (strlen($parm["begintime"]) && strlen($parm["endtime"])) {
        $iend = max(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $istart = min(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $begintime = date("Y-m-d ", $istart) . "00:00:00";
        $endtime = date("Y-m-d ", $iend) . "23:59:59";
        $where["a.putintime"] = ["between", [$begintime, $endtime]];
        $parm["begintime"] = $begintime;
        $parm["endtime"] = $endtime;
      }
      if (strlen($parm["state"])) {
        $state = $parm["state"] == 2 ? 0 : $parm["state"];
        $where ["a.state"] = ["eq", $state];
      }
      Cache::set($cachename, $where);
    }
    $list = $banklog->bankloglist($where, $pagesize);
    $this->assign("parm", $parm);
    $this->assign("statelist", $this->statelist);
    $this->assign("stateselect", $this->stateselect);
    $this->assign("list", $list);
    $this->assign("page", $list->render());
    return $this->fetch();
  }

  public function setlogstate() {
    $id = Request::instance()->param("id", 0, "intval");
    $loginfo = Banklog::get($id);
    if (empty($loginfo)) {
      return ["state" => "err", "msg" => "非法的参数"];
    }
    $loginfo->state = 1;
    if ($loginfo->save()) {
      $agent = AgentModel::get(["agentno" => $loginfo["agentno"]]);
      $agent->agentbankcard = $loginfo->newbankcard;
      $agent->bankaddr = $loginfo->bankinfo;
      $agent->bankname = "建设银行";
      $agent->bankowner=$loginfo->bankowner;

      if ($agent->save()) {
        return ["state" => "ok", "msg" => "操作成功"];
      } else {
        $loginfo->state = 0;
        $loginfo->save();
        return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
      }
    } else {
      return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
    }
  }

  public function export() {
    $sessionid = session_id();
    $cacheexport = "member_export_where" . $sessionid;
    if (Cache::has($cacheexport)) {
      $where = Cache::get($cacheexport);
    } else {
      $where = [];
    }
    $member = new AgentModel();
    $list = $member->Agentlist($where, TRUE);
    $header = ["代理商", "代理区域", "电话号码", "身份证", "银行类型", "支行信息", "银行卡号","持卡人"];
    $data = [];
    foreach ($list as $value) {
      $item = [];
      $item[] = $value["agentname"];
      $item[] = $value["agentarea"];
      $item[] = $value["agenttel"] . "\t";
      $item[] = $value["agentidcard"] . "\t";
      $item[] = $value["bankname"];
      $item[] = $value["bankaddr"];
      $item[] =$value["agentbankcard"]."\t";
      $item[] =$value["bankowner"]?$value["bankowner"]:$value["agentname"];
      $data[] = $item;
    }
    $displayname = date("Y-m-d H:i:s") . "代理商银行卡信息";
    $filename = time();
    $files = CometUtil::export($header, $data, $filename);
    $filename = export($files, $filename);
    $this->assign("displayname", $displayname);
    $this->assign("downname", $filename);
    return $this->fetch("orders/export");
  }

  public function checkbankcard() {
    $url = "http://www.cardcn.com/search.php?word=6222002003100666904&submit=";
    $data = curlutil::file_get_curl_c($url);
    echo $data;
  }

  public function editagent() {
    $id = Request::instance()->param("id", 0, "intval");
    $parms = Request::instance()->param();
    $info = AgentModel::get($id);
    if (Request::instance()->isPost()) {
      if (empty($info)) {
        $info = new AgentModel();
        $info->agentno = CometUtil::buildno();
        $info->regtime = date("Y-m-d H:i:s");
        $info->state = 1;
      }
      if (strlen($parms["agenttel"])) {
        $temp = AgentModel::get(["agenttel" => $parms["agenttel"]]);
        if (!empty($temp) && empty($info)) {
          return ["state" => "err", "msg" => "电话号码已经被注册，请更换手机注册"];
        }
      } else {
        return ["state" => "err", "msg" => "代理商电话号码不能为空"];
      }

      foreach ($parms as $key => $parm) {
        if (strlen($parm) === 0 && $key != "id" && $key != "putinnagent") {
          return ["state" => "err", "msg" => "请填写完整的信息"];
        }
      }
      $info->agentname = $parms["agentname"];
      $info->agentparent = $parms["agentparent"];
      $info->agentarea = $parms["agentarea"];
      $info->agentidcard = $parms["agentidcard"];
      $info->agentbankcard = $parms["agentbankcard"];
      $info->agentaddr = $parms["agentaddr"];
      $info->agenttel = $parms["agenttel"];
      $info->isvip = $parms["isvip"];
      $info->bankname = $parms["bankname"];
      $info->bankaddr = $parms["bankaddr"];
      $info->bankowner=$parms["bankowner"];
      if ($info->save()) {
        return ["state" => "ok", "msg" => $this->_sucmsg];
      } else {
        return ["state" => "err", "msg" => $this->_errmsg];
      }
    } else {
      $this->assign("info", $info);
      return $this->fetch();
    }
  }

  //代理商报单平移
  public function moveorders() {
    $sessionid = session_id();
    $parm = Request::instance()->param();
    $parms = CometUtil::trimparms($parm);
    $pageno = Request::instance()->param("page");
    $where = "1!=1";
    $cacheName = "Cache_MoveOrders_Where" . $sessionid;
    if (empty($pageno)) {
      Cache::rm($cacheName);
    } else {
      $where = Cache::get($cacheName);
    }
    if (Request::instance()->isPost()) {
      $url = Request::instance()->url();
      $_REQUEST["page"] = 1;
      $where = ["a.state" => 1];
      if (!strlen($parms["agentname"])) {
        $this->error("代理商名字不能为空", $url, ["isopen" => "1"]);
      } else {
        $where = [
            "b.agentname|b.id" => ["eq", $parms["agentname"]],
        ];
      }
      if (strlen($parms["isrecome"])) {
        $isrecome = ($parms["isrecome"] == 3) ? 0 : $parms["isrecome"];
        $where["a.isrecome"] = $isrecome;
      }
      if (strlen($parms["remituser"])) {
        $where["a.remituser"] = ["like", "%" . $parms["remituser"] . "%"];
      }
      Cache::set($cacheName, $where);
    } else {
      if (isset($where["b.agentname|b.id"])) {
        $parms["agentname"] = $where["b.agentname|b.id"][1];
      }

      if (isset($where["a.isrecome"])) {
        $parms["isrecome"] = $where["a.isrecome"];
      }
      if (isset($where["a.remituser"])) {
        $parms["remituser"] = str_replace("%", "", $where["a.remituser"][1]);
      }
    }
    $orders = new OrdersModel();
    $list = $orders->ordersort($where, 10);
    $this->assign("list", $list);
    $this->assign("page", $list->render());
    $this->assign("parm", $parms);
    return $this->fetch();
  }

  //报单平移业务逻辑
  public function domove() {
    $type = Request::instance()->param("type", 2, "intval");  //【1直通车，2代理底单，3普通报单平移】
    $ids = Request::instance()->param("ids");
    $ids = Arrayutil::stringToArray($ids);
    $agentno = Request::instance()->param("agentno");
    $agent = new AgentModel();
    if (empty($agentno)) {
      return  ["state" => "err", "msg" => "请输入平移的目标代理商"];
    } else {
      if ($type == 2) {
        if ($agent->checkAgent($agentno)) {
          return ["state" => "err", "msg" => "已经有10个底单了，无需平移更多底单"];
        }
      }
    }
    $orders = new OrdersModel();
    $where["a.id"] = ["in", $ids];
    $list = $orders->mainlist($where, TRUE);
    if ($type == 2) {
      $info=$agent->Agentinfo($agentno);
      if($info["agentparent"]!=$list[1]["agentno"]){
        return ["state"=>"err","msg"=>"会员之前不存在上下级关系，无法平移"];
      }
    }
    
    $savedata = [];
    foreach ($list as  $value) {
      $item = [];
      $item["agentno"] = $value["agentno"];
      $item["ordertype"] = $type;
      $item["handtime"] = date("Y-m-d H:i:s");
      $item["ordersid"] = $value["id"];
      $item["putintime"] = date("Y-m-d H:i:s");
      $item["state"] = 1;
      $item["adminid"] = $this->_adminid;
      $savedata[] = $item;
    }
    $biz=new BizModel();
     if($biz->saveAll($savedata)){
       $where=["id"=>["in",$ids]];
       if($type==2){
         if($orders->where($where)->update(["agentno"=>$agentno,"isrecome"=>2])!==FALSE){   //平移成底单
           return ["state"=>"ok","msg"=> $this->_sucmsg];
         }else{
           $where=[];
           $where["ordertype"]=$type;
           $where["ordersid"]=["in",$ids];
           $biz->where($where)->delete();
           return ["state"=>"err","msg"=>$this->_errmsg];
         }
       }else{
         if($orders->where($where)->update(["agentno"=>$agentno,"isrecome"=>0])!==FALSE){  //平移成普通报单
           return ["state"=>"ok","msg"=> $this->_sucmsg];
         }else{
           $where=[];
           $where["ordertype"]=$type;
           $where["ordersid"]=["in",$ids];
           $biz->where($where)->delete();
           return ["state"=>"err","msg"=>$this->_errmsg];
         }
       }
       
     }else{
       $where=[];
       $where["ordertype"]=$type;
       $where["ordersid"]=["in",$ids];
       $biz->where($where)->delete();
       return ["state"=>"err","msg"=> $this->_errmsg];
     }
    
    
  }

  //底单平移处理
  public function movebase() {
    $ids = Request::instance()->param("ids");
    $ids = Arrayutil::stringToArray($ids, ";");
    $ids= array_filter($ids);
    if (empty($ids)) {
      $this->error("信息不完整，不能平移");
    }
    $orders = new OrdersModel();
    $where = ["a.id" => ["in", $ids], "a.state" => 1];
    $list = $orders->orderlist($where, true);
    if (empty($list)) {
      $this->error("报单没有审核，不能平移");
    }
   
    if (count($ids) != count($list)) {
      $this->error("所选数据存在纰漏，不能平移");
    }
    $sortids = [];
    foreach ($list as $key => $value) {
      $sortids[] = $value["sortid"];
    }
    $this->assign("ids", Arrayutil::arrayTostring($ids, ";"));
    $this->assign("sortids", Arrayutil::arrayTostring($sortids, ";"));
    $this->assign("type", 2);
    return $this->fetch();
  }
  
  //普通报单平移到目标代理商名下
  public function movecommon(){
    $ids = Request::instance()->param("ids");
    $ids = Arrayutil::stringToArray($ids, ";");
    $ids= array_filter($ids);
    if (empty($ids)) {
      $this->error("信息不完整，不能平移");
    }
    
    if (empty($ids)) {
      $this->error("信息不完整，不能平移");
    }
    $orders = new OrdersModel();
    $where = ["a.id" => ["in", $ids], "a.state" => 1];
    $list = $orders->orderlist($where, true);
    if (empty($list)) {
      $this->error("数据没有审核，不能平移");
    }
    if (count($ids) != count($list)) {
      $this->error("所选数据存在纰漏，不能平移");
    }
    $sortids = [];
    foreach ($list as $key => $value) {
      $sortids[] = $value["sortid"];
    }
    $this->assign("ids", Arrayutil::arrayTostring($ids, ";"));
    $this->assign("sortids", Arrayutil::arrayTostring($sortids, ";"));
    $this->assign("type", 3);
    return $this->fetch("movebase");
  }
}
