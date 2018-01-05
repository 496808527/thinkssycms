<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\controller;

use app\common\controller\AdminCommon;
use app\common\model\OrdersModel;
use app\common\model\AgentModel;
use app\common\util\Arrayutil;
use app\common\model\BizModel;
use app\common\model\Ztorders;
use app\common\model\FinanceModel;
use app\common\model\ReceiptModel;
use app\common\model\Ztpointmap;
use app\common\util\CometUtil;
use think\Request;
use think\Cache;

/**
 * Description of Finance
 *
 * @author ytkj
 */
class Finance extends AdminCommon {

  private $platform = ["1" => "云联惠", "2" => "其他"];
  private $statelist = ["1" => "已到账", "0" => "未到账", "-1" => "无效"];
  private $biztype = ["1" => "周奖金", "2" => "月度奖金", "3" => "直推奖励"];
  private $reulttype = ["1" => "车", "2" => "25W积分", "3" => "35W积分", "0" => "未知"];
  private $ztstate = ["1" => "已处理", "0" => "待处理", "2" => "已提交，待审核", "3" => "积分分配未完成"];
  private $futoutype=["1"=>"复投一单","0"=>"没有复投","2"=>"复投两单","3"=>"复投三单","4"=>"此单已经复投"];
  private $adminid;
  private $financeday;

  public function __construct(\think\Request $request = null) {
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
    $this->adminid = \think\Session::get("adminuser");
    $this->assign("futoutype", $this->futoutype);
    $this->assign("statelist", $this->statelist);
    $this->assign("ztstate", $this->ztstate);
    $this->assign("platform", $this->platform);
    $this->assign("resulttype", $this->reulttype);
  }

  //周奖励发放订单列表（非代理底单）
  public function week() {
    $username = Request::instance()->param("agentno", "", "trim");
    $begintime = Request::instance()->param("begintime", "");
    $endtime = Request::instance()->param("endtime", "");
    $order = new OrdersModel();
    $where = [
        "a.entrytime" => ["BETWEEN", $this->week],
        "a.state" => 1,
        "a.isrecome" => 0,
        "a.weekend" => 0
    ];
    
    $orders = new OrdersModel();
    if (Request::instance()->isPost()) {
      if (strlen($username)) {
        $where["a.agentno|b.agentname|b.id"] = ["eq", $username];
      }
    }
    $list = $order->financeorder($where, 15);
    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    $this->assign("flatform", $this->platform);
    $this->assign("agentno", $username);
    $this->assign("begintime", $begintime);
    $this->assign("endtime", $endtime);
    return $this->fetch();
  }

