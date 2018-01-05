<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\controller;
use app\common\controller\AdminCommon;
use app\common\model\AgentExcelModel;


use think\Request; 
use think\Cache;
/**
 * Description of Agentexcel
 *
 * @author ytkj
 */
class Agentexcel extends AdminCommon {
  
  //对账表记录
  public function  index(){
    $username = Request::instance()->param("agentno", "", "trim");
    $curstate = Request::instance()->param("state", "", "trim");
    $begintime = Request::instance()->param("begintime", "");
    $endtime = Request::instance()->param("endtime", "");
    $pageno = Request::instance()->param("page");
    $excel=new AgentExcelModel();
    $sessionid = session_id();
    $cachename = "Agentexcelindex_where_$sessionid";
    if (!empty($pageno)) {
      if (Cache::has($cachename)) {
        $where = Cache::get($cachename);
      } else {
        $where=["a.state"=>1];
      }
    } else {
      Cache::rm($cachename); //此处必须删除，否则会影响分页结果
      $where=["a.state"=>1];
    }
    if (Request::instance()->isPost()) {
      if (strlen($username)) {
        $where ["a.agentno|b.agentname|b.id"] = ["eq", $username];
      }
      $iend = max(strtotime($begintime), strtotime($endtime));
      $istart = min(strtotime($begintime), strtotime($endtime));

      if (strlen($begintime) && strlen($endtime)) {
        $begintime = date("Y-m-d ", $istart) . "00:00:00";
        $endtime = date("Y-m-d ", $iend) . "23:59:59";
        $where["a.putintime"] = ["between", [$begintime, $endtime]];
      }
      Cache::set($cachename, $where);
    }
    
    $list = $excel->excellist($where, 15);
    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    $this->assign("agentno", $username);
    $this->assign("begintime", $begintime);
    $this->assign("endtime", $endtime);
    $this->assign("curstate", $curstate);
    return $this->fetch();
  }
  
  //删除记录
  public function  delinfo(){
    $id= Request::instance()->param("id");
    $info=AgentExcelModel::get($id);
    $info->state=0;
    if($info->save()){
      return ["state"=>"ok","msg"=>"删除记录成功"];
    } else {
      return ["state"=>"err","msg"=>"系统繁忙，请稍后再试"];
    }
  }
}
