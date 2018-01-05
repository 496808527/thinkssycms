<?php

namespace app\admin\controller;

use app\common\controller\AdminCommon;
use app\common\model\OrdersModel;
use app\common\util\Arrayutil;
use app\common\model\User;
use app\common\util\CometUtil;
use app\common\model\ReceiptModel;
use app\common\util\SmsSend;
use app\common\model\AgentModel;
use app\common\model\OrdersmsModel;
use app\common\model\OrdersMapModel;
use app\common\util\stringutil;
use app\common\model\Ztorders;
use think\Request;
use think\Cache;

class Orders extends AdminCommon {

  private $platform = ["1" => "云联惠", "2" => "其他"];
  private $statelist = ["1" => "已审核", "-1" => "已拒绝", "0" => "待审核"];
  private $searchstate = ["1" => "已审核", "-1" => "已拒绝", "2" => "待审核"];
  private $isrecome = ["1" => "标记", "0" => "普通订单", "2" => "底单"];
  private $biztype = ["1" => "要车", "2" => "复投一单", "3" => "复投两单", "4" => "复投三单"];
  private $putin = ["2" => "代理提交", "1" => "后台导入"];
  private $isredelivery = ["2" => "初始单", "1" => "复投单"];
  private $recome = ["1" => "标记", "2" => "底单"];
  private $adminid;

  public function __construct(\think\Request $request = null) {
    parent::__construct($request);
    $this->adminid = \think\Session::get("adminuser");
    $this->assign("putinlist", $this->putin);
    $this->assign("recomelist", $this->recome);
    $this->assign("redellist", $this->isredelivery);
    $this->assign("searchstate", $this->searchstate);
    $this->assign("platform", $this->platform);
    $this->assign("isrecome", $this->isrecome);
    $this->assign("statelist", $this->statelist);
  }