  //奖金发放总会
  public function FinanceSend() {
    $id = Request::instance()->param("id");
    $id = Arrayutil::stringToArray($id, ";");
    $agent = new AgentModel();
    if (count($id) != 1 && !empty($id)) {
      $order = new OrdersModel();
      $where = [
          "id" => ["in", $id]
      ];
      $orderlist = $order->where($where)->group("agentno,remark")->field("count(1) totalcount,agentno,remark")->select();

      foreach ($orderlist as $temp) {
        $agentno = $temp["agentno"];
        $agentinfo = $agent->AgentMoney($agentno, $temp["totalcount"], $temp["remark"]);
        $list[] = $agentinfo;
      }
    } else if (count($id) === 1 && !empty($id)) {
      $order = OrdersModel::get($id);
      if (empty($order)) {
        $this->error("参数错误，请联系管理员，核对数据");
      }
      $agentno = $order["agentno"];
      $list[] = $agent->AgentMoney($agentno, 1, $order["remark"]);
    } else {
      $cacheName = "WeekFinance" . $this->financeday[0];
      if (Cache::has($cacheName)) {
        $list = Cache::get($cacheName);
      } else {
        $order = new OrdersModel();
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
    }
    $this->assign("list", $list);
    $id = Arrayutil::arrayTostring($id, ";");
    $this->assign("id", $id);
    return $this->fetch();
  }

  public function doFinanceSend() {
    $id = Request::instance()->param("orderid");
    $id = Arrayutil::stringToArray($id, ";");
    $order = new OrdersModel();
    $agent = new AgentModel();
    $cacheName = "WeekFinance" . $this->financeday[0];
    if (empty($id)) {
      $where = [
          "a.entrytime" => ["BETWEEN", $this->financeday],
          "a.state" => 1,
          "a.isrecome" => 0,
          "a.financeend" => 0
      ];
    } else {
      $where = [
          "id" => ["in", $id]
      ];
    }

    $orderlist = $order->alias("a")->where($where)->select();
    $result = [];
    $datalist = [];
    foreach ($orderlist as $temp) {
      $agentno = $temp["agentno"];
      if ($agent->checkAgent($agentno)) {
        $result[] = $temp["id"];
        $moneyinfo = $agent->AgentMoney($agentno, 1);
        $datalist[] = [
            "orderid" => $temp["id"],
            "agentno" => $temp["agentno"],
            "biztype" => 1,
            "account" => $moneyinfo->account,
            "biztime" => date("Y-m-d H:i:s"),
            "adminid" => $this->adminid,
            "pagentno" => $moneyinfo->agentparent,
            "paccount" => $moneyinfo->paccount,
        ];
      }
    }
    $finance = new \app\common\model\FinanceModel();
    if (count($datalist) != 0) {
      if ($finance->saveAll($datalist)) {
        $where = ["id"=>["in", $result]];
        if ($order->where($where)->update(["financeend" => 1]) !== false) {
          Cache::rm($cacheName);
          return 1;
        } else {
          $finance->where(["orderid" => ["in", $result], "biztype" => 1])->delete();
          return "系统繁忙，稍后再试";
        }
      } else {
        return "系统繁忙，稍后再试";
      }
    } else {
      return "代理商底单不足10个，不能发放奖励";
    }
  }

  //奖金发放记录
  public function bonuslist() {
    $sessionid = session_id();
    $parm = Request::instance()->param();
    $pageno = Request::instance()->param("page");
    $finance = new \app\common\model\FinanceModel();


    $cacheName = "Cache_BonusList_Where_" . $sessionid;
    if (!empty($pageno)) {
      if (Cache::has($cacheName)) {
        $where = Cache::get($cacheName);
      } else {
        $where = [];
      }
    } else {
      Cache::rm($cacheName); //此处必须删除，否则会影响分页结果
      $where = [];
    }

    if (Request::instance()->isPost()) {
      $_REQUEST["page"] = 1;
      if (strlen($parm["agentno"])) {
        $where["a.agentno|b.agentname|b.id"] = ["eq", $parm["agentno"]];
      }
      if (strlen($parm["begintime"]) && strlen($parm["endtime"])) {
        $iend = max(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $istart = min(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $begintime = date("Y-m-d ", $istart) . "00:00:00";
        $endtime = date("Y-m-d ", $iend) . "23:59:59";
        $where["a.biztime"] = ["between", [$begintime, $endtime]];
      }
      Cache::set($cacheName, $where);
    } else {
      if (isset($where["a.agentno|b.agentname|b.id"]))
        $parm["agentno"] = $where["a.agentno|b.agentname|b.id"][1];
      if (isset($where["a.biztime"])) {
        $parm["begintime"] = $where["a.biztime"][1][0];
        $parm["endtime"] = $where["a.biztime"][1][1];
      }
    }
    $list = $finance->financelist($where, 15);
    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    $this->assign("parm", $parm);
    $this->assign("biztype", $this->biztype);
    return $this->fetch();
  }

  /**
   * 代理底单申报记录
   */
  public function recome() {
    $sessionid = session_id();
    $parm = Request::instance()->param();
    $pageno = Request::instance()->param("page");
    $biz = new BizModel();

    $cacheName = "Cache_Recome_Where_" . $sessionid;
    if (!empty($pageno)) {
      if (Cache::has($cacheName)) {
        $where = Cache::get($cacheName);
      } else {
        $where = [
            "a.ordertype" => 2,
            "a.state" => 1,
        ];
      }
    } else {
      Cache::rm($cacheName); //此处必须删除，否则会影响分页结果
      $where = [
          "a.ordertype" => 2,
          "a.state" => 1
      ];
    }
    if (Request::instance()->isPost()) {
      if (strlen(trim($parm["agentno"]))) {
        $where["a.agentno|b.id|b.agentname"] = ["eq", trim($parm["agentno"])];
      }
      if (strlen(trim($parm["begintime"])) && strlen(trim($parm["endtime"]))) {
        $iend = max(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $istart = min(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $begintime = date("Y-m-d ", $istart) . "00:00:00";
        $endtime = date("Y-m-d ", $iend) . "23:59:59";
        $where["a.putintime"] = ["between", [$begintime, $endtime]];
      }
      Cache::set($cacheName, $where);
    } else {
      if (isset($where["a.agentno|b.id|b.agentname"]))
        $parm["agentno"] = $where["a.agentno|b.id|b.agentname"][1];
      if (isset($where["a.putintime"])) {
        $parm["begintime"] = $where["a.biztime"][1][0];
        $parm["endtime"] = $where["a.biztime"][1][1];
      }
    }
    $list = $biz->bizlist($where, 10);
    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    $this->assign("flatform", $this->platform);
    $this->assign("parm", $parm);
    $this->assign("statelist", $this->statelist);
    return $this->fetch();
  }

  /**
   * 发放代理底单推荐奖励
   */
  public function sendbase() {
    $id = Request::instance()->param("orderid");
    $id = Arrayutil::stringToArray($id, ";");
    $biz = new BizModel();
    $where = [
        "id" => ["in", $id],
    ];

    $list = $biz->group("agentno")->where($where)->field("count(1) totalcount,agentno")->select();
    if (count($list) != 1) {
      return ["state" => "err", "msg" => "所选的报单，不是同一个代理商的直推单，不能发放奖励"];
    }

    if ($biz->where($where)->update(["state" => 2, "handtime" => date("Y-m-d H:i:s"), "adminid" => $this->adminid])) {
      $fin = new FinanceModel();
      $datalist = [
          "agentno" => $list[0]["agentno"],
          "biztype" => 3,
          "account" => 10000,
          "biztime" => date("Y-m-d H:i:s"),
          "adminid" => $this->adminid,
          "orderslist" => Arrayutil::arrayToJson($id)
      ];
     if( $fin->save($datalist)){
      return ["state" => "ok", "msg" => "发放成功！"];
     }else{
       return ["state" => "ok", "msg" => $this->_errmsg];
     }
    } else {
      return ["state" => "err", "msg" => "系统繁忙，稍后再试"];
    }
  }

  //直通车订单申报记录
  public function carlist() {
    $username = Request::instance()->param("agentno", "", "trim");
    $begintime = Request::instance()->param("begintime", "");
    $endtime = Request::instance()->param("endtime", "");
    $biz = new Ztorders();
    $where = ["a.state"=>["neq",1]];
    if (strlen($username)) {
      $where["a.agentno|c.id|c.agentname"] = ["eq", $username];
    }
    
    if (strlen($begintime) && strlen($endtime)) {
      $iend = max(strtotime($begintime), strtotime($endtime));
      $istart = min(strtotime($begintime), strtotime($endtime));
      $begintime = date("Y-m-d ", $istart) . "00:00:00";
      $endtime = date("Y-m-d ", $iend) . "23:59:59";
      $where["a.putintime"] = ["between", [$begintime, $endtime]];
    }
    $list = $biz->ztlist($where, 15);
    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    
    $this->assign("agentno", $username);
    $this->assign("begintime", $begintime);
    $this->assign("endtime", $endtime);
    
    return $this->fetch();
  }

  //直通车积分处理逻辑
  public function docarlist() {
    $id = Request::instance()->param("id", 0, "intval");
    $info = Ztorders::get($id);
    if (empty($info)) {
      $this->error("非法的参数传入");
    }
    if($info->state<=1){
      $this->error("此状态下的直通车，无需处理,需要等用户填写积分分配方案");
    }
    $biz = new BizModel();
    $where = [
        "a.ztid" => $id,
        "a.state" => 1
    ];
    $baselist = $biz->bizlist($where, TRUE);
    $this->assign("baselist", $baselist);
    $list = Ztpointmap::all(["ztid" => $id]);
    $totalaccount = (new Ztpointmap())->where("ztid=$id")->sum("pointaccount");
    $this->assign("totalaccount", $totalaccount);
    $this->assign("list", $list);
    $this->assign("ztinfo", $info);
    return $this->fetch();
  }

  public function dopoint() {
    $ztid = Request::instance()->param("ztid");
    $ztinfo = Ztorders::get($ztid);
    $ztinfo->state = 1;
    if ($ztinfo->save()) {
      return ["state" => "ok", "msg" => "操作成功"];
    } else {
      return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
    }
  }

  //收据管理
  public function recelist() {
    $username = Request::instance()->param("agentno", "", "trim");
    $begintime = Request::instance()->param("begintime", "");
    $endtime = Request::instance()->param("endtime", "");
    $order = new ReceiptModel();
    $where = [];
    if (Request::instance()->isPost()) {
      if (strlen($username)) {
        $where["a.receiptno"] = ["eq", $username];
      }
      $iend = max(strtotime($begintime), strtotime($endtime));
      $istart = min(strtotime($begintime), strtotime($endtime));
      if (strlen($begintime) && strlen($endtime)) {
        $begintime = date("Y-m-d ", $istart) . "00:00:00";
        $endtime = date("Y-m-d ", $iend) . "23:59:59";
        $where["a.entrytime"] = ["between", [$begintime, $endtime]];
      }
    }
    $list = $order->recelist($where, 15);
    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    $this->assign("flatform", $this->platform);
    $this->assign("agentno", $username);
    $this->assign("begintime", $begintime);
    $this->assign("endtime", $endtime);
    $this->assign("statelist", $this->statelist);
    return $this->fetch();
  }

  public function printorder() {
    $id = Request::instance()->param("id");
    $receinfo = (new ReceiptModel())->receinfo(["a.id" => $id]);
    $this->assign("orders", $receinfo);
    return $this->fetch();
  }

  //报单奖金发放列表
  public function FinanceList() {
    $sessionid = session_id();
    $parms = Request::instance()->param();
    $parms = CometUtil::trimparms($parms);
    $pageno = Request::instance()->param("page");
    $cacheName = "Cache_FinanceList_Where_" . $sessionid;
    if (!empty($pageno)) {
      if (Cache::has($cacheName)) {
        $where = Cache::get($cacheName);
      } else {
        $where = [
            "a.entrytime" => ["BETWEEN", $this->financeday],
            "a.state" => 1,
            "a.isrecome" => 0,
            "a.financeend" => 0
        ];
      }
    } else {
      Cache::rm($cacheName); //此处必须删除，否则会影响分页结果
      $where = [
          "a.entrytime" => ["BETWEEN", $this->financeday],
          "a.state" => 1,
          "a.isrecome" => 0,
          "a.financeend" => 0
      ];
    }
    $orders = new OrdersModel();
    if (Request::instance()->isPost()) {
      $_REQUEST["page"]=1;
      if (strlen($parms["agentno"])) {
        $where["a.agentno|b.agentname|b.id"] = ["eq", $parms["agentno"]];
      }
      Cache::set($cacheName, $where);
    } else {
      if (isset($where["a.agentno|b.agentname|b.id"])) {
        $parms["agentno"] = $where["a.agentno|b.agentname|b.id"][1];
      }
    }
    
    $list = $orders->financeorder($where, 15);
    $page = $list->render();
    $this->assign("parm", $parms);
    $this->assign("list", $list);
    $this->assign("page", $page);
    return $this->fetch();
  }
  


}
