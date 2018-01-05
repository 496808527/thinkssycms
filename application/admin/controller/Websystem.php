<?php
namespace app\admin\controller;
use app\common\controller\AdminCommon;
use app\common\model\WebsiteModel;
use app\common\model\RisksetModel;
use app\common\util\Sqlback;


use think\Request;
use think\Cache;
use think\Config;

class Websystem extends AdminCommon {
  //清除缓存
  public function clearCache(){
    $dirname =RUNTIME_PATH;
    del_caches($dirname);
    \think\Cache::clear();
  }
  
  //基本设置显示
  public function  index(){
    $info = WebsiteModel::get(1);
    $this->assign("info",$info);
    return $this->fetch();
  }
  
  //基本设置
  public function  savewebsys(){
    $parms = Request::instance()->param();
    $info = WebsiteModel::get(1);
    if(empty($info)||$info==null){
      $info =new WebsiteModel();
    }
    foreach ($parms as $key=>$value){
      if(strlen($value)!=0){
        $info->$key=$value;
      } 
    }
    $cacheName="Cache_Websit_Info";
    Cache::rm($cacheName);
    return  $info->save();
  }
  
  //分控设置
  public function  riskset(){
    if(Request::instance()->isGet()){
      $info = RisksetModel::get(1);
      $this->assign("info",$info);
      return $this->fetch();
    }else{
      $parms = Request::instance()->param();
      $info = RisksetModel::get(1);
      if(empty($info)||$info==null){
        $info =new RisksetModel();
      }
      foreach ($parms as $key=>$value){
        if(!empty($value)){
          $info->$key=$value;
        } 
      }
      return  $info->save();
    }
  }
  
  public function backupsql(){
    $config= Config::get("database"); 
    $sql=new Sqlback($config);
    $msg=$sql->backup();
    $this->error($msg);
  }
}
