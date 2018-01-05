<?php

namespace app\index\controller;

use think\Request;
use think\Session;
use think\Cache;
use app\common\model\OrdersModel;
use app\common\util\Arrayutil;
use app\common\model\BizModel;
use app\common\model\Ztorders;
use app\common\model\AgentModel;
use app\common\model\FinanceModel;
use app\common\model\OrdersMapModel;
use app\common\model\Ztpointmap;
use app\common\util\CometUtil;

/**
 * Description of Orders
 *
 * @author ytkj
 */
class Orders extends \app\common\controller\HomeCommon {

  var $PLATFORM = [1 => "云联惠", 2 => "其他"];
  var $BIZTYPE = [1, 2, 3, 4];
  var $MONTHGAP = 3; //月份间隔
  var $MONTHLIST=[];
  public function __construct(Request $request = null) {
    parent::__construct($request);
    $monthlist=[];
    for($i=0;$i<= $this->MONTHGAP;$i++){
      $monthbegin = date("Y-m", mktime(0, 0, 0, date("m") - $i, 1, date("Y")));
      $monthend = date("t", strtotime($monthbegin));
      $textdate= date("Y年m月", strtotime("-".$i." month"));
      $text=$textdate."上半月奖金";
      $item=["text"=>$text,"value"=>[$monthbegin.'-1',$monthbegin."-15 23:59:59"]];
      $monthlist[]=$item;
      $text=$textdate."下半月奖金";
      $item=["text"=>$text,"value"=>[$monthbegin.'-16',$monthbegin."-".$monthend.' 23:59:59']];
      $monthlist[]=$item;
    }
    $this->MONTHLIST=$monthlist;
    unset($monthlist);
  }

  //报单中心
  public function index() {
    if (Request::instance()->isPost()) {
      $parm = Request::instance()->param();
      $parm= CometUtil::trimparms($parm);
      $agentno = Session::get("userinfo")["agentno"];
      $order = new OrdersModel();
      $account = intval(Request::instance()->param("remitaccount"));
      for ($i = 0; $i < ceil($account / 10000); $i++) {
        $temp["agentno"] = $agentno;
        $temp["putintime"] = date("Y-m-d H:i:s");
        foreach ($parm as $key => $value) {
          if ($key == "images") {
            $value = Arrayutil::arrayTostring($value, ";");
            $temp["remitimg"] = $value;
          } else if ($key == "remitaccount") {
            if($value%10000===0){
              $temp[$key] = 10000;
            }else if($value%9999===0){
              $temp[$key] = 9999;
            }else  if($value%9998===0){
              $temp[$key] = 9998;
            }else{
              return ["state"=>"err","msg"=>"入单金额不合法，请输入正确的报单金额"];
            }
          } else {
            $temp[$key] = $value;
          }
        }
        $orders[] = $temp;
      }
      $result = $order->saveAll($orders);
      if ($result) {
        return ["state" => "ok", "msg" => "恭喜您报单成功,请等待管理员审核", "tourl" => url("orders/main")];
      } else {
        return ["state" => "err", "msg" => "系统繁忙，请稍后再试！"];
      }
    } else {
      $this->assign("name", "报单中心");
      $uploadid = md5(uniqid());
      $savepath = Request::instance()->param('savepath');
      $savepath = $savepath ? $savepath : "util";
      Session::set("uploadid", $uploadid);
      $this->assign("uploadid", $uploadid);
      $this->assign("savepath", $savepath);
      $this->assign("flatfrom", $this->PLATFORM);
      return $this->fetch();
    }
  }

