<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\controller;
use think\Controller;
use \think\Cache;
use think\Session;
use think\Request;


/**
 * Description of NormalCommon
 *
 * @author Administrator
 */

class NormalCommon extends Controller {
  //put your code here
  public function _initialize() {
    parent::_initialize();
    $cacheName="Cache_Websit_Info";
    if(Cache::has($cacheName)){
      $webinfo= Cache::get($cacheName);
    }else{
      $webinfo= \app\common\model\WebsiteModel::get(1);
      Cache::set($cacheName, $webinfo);
    }
    $this->assign("webinfo",$webinfo);
    $this->assign("pindex", Request::instance()->param("pindex","1"));
  }
  
  protected function checkuser(){
    if(!Session::has("frontuid")){
      $this->redirect("wap/login/index");
    }
  }
}