  public function index() {
    $pageno = Request::instance()->param("page");
    $parm = Request::instance()->param();
    $orders = new OrdersModel();
    $sessionid = session_id();
    $cachename = "ordersindex_where_$sessionid";
    $cacheexport = "cacheexport_where_" . $sessionid;
    foreach ($parm as $key => $value) {
      $parm[$key] = trim($value);
    }

    if (!empty($pageno)) {
      if (Cache::has($cachename)) {
        $where = Cache::get($cachename);
      } else {
        $where["a.state"] = 0;
        $where["a.isredelivery"] = 0;
        $where["a.isspecial"] = 0;
      }
    } else {
      Cache::rm($cachename); //此处必须删除，否则会影响分页结果
      Cache::rm($cacheexport); //此处必须删除，否则会影响分页结果
      $where["a.state"] = 0;
      $where["a.isredelivery"] = 0;
      $where["a.isspecial"] = 0;
    }
    if (Request::instance()->isPost()) {
      $_REQUEST["page"] = 1;
      $where = [];
      if (strlen($parm["agentno"])) {
        $where ["a.agentno|b.agentname|b.id"] = ["eq", trim($parm["agentno"])];
      }
      if (strlen($parm["begintime"]) && strlen($parm["endtime"])) {
        $iend = max(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $istart = min(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $parm["begintime"] = date("Y-m-d ", $istart) . "00:00:00";
        $parm["endtime"] = date("Y-m-d ", $iend) . "23:59:59";
        $where["a.putintime"] = ["between", [$parm["begintime"], $parm["endtime"]]];
      }
      if (strlen($parm["state"])) {
        $itemstate = ($parm["state"] == 2) ? 0 : $parm["state"];
        $where["a.state"] = $itemstate;
      }
      if (strlen($parm["bankuser"])) {
        $where["a.bankuser"] = ["like", "%" . $parm["bankuser"] . "%"];
      }
      if (strlen($parm["remituser"])) {
        $where["a.remituser"] = ["like", "%" . $parm["remituser"] . "%"];
      }
      if (strlen($parm["sortid"])) {
        $where["a.sortid"] = ["eq", $parm["sortid"]];
      }
      if (strlen($parm["agentarea"])) {
        $where["b.agentarea"] = ["like", "%" . $parm["agentarea"] . "%"];
      }

      Cache::set($cachename, $where);
      Cache::set($cacheexport, $where, 120);
    } else {
      if (!isset($parm["state"])) {
        $parm["state"] = 3;
      }
      if (isset($where["b.agentarea"])) {
        $parm["agentarea"] = str_replace("%", "", $where["b.agentarea"][1]);
      }
      if (isset($where["a.remituser"])) {
        $parm["remituser"] = str_replace("%", "", $where["a.remituser"][1]);
      }
      if (isset($where["a.state"])) {
        $parm["state"] = $where["a.state"];
      }
      if (isset($where["a.agentno|b.agentname|b.id"])) {
        $parm["agentno"] = str_replace("%", "", $where["a.agentno|b.agentname|b.id"][1]);
      }
      if (isset($where["a.bankuser"])) {
        $parm["bankuser"] = str_replace("%", "", $where["a.bankuser"][1]);
      }
      if (isset($where["a.remituser"])) {
        $parm["remituser"] = str_replace("%", "", $where["a.remituser"][1]);
      }
      if (isset($where["a.putintime"])) {
        $parm["begintime"] = $where["a.putintime"][1][0];
        $parm["endtime"] = $where["a.putintime"][1][1];
      }
    }
    $list = $orders->orderlist($where, 15);
    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    $this->assign("parm", $parm);
    return $this->fetch();
  }

  public function dostate() {
    $id = Request::instance()->param("id", 0, "intval");
    $isdel = Request::instance()->param("isdel");
    $entrytime = Request::instance()->param("entrytime");
    $sortid = Request::instance()->param("sortid", 0, "intval");
    $orders = OrdersModel::get($id);
    $ids = Request::instance()->param("ids");
    $ids = Arrayutil::stringToArray($ids, ";");
    $ids = array_filter($ids);
    if (empty($orders)) {
      return ["state" => "err", "msg" => "非法的参数传入"];
    } else {

      if (empty($ids)) {
        $orders->state = $isdel;
        $orders->entrytime = $entrytime;
        $orders->handtime = date("Y-m-d H:i:s");
        if ($sortid > 0) {
          $item = OrdersModel::get(["sortid" => $sortid]);
          if (!empty($item)) {
            return ["state" => "err", "msg" => "排单号重复"];
          }
          $orders->sortid = $sortid;
        }
        $orders->adminid = $this->adminid;
        if ($orders->save()) {
          return ["state" => "ok", "msg" => "操作成功"];
        } else {
          return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
        }
      } else {
        foreach ($ids as $key => $id) {
          $orders = OrdersModel::get($id);
          $orders->state = $isdel;
          $orders->entrytime = $entrytime;
          $orders->handtime = date("Y-m-d H:i:s");
          $tempsort = $sortid + $key;
          $item = OrdersModel::get(["sortid" => $tempsort]);
          if (!empty($item)) {
            continue;
          }
          $orders->sortid = $tempsort;
          $orders->adminid = $this->adminid;
          $orders->save();
        }
        return ["state" => "ok", "msg" => "操作成功"];
      }
    }
  }

  //代理底单划分
  public function orderdivide() {
    $parms = Request::instance()->param();
    $pageno = Request::instance()->param("page");
    $sessionid = session_id();
    $cachename = "orderdivide_where_$sessionid";
    $orders = new OrdersModel();
    if (!empty($pageno)) {
      if (Cache::has($cachename)) {
        $where = Cache::get($cachename);
      } else {
        $where = ["a.state" => 1];
      }
    } else {
      Cache::rm($cachename); //此处必须删除，否则会影响分页结果
      $where = "1!=1";
    }
    if (Request::instance()->isPost()) {
      $_REQUEST["page"] = 1;
      $where = ["a.state" => 1];
      if (strlen($parms["agentno"])) {
        $where = ["b.agentname" => ["like", "%" . $parms["agentno"] . "%"]];
      }
      if (strlen($parms["agentarea"])) {
        $where = ["b.agentarea" => ["like", "%" . $parms["agentarea"] . "%"]];
      }
      if (strlen($parms["isrecome"])) {
        $where["a.isrecome"] = $parms["isrecome"];
      }
      Cache::set($cachename, $where);
    } else {
      if (isset($where["b.agentname"])) {
        $parms["agentno"] = str_replace("%", "", $where["b.agentname"][1]);
      }
      if (isset($where["b.agentarea"])) {
        $parms["agentarea"] = str_replace("%", "", $where["b.agentarea"][1]);
      }
      if (isset($where["a.isrecome"])) {
        $parms["isrecome"] = $where["a.isrecome"];
      }
    }
    $list = $orders->orderlist($where, 10);
    $this->assign("list", $list);
    $this->assign("page", $list->render());
    $this->assign("parm", $parms);
    return $this->fetch();
  }

  //设置成代理底单
  public function setbase() {
    $id = Request::instance()->param("orderid");
    $id = Arrayutil::stringToArray($id, ";");
    $order = new OrdersModel();
    $info = $order->get($id[0]);

    $agentno = $info["agentno"];
    $agent = new \app\common\model\AgentModel();
    if ($agent->checkAgent($agentno)) {
      return ["state" => "err", "msg" => "已经有10代理底单了，无需再设置"];
    }

    $where = [
        "state" => 1,
        "agentno" => $agentno,
        "isrecome" => 2
    ];
    $count = $order->where($where)->count();
    if ($count >= 10) {
      return ["state" => "err", "msg" => "已经有10代理底单了，无需再设置"];
    }
    $id = Arrayutil::sub_array($id, 0, 10 - $count);
    $temp["id"] = ["in", $id];
    if ($order->where($temp)->update(["isrecome" => 2])) {
      return ["state" => "ok", "msg" => "操作成功"];
    } else {
      return ["state" => "err", "msg" => "系统繁忙，稍后再试"];
    }
  }

  //恢复成普通订单
  public function backorder() {
    $id = Request::instance()->param("orderid");
    $order = new OrdersModel();
    $info = $order->get($id);
    $info->isrecome = 0;
    if ($info->save()) {
      \app\common\model\BizModel::where("ordersid=$id")->delete();
      return ["state" => "ok", "msg" => "操作成功"];
    } else {
      return ["state" => "err", "msg" => "系统繁忙，稍后再试"];
    }
  }

  //已审核的订单 根据入账时间排序
  public function sortorder() {
    $pageno = Request::instance()->param("page");
    $parm = Request::instance()->param();
    $sessionid = session_id();
    $cachename = "sortorder_where_$sessionid";
    $orders = new OrdersModel();
    if (!empty($pageno)) {
      if (Cache::has($cachename)) {
        $where = Cache::get($cachename);
      } else {
        $where["a.state"] = 1;
        $where["a.sortid"] = ["null", null];
      }
    } else {
      Cache::rm($cachename); //此处必须删除，否则会影响分页结果
      $where["a.state"] = 1;
      $where["a.sortid"] = ["null", null];
    }
    if (Request::instance()->isPost()) {
      $_REQUEST["page"] = 1;
      $where = [];
      $where["a.state"] = 1;
      $where["a.sortid"] = ["null", null];
      if (strlen($parm["agentno"])) {
        $where ["a.agentno|b.agentname|b.id"] = ["eq", $parm["agentno"]];
      }
      if (strlen($parm["begintime"]) && strlen($parm["endtime"])) {
        $iend = max(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $istart = min(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $begintime = date("Y-m-d ", $istart) . "00:00:00";
        $endtime = date("Y-m-d ", $iend) . "23:59:59";
        $where["a.entrytime"] = ["between", [$begintime, $endtime]];
      }
      if (strlen($parm["remituser"])) {
        $where["a.remituser"] = ["like", "%" . $parm["remituser"] . "%"];
      }
      Cache::set($cachename, $where);
    } else {
      if (isset($where["a.entrytime"])) {
        $parm["begintime"] = $where["a.entrytime"][1][0];
        $parm["endtime"] = $where["a.entrytime"][1][1];
      }
      if (isset($where["a.agentno|b.agentname|b.id"])) {
        $parm["agentno"] = $where["a.agentno|b.agentname|b.id"][1];
      }
      if (isset($where["a.remituser"])) {
        $parm["remituser"] = str_replace("%", "", $where["a.remituser"][1]);
      }
    }
    $sortid = $orders->max("sortid");
    $this->assign("sortid", $sortid);
    $list = $orders->ordersort($where, 15);
    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    $this->assign("parm", $parm);
    return $this->fetch();
  }

  //订单排号
  public function dosort() {
    $id = Request::instance()->param("orderid");
    $sortid = Request::instance()->param("sortid");

    $temp = (new OrdersModel())->where("sortid=$sortid")->find();
    if (!empty($temp)) {
      return ["state" => "err", "msg" => "排单编号已经被占用"];
    }
    $order = OrdersModel::get($id);
    if ($order->state != 1) {
      return ["state" => "err", "msg" => "报单未通过审核，不能排号"];
    }
    $order->sortid = $sortid;
    if ($order->save()) {
      return ["state" => "ok", "msg" => "设置成功"];
    } else {
      return ["state" => "err", "msg" => "设置失败"];
    }
  }

  //打印收据
  public function printorder() {
    $id = Request::instance()->param("id");
    $user = User::get($this->adminid);
    $orders = OrdersModel::get($id);
    $entrytime = $orders["entrytime"];

    if (empty($entrytime)) {
      $this->error("报单还没入账，不能打印收据");
    }
    $rece = ReceiptModel::get(["orderid" => $id]);
    if (!empty($rece)) {
      $this->error("该报单已经打印了，如果需要重新打印，请到财务管理里面打印");
    }

    $begin = date("Y-m-d", strtotime($entrytime)) . " 00:00:00";
    $end = date("Y-m-d", strtotime($entrytime)) . " 23:59:59";
    $count = ReceiptModel::where(["entrytime" => ["between", [$begin, $end]]])->count();
    $receiptno = date("Ymd", strtotime($entrytime)) . "-" . ($count + 1);

    $this->assign("receiptno", $receiptno);
    $this->assign("user", $user);
    $this->assign("orders", $orders);

    return $this->fetch();
  }

  public function doprint() {
    $receiptno = Request::instance()->param("receiptno");
    $parms = Request::instance()->param();
    $rece = ReceiptModel::get(["receiptno" => $receiptno]);
    if (empty($rece)) {
      $rece = new ReceiptModel();
      $rece->adminid = $this->adminid;
      $rece->printtime = date("Y-m-d H:i:s");
      foreach ($parms as $key => $value) {
        $rece->$key = $value;
      }
      if ($rece->save()) {
        return ["state" => "ok", "msg" => "插入数据成功"];
      } else {
        return ["state" => "err", "msg" => "该收据号已经被开出，请重新打开打印窗口"];
      }
    } else {
      return ["state" => "err", "msg" => "该收据号已经被开出，请重新打开打印窗口"];
    }
  }

  //散户入单
  public function addorder() {
    if (Request::instance()->isPost()) {
      $orders = new OrdersModel();
      $parms = Request::instance()->param();
      unset($parms["agentname"]);
      $account = intval(Request::instance()->param("remitaccount"));
      $savedatas = [];
      $itemtime = date("Y-m-d H:i:s");
      for ($i = 0; $i < ($account / 10000); $i++) {
        foreach ($parms as $key => $value) {
          if ($key != "remitaccount") {
            if ($key == "sortid") {
              if (($account / 10000) == 1 && strlen($value)) {
                $temp[$key] = $value;
              }
            } else {
              $temp[$key] = $value;
            }
          } else {
            $temp["remitaccount"] = 10000;
          }
        }
        $temp["putintime"] = $itemtime;
        $temp["adminid"] = $this->_adminid;
        $temp["handtime"] = $itemtime;
        $temp["entrytime"] = $itemtime;
        $savedatas[] = $temp;
      }
      if (count($savedatas) === 0) {
        return ["state" => "err", "msg" => "报单金额不能小于1W"];
      }
      if ($orders->saveAll($savedatas)) {
        return ["state" => "ok", "msg" => "操作成功！"];
      } else {
        return ["state" => "err", "msg" => "系统繁忙，清稍后再试"];
      }
    } else {
      $this->assign("info", null);
      return $this->fetch();
    }
  }

  //排单出彩列表
  public function sendorder() {
    $username = Request::instance()->param("agentno", "", "trim");
    $begintime = Request::instance()->param("begintime", "");
    $endtime = Request::instance()->param("endtime", "");
    $orders = new OrdersModel();

    $entrytime = date("Y-m-d", strtotime("-1 day")) . " 23:59:59";
    $where["a.state"] = 1;
    $where["a.sortid"] = ["neq", ""];
    $where["a.issend"] = 0;

   

    if (strlen($username)) {
      $where ["a.agentno|b.agentname|b.id"] = ["eq", $username];
    }

    if (strlen($begintime) && strlen($endtime)) {
      $iend = max(strtotime($begintime), strtotime($endtime));
      $istart = min(strtotime($begintime), strtotime($endtime));
      $begintime = date("Y-m-d ", $istart) . "00:00:00";
      $endtime = date("Y-m-d ", $iend) . "23:59:59";
      $where["a.entrytime"] = ["between", [$begintime, $endtime]];
      $list = $orders->ordersort($where, 15);
    } else {
      $where["a.entrytime"] = ["ELT", $entrytime];
      //此处用缓存有必要么， ？？
      $_tempsortid = $orders->alias("a")->where($where)->max("sortid");
    
      $_tempsortid = floor($_tempsortid * 0.1 ) +99;
      $where["a.sortid"] = ["elt", $_tempsortid];
      
      $list = $orders->ordersort($where, 15);
    }

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

  //导出报单列表
  public function export() {
    $sessionid = session_id();
    $cacheexport = "cacheexport_where_" . $sessionid;
    if (Cache::has($cacheexport)) {
      $where = Cache::get($cacheexport);
    } else {
      $begin = date("Y-m-d", strtotime("-1 day")) . " 00:00:00";
      $end = date("Y-m-d", strtotime("-1 day")) . " 23:59:59";
      $where = [
          "a.state" => "1",
          "a.putintime" => ["between", [$begin, $end]],
      ];
    }
    $list = (new OrdersModel())->orderlist($where, TRUE);
    $header = ["排单编号", "代理ID", "入单人", "推荐人", "代理区域", "汇款人", "汇款时间", "入单人身份证",
        "入单人手机", "积分平台", "返积分ID", "返积分手机号", "代理商", "汇款金额", "报单类型", "审核状态", "审核时间"];
    $data = array();
    foreach ($list as $key => $value) {
      $item = [];
      $sortid = $value["sortid"] ? $value["sortid"] : "待排单";
      $item[] = $sortid;
      $item[] = $value["uid"] . "\t";
      $item[] = $value["remituser"] . "\t";
      $item[] = $value["recomeuser"] . "\t";
      $item[] = $value["agentarea"] . "\t";
      $item[] = $value["bankuser"] . "\t";
      $item[] = $value["remittime"] . "\t";
      $item[] = $value["remitidcard"] . "\t";
      $item[] = $value["remittel"] . "\t";
      if (empty($value["platform"])) {
        $item[] = "未知" . "\t";
      } else {
        $item[] = $this->platform[$value["platform"]] . "\t";
      }
      $item[] = $value["platformid"] . "\t";
      $item[] = $value["platformtel"] . "\t";
      $item[] = $value["agentname"] . "\t";
      $item[] = $value["remitaccount"] . "\t";
      $item[] = $this->isrecome[$value["isrecome"]] . "\t";
      $item[] = $this->statelist[$value["state"]] . "\t";
      $item[] = $value["handtime"] . "\t";
      $data[$key] = $item;
      unset($value);
      unset($item);
    }
    unset($list);
    $displayname = "";
    if (key_exists("a.putintime", $where)) {
      $begin = date("Y-m-d", strtotime($where["a.putintime"][1][0]));
      $end = date("Y-d-d", strtotime($where["a.putintime"][1][1]));
      if ($begin == $end) {
        $displayname .= "$begin";
      } else {
        $displayname .= $begin . "到$end";
      }
    }
    if (key_exists("a.state", $where)) {
      $displayname .= $this->statelist[$where["a.state"]];
    }
    $displayname .= "报单数据";

    $filename = time();
    $tempname = "报单数据";
    $files = CometUtil::export($header, $data, $tempname);
    $filename = export($files, $filename);
    $this->assign("displayname", $displayname);
    $this->assign("downname", $filename);
    return $this->fetch();
  }

  //报单预出彩
  public function dosendorder() {
    $id = Request::instance()->param("id");
    $info = OrdersModel::get($id);
    if (empty($info)) {
      return ["state" => "err", "msg" => "非凡的传参"];
    }
    $info->issend = 1;
    $info->sendtime = date("Y-m-d H:i:s");
    if ($info->save()) {
      #发送短信
      $agentinfo = AgentModel::get(["agentno" => $info["agentno"]]);
      $where = [
          "sendtime" => ["like", date("Y-m-d") . "%"],
          "sendtel" => $agentinfo["agenttel"],
      ];
      $smslog = new OrdersmsModel();
      $smsinfo = $smslog->where($where)->find();
      if (empty($smsinfo)) {
        $sms = new SmsSend();
        $msg = "【云堂网络科技】尊敬的代理商：" . $agentinfo["agentname"] . "您好，您的报单即将出彩，请通过微信公众号：云堂网络科技  登录报单系统，在我的出彩报单中进行处理;";
        $result = $sms->sendSMS($agentinfo["agenttel"], $msg);
        if (isset($result[1]) && $result[1] == 0) {
          $smslog->sendtime = date("Y-m-d H:i:s");
          $smslog->sendtel = $agentinfo["agenttel"];
          $smslog->agentno = $agentinfo["agentno"];
          $smslog->state = 1;
          $smslog->save();
        }
      }
      return ["state" => "ok", "msg" => "操作成功"];
    } else {
      return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
    }
  }

  //报单拒绝
  public function repulseorder() {
    $id = Request::instance()->param("id");
    $info = OrdersModel::get($id);
    if (Request::instance()->isPost()) {
      $remark = Request::instance()->param("remark");
      if (strlen($remark) === 0) {
        return ["state" => "err", "msg" => "拒绝报单的原因不能为空！"];
      }
      $info->state = -1;
      $info->adminid = $this->_adminid;
      $info->handtime = date("Y-m-d H:i:s");
      $info->remark = $remark;
      if ($info->save()) {
        return ["state" => "ok", "msg" => "操作成功"];
      } else {
        return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
      }
    } else {
      $this->assign("info", $info);
      return $this->fetch();
    }
  }

  public function officialorders() {
    $username = Request::instance()->param("agentno", "", "trim");
    $begintime = Request::instance()->param("begintime", "");
    $endtime = Request::instance()->param("endtime", "");
    $orders = new OrdersModel();
    $entrytime = date("Y-m-d", strtotime("-1 day")) . " 23:59:59";
    $where = [
        "a.state" => 1,
        "a.issend" => 3
    ];

    $iend = max(strtotime($begintime), strtotime($endtime));
    $istart = min(strtotime($begintime), strtotime($endtime));

    if (strlen($username)) {
      $where ["a.agentno|b.agentname|b.id"] = ["eq", $username];
    }

    if (strlen($begintime) && strlen($endtime)) {
      $begintime = date("Y-m-d ", $istart) . "00:00:00";
      $endtime = date("Y-m-d ", $iend) . "23:59:59";
      $where["a.entrytime"] = ["between", [$begintime, $endtime]];
      $list = $orders->ordersort($where, 15);
    } else {
      $where["a.entrytime"] = ["ELT", $entrytime];
      //此处用缓存有必要么， ？？
      $_tempsortid = $orders->alias("a")->where($where)->max("sortid");
      $_tempsortid = floor($_tempsortid * 0.1) - 1;
      $where["a.sortid"] = ["elt", $_tempsortid];
      $list = $orders->ordersort($where, 15);
    }

    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    $this->assign("flatform", $this->platform);
    $this->assign("agentno", $username);
    $this->assign("begintime", $begintime);
    $this->assign("endtime", $endtime);
    return $this->fetch();
  }

  //确认出彩
  public function confirmorders() {
    $ordersid = Request::instance()->param("id");

    if (Request::instance()->isPost()) {
      $orders = OrdersModel::get($ordersid);
      $orders->issend = 2;
      if ($orders->save()) {
        $ordersmap = (new OrdersMapModel())->where(["parentorderid" => $ordersid])->column("orderid"); //获取复投单号
        foreach ($ordersmap as $itemid) {
          $lockCache = "LockCache";
          if (!Cache::has($lockCache)) {
            Cache::set($lockCache, $itemid, 60); //锁
            $sortid = (new OrdersModel())->max("sortid");
            $order = OrdersModel::get($itemid);
            $order->sortid = $sortid + 1;
            $order->bankuser = "编号$ordersid的积分复投到$sortid";
            $order->state = 1;
            $order->entrytime = date("Y-m-d");
            $order->issend = 0;
            $order->handtime = date("Y-m-d H:i:s");
            $order->save();
            Cache::rm($lockCache);
          }
        }
        return ["state" => "ok", "msg" => "出彩成功！"];
      } else {
        return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
      }
    } else {
      $orders = (new OrdersModel())->orderinfo($ordersid);
      $this->assign("biztype", $this->biztype);
      $this->assign("info", $orders);
      $this->assign("platform", $this->platform);
      return $this->fetch();
    }
  }

  //导入老系统数据
  public function putinorder() {
    return $this->fetch();
  }

  //现金报单数据导入，并入库
  public function putincash() {
    $filepath = Request::instance()->param("filepath");
    $rows = CometUtil::ExcelReader($filepath);
    $total = count($rows);
    if (!empty($rows)) {
      $filename = time() . ".csv";
      $rows[0][] = "错误原因";
      $head = Arrayutil::arrayTostring($rows[0], ",") . PHP_EOL;
      file_put_contents($filename, $head);
      //出去表头
      array_shift($rows);
      $putIndata = [];
      foreach ($rows as $item) {
        if (empty($item)) {
          continue;
        }
        $sortid = stringutil::trimString($item[0]);  //排单编号
        $agenttel = stringutil::trimString($item[1]); //代理商电话
        $remituser = stringutil::trimString($item[2]);  //入单人
        $recomeuser = stringutil::trimString($item[3]);  //推荐人
        $bankuser = stringutil::trimString($item[5]);    //汇款人
        $remittime = stringutil::simpletrim(trim($item[6]));   //汇款时间 
        $entrytime =  stringutil::simpletrim(trim($item[6]));    //入账时间
        $remitidcard = stringutil::trimString($item[7]);   //入单人身份证
        $remittel =stringutil::trimString( $item[8]);     //入单人手机
        $platform = stringutil::trimString($item[9]);    //积分平台
        $platformid = stringutil::trimString($item[10]);   //积分ID
        $platformtel = stringutil::trimString($item[11]);   //返积分手机
        $remitaccount = stringutil::trimString($item[12]);   //入单金额
        $weekend =stringutil::trimString( $item[13]);        //是否发放周奖励
        $monthend =stringutil::trimString( $item[14]);       //是否发放月奖励
        $isrecome = stringutil::trimString($item[15]);       //底单类型  1别人的底单，2自己的底单，0普通报单 
        $resulttype =stringutil::trimString( $item[16]);      //是否是复投单
        $issend = stringutil::trimString($item[17]);          //是否出彩
        $isincar = stringutil::trimString($item[18]);

        $caCheName = "AgentTel_PutIn_" . $agenttel;

        if (Cache::has($caCheName)) {
          $agentinfo = Cache::get($caCheName);
        } else {
          $agentinfo = AgentModel::get(["agenttel" => $agenttel])->agentno;
          Cache::set($caCheName, $agentinfo, 120);
        }
        if (empty($sortid)) {  //没有排序的 ，不导入，记录数据
          $item[] = "没有排序";
          $str = Arrayutil::arrayTostring($item, "\t,") . PHP_EOL;
          file_put_contents($filename, $str, FILE_APPEND); //记录没有导入的数据
          continue;
        }
        if (empty($agentinfo)) {
          $item[] = "代理商未注册";
          $str = Arrayutil::arrayTostring($item, "\t,") . PHP_EOL;
          file_put_contents($filename, $str, FILE_APPEND); //记录没有导入的数据
          continue;
        }
        $ordersinfo = OrdersModel::get(["sortid" => $sortid]);
        if ($ordersinfo) { //已经录入的数据，不重复录入，是否需要提示，写入到文件？？
          $item[] = "报单编号已存在";
          $str = Arrayutil::arrayTostring($item, "\t,") . PHP_EOL;
          file_put_contents($filename, $str, FILE_APPEND); //记录没有导入的数据
          continue;
        }
        if(strlen($remitidcard)!=18|| strlen($remittel)!=11| strlen($platformtel)!=11){
          $item[] = "数据长度不和法";
          $str = Arrayutil::arrayTostring($item, "\t,") . PHP_EOL;
          file_put_contents($filename, $str, FILE_APPEND); //记录没有导入的数据
          continue;
        }
        
        
        $putIndata[] = [
            "sortid" => $sortid,
            "agentno" => $agentinfo,
            "remituser" => $remituser,
            "recomeuser" => $recomeuser,
            "bankuser" => $bankuser,
            "remittime" => $remittime,
            "entrytime" => $entrytime,
            "remitidcard" => $remitidcard,
            "remittel" => $remittel,
            "platform" => $platform,
            "platformid" => $platformid,
            "platformtel" => $platformtel,
            "remitaccount" => $remitaccount,
            "weekend" => $weekend,
            "monthend" => $monthend,
            "state" => 1,
            "isrecome" => $isrecome,
            "resulttype" => $resulttype,
            "adminid" => $this->_adminid,
            "handtime" => $entrytime,
            "isredelivery" => 0,
            "isspecial" => 0,
            "issend" => $issend,
            "isputin" => 1,
            "isincar" => $isincar,
        ];
        unset($item);
      }
      unset($rows);
      $orders = new OrdersModel();
      $data = [
          "total" => $total,
          "putin" => count($putIndata),
      ];
      if ($total - 2 == count($putIndata)) {
        @unlink($filename);
      } else {
        $data["downfile"] = $filename;
      }
   
      if (empty($putIndata)) {
        return ["state" => "err", "msg" => "没有可以到入的数据", "data" => $data];
      } else {
        if ($orders->saveAll($putIndata)) {
          return ["state" => "ok", "msg" => "导入成功", "data" => $data];
        } else {
          return ["state" => "err", "msg" => "导入失败"];
        }
      }
    } else {
      return ["state" => "err", "msg" => "抱歉，没有找到文件"];
    }
  }

  //复投单数据导入
  public function putinpoint() {
    $filepath = Request::instance()->param("filepath");
    $rows = CometUtil::CsvFileRead($filepath);
    $total = count($rows);
    if (!empty($rows)) {
      $filename = time() . ".csv";
      $rows[0][] = "错误原因";
      $head = Arrayutil::arrayTostring($rows[0], ",") . PHP_EOL;
      file_put_contents($filename, $head);
      //除去表头
      array_shift($rows);
      $putIndata = [];

      foreach ($rows as $item) {
        if (empty($item)) {
          continue;
        }
        $orders = new OrdersModel();
        $basesortid = $item[0];  //最初底单
        $sortid = $item[1];       //复投编号
        $biztype = $item[2];      //复投方式
        $actiontime = $item[3];    //复投时间
        $isspecial = $item[4];     //能否参与直通车

        $orderinfo = OrdersModel::get(["sortid" => $basesortid]);
        if (empty($orderinfo)) {
          $item[] = "初始单不存在";
          $str = Arrayutil::arrayTostring($item, ",") . PHP_EOL;
          file_put_contents($filename, $str, FILE_APPEND); //记录没有导入的数据
          continue;
        }
        $tempinfo = OrdersModel::get(["sortid" => $sortid]);
        if (!empty($tempinfo)) {
          $item[] = "复投单的排单编号已经存在";
          $str = Arrayutil::arrayTostring($item, ",") . PHP_EOL;
          file_put_contents($filename, $str, FILE_APPEND); //记录没有导入的数据
          continue;
        }
        $ordersMap = OrdersMapModel::get(["parentorderid" => $orderinfo->id]);

        if (empty($ordersMap)) {
          $baseorderid = $orderinfo->id;
        } else {
          $baseorderid = $ordersMap->baseorderid;
        }
        $orderid = $orderinfo->id;
        unset($orderinfo->id);
        $orderinfo->sortid = $sortid;
        $orderinfo->bankuser = "排单编号:" . $basesortid . "的积分复投，复投单号" . $sortid;
        $orderinfo->isputin = 1;
        $orderinfo->isredelivery = 1;
        $orderinfo->isspecial = $isspecial;


        if ($orders->save($orderinfo->toArray())) {
          $putIndata[] = [
              "baseorderid" => $baseorderid,
              "parentorderid" => $orderid,
              "orderid" => $orders->id,
              "isspecial" => $isspecial,
              "lettertype" => $biztype,
              "actiontime" => $actiontime,
          ];
        } else {
          $item[] = "数据库录入错误";
          $str = Arrayutil::arrayTostring($item, ",") . PHP_EOL;
          file_put_contents($filename, $str, FILE_APPEND); //记录没有导入的数据
        }
      }
      unset($rows);
      $ordersmap = new OrdersMapModel();
      $data = [
          "total" => $total,
          "putin" => count($putIndata),
      ];
      if ($total - 2 == count($putIndata)) {
        @unlink($filename);
      } else {
        $data["downfile"] = $filename;
      }

      if (empty($putIndata)) {
        return ["state" => "err", "msg" => "导入失败", "data" => $data];
      } else {
        if ($ordersmap->saveAll($putIndata)) {
          return ["state" => "ok", "msg" => "导入成功", "data" => $data];
        } else {
          return ["state" => "err", "msg" => "导入失败"];
        }
      }
    } else {
      return ["state" => "err", "msg" => "抱歉，没有找到文件"];
    }
  }

  //报单复合查询
  public function search() {
    $parm = Request::instance()->param();
    foreach ($parm as $key => $value) {
      $parm[$key] = trim($value);
    }
    $sessionid = session_id();
    $cachename = "orderssearch_where_$sessionid";
    $searchexport = "searchexport_where_$sessionid";
    $pageno = Request::instance()->param("page");

    if (!empty($pageno)) {
      if (Cache::has($cachename)) {
        $where = Cache::get($cachename);
      } else {
        $where = [];
      }
    } else {
      $where = "1!=1";
      Cache::rm($cachename); //此处必须删除，否则会影响分页结果
    }

    if (Request::instance()->isPost()) {
      $_REQUEST["page"] = 1;
      $where = [];
      if (strlen($parm["agentno"])) {
        $where ["a.agentno|b.agentname|b.id"] = ["eq", $parm["agentno"]];
      }
      if (strlen($parm["agentarea"])) {
        $where ["b.agentarea"] = ["like", "%" . $parm["agentarea"] . "%"];
      }
      if (strlen($parm["begintime"]) && strlen($parm["endtime"])) {
        $iend = max(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $istart = min(strtotime($parm["begintime"]), strtotime($parm["endtime"]));
        $begintime = date("Y-m-d ", $istart) . "00:00:00";
        $endtime = date("Y-m-d ", $iend) . "23:59:59";
        $where["a.entrytime"] = ["between", [$begintime, $endtime]];
        $parm["begintime"] = $begintime;
        $parm["endtime"] = $endtime;
      }
      if (strlen($parm["isrecome"])) {
        $isrecome = $parm["isrecome"];
        $where["a.isrecome"] = ["eq", $isrecome];
      }
      if (strlen($parm["remituser"])) {
        $isrecome = $parm["remituser"];
        $where["a.remituser"] = ["like", "%" . $isrecome . "%"];
      }
      if (strlen($parm["sortid"])) {
        $sortid = $parm["sortid"];
        $where["a.sortid"] = ["eq", $sortid];
      }

      if (strlen($parm["state"])) {
        $state = $parm["state"] == 2 ? 0 : $parm["state"];
        $where["a.state"] = ["eq", $state];
      }
      if (strlen($parm["isputin"])) {
        $putin = $parm["isputin"] == 1 ? 1 : 0;
        $where["a.isputin"] = ["eq", $putin];
      }
      if (strlen($parm["isredelivery"])) {
        $where["a.isredelivery"] = ["eq", $parm["isredelivery"]];
      }
      Cache::set($cachename, $where);
      Cache::set($searchexport, $where);
    } else {

      if (!isset($parm["state"])) {
        $parm["state"] = 3;
      }
      if (isset($where["a.agentno|b.agentname|b.id"])) {
        $parm["agentno"] = str_replace("%", "", $where["a.agentno|b.agentname|b.id"][1]);
      }
      if (isset($where["b.agentarea"])) {
        $parm["agentarea"] = str_replace("%", "", $where["b.agentarea"][1]);
      }
      if (isset($where["a.entrytime"])) {
        $parm["begintime"] = $where["a.entrytime"][1][0];
        $parm["endtime"] = $where["a.entrytime"][1][1];
      }

      if (isset($where["a.isrecome"])) {
        $parm["isrecome"] = str_replace("%", "", $where["a.isrecome"][1]);
      }
      if (isset($where["a.remituser"])) {
        $parm["remituser"] = str_replace("%", "", $where["a.remituser"][1]);
      }
      if (isset($where["a.sortid"])) {
        $parm["sortid"] = str_replace("%", "", $where["a.sortid"][1]);
      }

      if (isset($where["a.state"])) {
        $parm["state"] = $where["a.state"][1];
      }
      if (isset($where["a.isputin"])) {
        $parm["isputin"] = $where["a.isputin"][1]?$where["a.isputin"][1]:2;
      }
      if (isset($where["a.isredelivery"])) {
        $parm["isredelivery"] = str_replace("%", "", $where["a.isredelivery"][1]);
      }
    }
    $orders = new OrdersModel();
    $list = $orders->sortList($where, 15);
    $this->assign("list", $list);
    $this->assign("parm", $parm);
    $this->assign("page", $list->render());
    return $this->fetch();
  }

  //搜索选择导出
  public function search_export() {
    $sessionid = session_id();
    $searchexport = "searchexport_where_$sessionid";
    $ids = Request::instance()->param("ids");

    $ids = Arrayutil::stringToArray($ids, ";");
    $ids = array_filter($ids);
    if (!empty($ids)) {
      $where["a.id"] = ["in", $ids];
      $list = (new OrdersModel())->sortList($where, TRUE);
    } else {
      $where = Cache::get($searchexport);
      if (empty($where)) {
        return ["state" => "err", "msg" => "请重新查询数据"];
      }
      $list = (new OrdersModel())->sortList($where, true); //此处是被逼无奈这么写的
    }
    $header = ["代理ID", "入单人", "推荐人", "代理区域", "汇款人", "汇款时间", "入单人身份证", "入单人手机", "积分平台",
        "返积分ID", "返积分手机号", "代理商", "汇款金额", "报单类型", "审核状态", "审核时间"];
    $data = array();
    foreach ($list as $key => $value) {
      $item = [];
      $item[] = $value["uid"] . "\t";
      $item[] = $value["remituser"] . "\t";
      $item[] = $value["recomeuser"] . "\t";
      $item[] = $value["agentarea"] . "\t";
      $item[] = $value["bankuser"] . "\t";
      $item[] = $value["remittime"] . "\t";
      $item[] = $value["remitidcard"] . "\t";
      $item[] = $value["remittel"] . "\t";
      if (empty($value["platform"])) {
        $item[] = "未知" . "\t";
      } else {
        $item[] = $this->platform[$value["platform"]] . "\t";
      }
      $item[] = $value["platformid"] . "\t";
      $item[] = $value["platformtel"] . "\t";
      $item[] = $value["agentname"] . "\t";
      $item[] = $value["remitaccount"] . "\t";
      $item[] = $this->isrecome[$value["isrecome"]] . "\t";
      $item[] = $this->statelist[$value["state"]] . "\t";
      $item[] = $value["handtime"] . "\t";
      $data[$key] = $item;
      unset($value);
    }
    unset($list);
    $filename = time() . ".csv";
    CometUtil::Simple_export($header, $data, $filename);
  }

  //修改报单信息
  public function updateorders() {
    $id = Request::instance()->param("id");
    $orders = OrdersModel::get($id);
    if (empty($orders)) {
      $this->error("非法的参数传入");
    }
    if (Request::instance()->isPost()) {
      $parm = Request::instance()->param();
      unset($parm["agentname"]);
      foreach ($parm as $key => $item) {
        if ($key == "sortid") {
          if (strlen($item)) {
            $orders->$key = $item;
          } else {
            $orders->sortid = null;
          }
        } else {
          if (strlen($item)) {
            $orders->$key = $item;
          }
        }
      }
      if ($orders->save()) {
        return ["state" => "ok", "msg" => "修改成功"];
      } else {
        return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
      }
    } else {
      $this->assign("info", $orders);
      return $this->fetch();
    }
  }

  //文件下载，并删除原始文件
  public function downfile() {
    $filename = Request::instance()->param("file");
    header("Cache-Control: max-age=0");
    header("Content-Description: File Transfer");
    header('Content-disposition: attachment; filename=' . basename($filename)); // 文件名
    header("Content-Type: application/zip"); // zip格式的
    header("Content-Transfer-Encoding: binary"); //
    header('Content-Length: ' . filesize($filename)); //
    @readfile($filename); //输出文件;
    unlink($filename); //删除压缩包临时文件
  }

  public function insertorder() {
    if (Request::instance()->isPost()) {
      $parms = Request::instance()->param();
      unset($parms["agentname"]);
      $begin = $parms["beginsortid"];
      unset($parms["beginsortid"]);
      $end = $parms["endsortid"];
      unset($parms["endsortid"]);
      $numsortid = $parms["numsortid"];
      unset($parms["numsortid"]);
      $remitaccount = $parms["remitaccount"];
      unset($parms["remitaccount"]);
      if (!strlen($begin)) {
        return ["state" => "err", "msg" => "初始编号不能为空！"];
      }
      if (!strlen($remitaccount)) {
        return ["state" => "err", "msg" => "入单金额不能为空！"];
      }
      $numsortid = empty($numsortid) ? 1 : $numsortid;
      $temp = $remitaccount / 10000;
      if ($temp > $numsortid) {
        return ["state" => "err", "msg" => "插单数量大于移动步长"];
      }
      $info = OrdersModel::get(["sortid" => $begin]);
      if (empty($info)) {
        return ["state" => "err", "msg" => "起始位置不合法"];
      }
      $where = " sortid>=$begin and sortid<$end";
      $result = OrdersModel::where($where)->update(['sortid' => ['exp', 'sortid+' . $numsortid]]);
      if ($result) {
        $data = [];
        for ($i = 0; $i < $temp; $i++) {
          $item = [];
          foreach ($parms as $key => $values) {
            if (strlen($values)) {
              $item[$key] = $values;
            }
          }
          if (empty($item)) {
            continue;
          }
          $item["remitaccount"] = 10000;
          $item["sortid"] = $begin + $i;
          $item["remittime"] = date("Y-m-d H:i:s");
          $data[] = $item;
        }
        $orders = new OrdersModel();
        if (empty($data)) {
           return ["state" => "ok", "msg" => "操作成功"];
        } else {
          if ($orders->saveAll($data)) {
            return ["state" => "ok", "msg" => "操作成功"];
          } else {
            return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
          }
        }
      } else {
        return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
      }
    } else {
      return $this->fetch();
    }
  }

  //复制报单
  public function copyorders(){
    $id= Request::instance()->param("id");
    $info= OrdersModel::get($id);
    if(empty($info)){
      return ["state"=>"err","msg"=>"信息不存在， 无法复制"];
    }
    if($info["state"]===-1){
      return  ["state"=>"err","msg"=>"已经拒绝的报单，无需复制"];
    }
    $orders=new OrdersModel();
    if($orders->copyorder($info)){
      return ["state"=>"ok","msg"=>$this->_sucmsg];
    }else{
      return ["state"=>"err","msg"=>$this->_errmsg];
    }
  }
  
  public function ztcfutou(){
    $ztid= Request::instance()->param("ztid",0,"intval");
    $cacheName="ztLock";
    if(Cache::has($cacheName)){
      return ["state"=>"err","msg"=>"系统繁忙，请稍后再试"] ;
    }
    Cache::set($cacheName, "Lock"); 
    $ztinfo= Ztorders::get($ztid);
    if(empty($ztinfo)){
      $this->error("没有直通车信息");
    }
    if($ztinfo->futoutype==4){
      $this->error("已经复投，不能再复投了");
    }
    if(Request::instance()->isPost()){
       $parms = Request::instance()->param();
       unset($parms["agentname"]);
       unset($parms["ztid"]);
       $ftcount= trim($parms["futoutype"]);
       unset($parms["futoutype"]);
       for($i=0;$i<$ftcount;$i++){
         $orders=new OrdersModel();
         
         foreach ($parms as $key=>$value){
           if($key=="sortid"){
             $value+=$i;
             $info = OrdersModel::get(["sortid"=>$value]);
             if($info){
               Cache::rm($cacheName);
               return  ["state"=>"err","msg"=>"报单编号已经被占用，不能复投此单"];
             }
           }
           $orders->$key=$value;
         }
         if($i===0){
           $orders->isspecial=1;
         }
         $orders->isredelivery=1;
         $orders->remittime=$orders->putintime=$orders->handtime=$orders->entrytime= date("Y-m-d H:i:s");
         $orders->adminid= $this->_adminid;
         if($orders->save()){
           Cache::rm($cacheName);
         }else{
           Cache::rm($cacheName);
           return ["state"=>"err","msg"=>"系统繁忙，请稍后再试"];
         }
       }
       $ztinfo->futoutype=4;
       $ztinfo->save();       
       return ["state"=>"ok","msg"=> $this->_sucmsg];
    }else{
      $this->assign("ztinfo",$ztinfo);
      return $this->fetch();
    }
  }
}
