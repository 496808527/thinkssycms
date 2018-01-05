<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\controller;

use app\common\controller\AdminCommon;
use app\common\model\ResourceModel;

use think\Cache;
use \think\Request;

/**
 * Description of Resource
 *
 * @author ytkj
 */
class Resource extends AdminCommon {

  private $filetype = ["1" => "客服相关", "2" => "财务相关", "3" => "其他"];

  public function __construct(\think\Request $request = null) {
    parent::__construct($request);
    $this->assign("typelist", $this->filetype);
  }

  public function index() {
    $where = [];
    $pageno = Request::instance()->param("page");
    $sessionid = session_id();
    $cachename = "Resourceindex_where_$sessionid";
    $parm= Request::instance()->param();
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
      $where = [];
    }

    if (Request::instance()->isPost()) {
      
      if(strlen($parm["filetype"])){
        $where["a.filetype"]=["eq",$parm["filetype"]];
      }
      $begintime = Request::instance()->param("begintime", "");
      $endtime = Request::instance()->param("endtime", "");
   
      if (strlen($begintime) && strlen($endtime)) {
        $iend = max(strtotime($begintime), strtotime($endtime));
        $istart = min(strtotime($begintime), strtotime($endtime));
        $begintime = date("Y-m-d ", $istart) . "00:00:00";
        $endtime = date("Y-m-d ", $iend) . "23:59:59";
        $where["a.putintime"] = ["between", [$begintime, $endtime]];
      }
      
      Cache::set($cachename, $where);
    }
    $list = (new ResourceModel())->resourselist($where, 15);
    $page = $list->render();
    $this->assign("parm",$parm);
    $this->assign("list", $list);
    $this->assign("page", $page);
    return $this->fetch();
  }

  //上传资料
  public function sendResource() {
    if (Request::instance()->isPost()) {
      $parms = Request::instance()->param();
      $res = new ResourceModel();
      $res->filetype = $parms["filetype"];
      $res->filepath = $parms["filepath"];
      $res->putintime = date("Y-m-d H:i:s");
      $res->adminid = $this->_adminid;
      $res->filedescribe = $parms["filedescribe"];
      return $res->save();
    } else {
      return $this->fetch();
    }
  }

}