  //待审核的报单
  public function main() {
    $order = new OrdersModel();
    $agentno = Session::get("userinfo")["agentno"];
    $this->assign("name", "待审核的报单");
    $where = [
        "a.state" => ["eq", 0],
        "a.agentno" => ["eq", $agentno],
    ];
    $list = $order->mainlist($where, 10);
    $page = $list->render();
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      return $this->fetch();
    }
  }

  //已审核的报单
  public function mainlist() {
    $order = new OrdersModel();
    $this->assign("name", "已审核的报单");
    $agentno = Session::get("userinfo")["agentno"];
    $where = [
        "a.isincar" => ["eq", 0],
        "a.isrecome" => ["eq", 0],
        "a.state|a.isspecial" => ["eq", 1],
        "a.agentno" => ["eq", $agentno],
    ];
    $list = $order->mainlist($where, 10);
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      return $this->fetch();
    }
  }

  //直通车，代理底单业务处理（入库）
  public function doorders() {
    if (Request::instance()->isPost()) {
      $agentno = Session::get("userinfo")["agentno"];
      $type = Request::instance()->param("type"); //1直通车，2代理底单
      $ids = Request::instance()->param("ids");
      $ids = Arrayutil::stringToArray($ids, ";");
      $ids = array_filter($ids);
      $temp = Arrayutil::arrayTostring($ids, ",");
      $incometype = Request::instance()->param("actiontype");
      $order = new OrdersModel();
      $ztid = "";

      //检查是否存在10个底单
      $agent = new AgentModel();
      $biz = new BizModel();
      if (!$agent->checkAgent($agentno)) {
        $count = (new OrdersModel())->where("agentno='$agentno' and isrecome=2")->count();
        if ($count < 10 && $type == "1") {//直通车，代理底单不足处理逻辑
          return ["state" => "err", "msg" => "对不起，您的代理底单数目不足，无法申请", "tourl" => url("orders/mainlist")];
        } else if ($count < 10 && $type == "2") {//推荐新代理， 代理底单不足处理逻辑
          $bizlist = BizModel::where(["ordersid" => ["in", $ids]])->select();
          if (!empty($bizlist)) {
            return ["state" => "err", "msg" => "所选报单，存在已经申请过的报单，不能申请", "tourl" => url("orders/mainlist")];
          }
          //补全代理底单
          $ids = Arrayutil::sub_array($ids, 0, 10 - $count);

          $where = ["id" => ["in", $ids]];
          $savedata = ["isrecome" => 2];
          $order->where($where)->update($savedata);

          $orders = [];
          foreach ($ids as $ordersid) {
            $orders[] = [
                "agentno" => $agentno,
                "ordertype" => $type,
                "ordersid" => $ordersid,
                "putintime" => date("Y-m-d H:i:s"),
                "incometype" => $incometype,
                "state" => 0,
            ];
          }
          $result = $biz->saveAll($orders);
          if ($result) {
            return ["state" => "ok", "msg" => "恭喜您操作成功", "tourl" => url("orders/mainlist")];
          } else {
            return ["state" => "err", "msg" => "系统繁忙，请稍后再试！"];
          }
        }
      }

      $bizlist = BizModel::where(["ordersid" => ["in", $ids],"state"=>"1"])->select();
      if (!empty($bizlist)) {
        return ["state" => "err", "msg" => "所选报单，存在已经申请过的报单，不能申请", "tourl" => url("orders/mainlist")];
      }


      if ($type == "1") {
        $ztorder = new Ztorders();
        $ztorder->agentno = $agentno;
        $ztorder->putintime = date("Y-m-d");
        $ztorder->state = 0;
        $ztorder->save();
        $ztid = $ztorder->id;
        $where = ["id" => ["in", $ids]];
        $savedata = ["isincar" => 1];
        $order->where($where)->update($savedata);
      }
      $orders = [];
      foreach ($ids as $ordersid) {
        $orders[] = [
            "agentno" => $agentno,
            "ordertype" => $type,
            "ordersid" => $ordersid,
            "putintime" => date("Y-m-d H:i:s"),
            "incometype" => $incometype,
            "state" => 0,
            "ztid" => $ztid
        ];
      }
      $tourl = ($type == "1") ? url("orders/cars") : url("index/reg", ["agentparent" => $agentno]);
      $result = $biz->saveAll($orders);
      if ($result) {
        Session::set("NewUserOrders", $temp);
        return ["state" => "ok", "msg" => "恭喜您操作成功", "tourl" => $tourl];
      } else {
        return ["state" => "err", "msg" => "系统繁忙，请稍后再试！"];
      }
    }
  }

  //直通车
  public function cars() {
    $order = new Ztorders();
    $datatype = Request::instance()->param("datatype", "1", "intval");
    $agentno = Session::get("userinfo")["agentno"];
    $this->assign("name", "我的直通车");
    $where = [
        "a.agentno" => ["eq", $agentno],
    ];
    if ($datatype == 2) {
      $where["a.state"] = ["in", [0, 3]];
    }
    if ($datatype == 4) {
      $where["a.state"] = ["eq", 1];
    }
    $list = $order->ztlist($where, 10);
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      $this->assign("datatype", $datatype);
      return $this->fetch();
    }
  }

  public function sendztorder() {
    if (Request::instance()->isPost()) {
      $parm = Request::instance()->param();
      $ztid = Request::instance()->param("ztid");
      $resulttype = Request::instance()->param("resulttype");
      $pointaccount = Request::instance()->param("pointaccount");
      $futoutype= Request::instance()->param("futoutype");
      $cacheName = "SendsOrderCache";
      if(Cache::has($cacheName)){ //锁
        return ;
      }
      $ztorder = Ztorders::get(["id" => $ztid]);
      $futoutype = ($ztorder->futoutype==0)?$futoutype:$ztorder->futoutype;
      if (empty($ztorder)) {
        return ["state" => "err", "msg" => "非法的直通车凭据"];
      }
      if ($ztorder->reulttype != 0) {
        $temptype = $ztorder->reulttype;
        if ($temptype != $resulttype) {
          return ["state" => "err", "msg" => "奖励获取方式和之前申报的方式不相同，请重新选择"];
        }
      }
      if ($resulttype == 1) {
        $result = 0;
      } elseif ($resulttype == 2) {
        $result = 250000;
      } else {
        $result = 350000;
      }  
      $ztmap = new Ztpointmap();
      $sumacc=0;
      if ($resulttype != 1) {
        if ($pointaccount > $result) {
          return ["state" => "err", "msg" => "输入的积分额度大于可以分配的积分额度"];
        }
        $sumacc = $ztmap->where(["ztid" => $ztid])->sum("pointaccount");
        if ($sumacc >= $result) {
          return ["state" => "err", "msg" => "积分已经分配完毕，不能再分配了"];
        }
        $temp = $sumacc + $pointaccount;
        $point=$temp+$futoutype*100000;
        if ($point > $result) {
          $point-=$pointaccount;
          return ["state" => "err", "msg" => "输入的积分额度大于可以分配的积分额度，最多可以分配" . ($result - $point) . "积分"];
        }
        if($futoutype!=0){ //复投处理逻辑
          $maxcount = $result-$temp/100000;
          if($maxcount<$futoutype){
            return  ["state"=>"err","msg"=>"所剩积分 不足以复投报单"];
          }
        }
      }
      Cache::set($cacheName, $ztorder);
      unset($parm["futoutype"]);
      foreach ($parm as $key => $value) {
        if ($key == "images") {
          $ztmap->voucherimg = Arrayutil::arrayTostring($value, ";");
        } else {
          $ztmap->$key = $value;
        }
      }
    
      if ($ztmap->save()) {
        $state = (($sumacc + $pointaccount+$futoutype*100000) == $result) ? 2 : 3;
        $ztorder->state = $state;
        if ($ztorder->reulttype == 0) {
          $ztorder->reulttype = $resulttype;
        }
        if($ztorder->futoutype == 0){
          $ztorder->futoutype = $futoutype;
        }
        if($ztorder->save()){
          $result -= ($sumacc + $pointaccount+$futoutype*100000);
          Cache::rm($cacheName);
          return ["state" => "ok", "msg" => "操作成功！还有" . $result . "可以分配", "result" => $result];
        }else{
          return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
        }
      } else {
        return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
      }
    } else {
      $ztid = Request::instance()->param("ztid");
      $type = Request::instance()->param("type");
      $uploadid = md5(uniqid());
      $savepath = Request::instance()->param('savepath');
      $savepath = $savepath ? $savepath : "util";
      Session::set("uploadid", $uploadid);
      $this->assign("uploadid", $uploadid);
      $this->assign("savepath", $savepath);
      $this->assign("ztid", $ztid);
      $this->assign("type", $type);
      return $this->fetch();
    }
  }

  //我的底单
  public function baseorder() {
    $order = new OrdersModel();
    $agentno = Session::get("userinfo")["agentno"];
    $this->assign("name", "我的底单");
    $where = [
        "a.isrecome" => ["eq", 2],
        "a.state" => ["eq", "1"],
        "a.agentno" => ["eq", $agentno],
    ];
    $list = $order->orderlist($where, 10);
    $page = $list->render();
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      return $this->fetch();
    }
  }

  //代理底单
  public function baselist() {
    $order = new OrdersModel();
    $agentno = Session::get("userinfo")["agentno"];
    $datatype = Request::instance()->param("datatype", "1");
    $this->assign("name", "代理推荐");

    $where = [
        "a.weekend" => ["eq", 0],
        "a.isincar" => ["eq", 0],
        "a.state" => ["eq", "1"],
        "a.agentno" => ["eq", $agentno],
    ];
    if ($datatype == 2) {
      $where["a.isrecome"] = ["eq", "1"];
    }
    if ($datatype == 3) {
      $where["a.isrecome"] = ["eq", "0"];
    }

    $list = $order->orderlist($where, 10);
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      $this->assign("datatype", $datatype);
      return $this->fetch();
    }
  }

  //个人中心
  public function personcenter() {
    $this->assign("name", "个人中心");
    return $this->fetch();
  }
 
  //我的报单
  public function orderscenter(){
    $this->assign("name","我的报单");
    return $this->fetch();
  }
  
  //报单查询
  public function search(){
    if(Request::instance()->isPost()){
      $keyword= Request::instance()->param("keyword");
      $where=[
          "a.agentno"=> $this->agentno,
          "a.remituser|a.bankuser"=>["like","%$keyword%"]
      ];
      $orders=new OrdersModel();
      $list = $orders->mainlist($where, 15);
      return $list->toArray();
    }else{
       $this->assign("name","报单查询");
       return $this->fetch();
    }
  }

  //被驳回的报单
  public function refuselist() {
    $this->assign("name", "已驳回的报单");
    $order = new OrdersModel();
    $agentno = Session::get("userinfo")["agentno"];
    $where = [
        "a.state" => ["eq", -1],
        "a.agentno" => ["eq", $agentno],
    ];
    $list = $order->orderlist($where, 10);
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      return $this->fetch();
    }
  }

  //我的推荐下级
  public function recomelist() {
    $this->assign("name", "我的推荐");
    $agent = new AgentModel();
    $agentno = Session::get("userinfo")["agentno"];
    $where = [
        "a.state" => ["eq", 1],
        "a.agentparent" => ["eq", $agentno],
    ];
    $list = $agent->Agentlist($where, 10);
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      return $this->fetch();
    }
  }

  //我的业绩
  public function myperformance() {
    $this->assign("name", "我的业绩");
    $agentno = Session::get("userinfo")["agentno"];
    $agent = AgentModel::get(["agentno" => $agentno]);
    $cachename = "performance_" . $agentno;
    if (Cache::has($cachename)) {
      $result = Cache::get($cachename);
    } else {
      $result = [];
      $where = [
          "agentno" => $agentno,
          "state" => 1,
      ];
      $orderscount = OrdersModel::where($where)->count(); //总报单

      $where = [
          "agentno" => $agentno,
          "state" => 1,
          "financeend" => 1,
          "isrecome"=>0,
      ];
      $weekcount = OrdersModel::where($where)->count(); //已发奖励
      $where = [
          "agentno" => $agentno,
          "state" => 1,
          "financeend" => 0,
          "isrecome"=>0,
      ];
      $monthcount = OrdersModel::where($where)->count(); //未发奖励
     
      $result["Send"] = $weekcount * 1000;
 
      //
      //提成
      $where = [
          "pagentno" => $agentno,
      ];
      $ticheng = FinanceModel::where($where)->sum("paccount");
      $result["TiCheng"] = intval($ticheng);

      //代理推代理
      $where = [
          "agentno" => $agentno,
          "biztype" => 3
      ];
      $zhitui = FinanceModel::where($where)->sum("account");
      $result["ZhiTui"] = intval($zhitui);

      if ($agent->isvip == 1) {//如果是超级代理
        $result["All"] = $orderscount * 1000;
        $result["NoSend"] = $monthcount * 1000;
      } else {//如果是普通代理
        if ($orderscount>=10) {
          $result["All"] = ($orderscount - 10) * 1000;
          $result["NoSend"] = ($monthcount ) * 1000;
        } else {
          $result["All"] = 0;
          $result["NoSend"] = 0;
        }
      }
      Cache::set($cachename, $result, 300);
    }

    $this->assign("result", $result);

    return $this->fetch();
  }

  //个人信息
  public function myinfo() {
    $this->assign("name", "个人信息");
    $agentno = Session::get("userinfo")["agentno"];
    $cacheName = "Agent_Info_" . $agentno;
    $agent = new AgentModel();
    if (Cache::has($cacheName)) {
      $info = Cache::get($cacheName);
    } else {
      $info = $agent->Agentinfo($agentno);
      Cache::set($cacheName, $info, 300);
    }
    $this->assign("info", $info);
    return $this->fetch();
  }

  //我的出彩单
  public function endorders() {
    $this->assign("name", "我的出彩单");
    $datatype = Request::instance()->param("datatype", 1);
    $order = new OrdersModel();
    $agentno = Session::get("userinfo")["agentno"];
    $where = [
        "a.state" => ["eq", 1],
        "a.agentno" => ["eq", $agentno],
    ];
    if ($datatype == 1) {
      $where["a.issend"] = ["neq", 0];
    } else if ($datatype == 2) {
      $where["a.issend"] = ["eq", 1];
    } else if ($datatype == 3) {
      $where["a.issend"] = ["eq", 2];
    } else {
      $where["a.issend"] = ["eq", 3];
    }

    $list = $order->mainlist($where, 10);
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      $this->assign("datatype", $datatype);
      return $this->fetch();
    }
  }

  //我的复投单
  public function redellist() {
    $this->assign("name", "我的复投单");
    $order = new OrdersModel();
    $agentno = Session::get("userinfo")["agentno"];
    $where = [
        "a.isredelivery" => ["eq", 1],
        "a.agentno" => ["eq", $agentno],
    ];
    $list = $order->mainlist($where, 10);
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      return $this->fetch();
    }
  }

  public function doredelivery() {
    $ids = Request::instance()->param("ids", "");
    $biztype = Request::instance()->param("biztype", 1, "intval"); //复投方式  1，要车 ，2 复投一单 ，3复投2单，4，复投3单
    if (!in_array($biztype, $this->BIZTYPE)) {
      return ["state" => "err", "msg" => "非法的参数请求，不予处理"];
    }
    $orders = new OrdersModel();
    if (strlen($ids) == 0) {
      return ["state" => "err", "msg" => "请选择需要复投的报单"];
    }
    $ids = Arrayutil::stringToArray($ids, ";");
    $ids = array_filter($ids);
    if (count($ids) > 10) {
      $ids = Arrayutil::sub_array($ids, 0, 10);
    }

    if (!empty($ids)) {
      $where = [
          "a.issend" => "1",
          "a.state" => "1",
      ];
      $where["a.id"] = ["in", $ids];
      $list = $orders->orderlist($where, TRUE); //获取复投单的信息
    } else {
      return ["state" => "err", "msg" => "请选择需要复投的报单"];
    }

    if ($biztype != 1) {//此处处理复投单   //如果是要车， 则报单终止，直接出彩在最后处理    
      $count = intval($biztype) - 1;
      for ($i = 0; $i < $count; $i++) {
        foreach ($list as $item) {
          $baseid = $item->id;
          $ordersmap = OrdersMapModel::get(["parentorderid" => $baseid]);
          $item->bankuser = "排单编号:$baseid的积分复投";
          $orderid = $orders->addredelivery($item, $i);
          $itemmap = new OrdersMapModel();
          if (empty($ordersmap)) {
            $itemmap->baseorderid = $baseid;
          } else {
            $itemmap->baseorderid = $ordersmap->baseorderid;
          }
          $itemmap->parentorderid = $baseid;
          $itemmap->orderid = $orderid->id;
          $itemmap->isspecial = $orderid->isspecial;
          $itemmap->lettertype = $biztype;
          $itemmap->save();
        }
      }
    }
    if (!empty($ids)) {
      if ($orders->alias("a")->where($where)->update(["issend" => "3", "resulttype" => $biztype])) {
        return ["state" => "ok", "msg" => "申请成功，请等待正式出彩通知"];
      } else {
        return ["state" => "err", "msg" => "系统繁忙，清稍后再试"];
      }
    }
  }
  
  public function bonuslist(){
    $this->assign("name", "我的奖金账单");
    $this->assign("list", $this->MONTHLIST);  
    return $this->fetch();
    
  }
  
  public function bonusinfo(){
    $index= Request::instance()->param("index");
    $tempinfo= $this->MONTHLIST[$index];
  
    $this->assign("name",$tempinfo["text"]);
    $where=[
        "a.agentno"=> $this->agentno,
        "a.state"=>1,
        "a.financeend"=>1,
        "a.entrytime"=>["between",$tempinfo["value"]],
        "a.isputin"=>0,
        "isrecome"=>0
    ];
    $orders=new OrdersModel();
    $list= $orders->sortList($where, 15);
    $this->assign("index",$index);
    if (Request::instance()->isAjax()) {
      return $list->toArray();
    } else {
      $this->assign("pageinfo", $list->toArray());
      return $this->fetch();
    }       
  }
}
