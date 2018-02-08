<?php

namespace app\admin\controller;

use app\common\controller\AdminCommon;
use app\common\model\ModelsModel;
use app\common\util\CometUtil;
use app\common\logic\DataLogic;
use app\common\model\FieldsModel;

use think\Request;

class Modelbuild extends AdminCommon {

  public function index() {
    $model=new ModelsModel();
    $where=[];
    $list=$model->modellist($where, 15);
    $page=$list->render();
    $this->assign("list",$list);
    $this->assign("page",$page);
    return $this->fetch();
  }

  //新增模型
  public function addmodel() {
    $id= Request::instance()->param("id");
    if($id){
      $info= ModelsModel::get($id);
    }else{
      $info=null;
    }
    if (Request::instance()->isPost()) {
      $parms= Request::instance()->param();
      $parms= CometUtil::trimparms($parms);
      if(empty($info)){
        $info=new ModelsModel();
      }
      unset($parms["id"]);
      foreach ($parms as $key=>$value){
        $info->$key=$value;
      }
      if($info->save()!==FALSE){
        return ["state"=>"ok","msg"=> $this->_sucmsg];
      }else{
        return ["state"=>"err","msg"=> $this->_errmsg];
      }
    } else {
      $this->assign("info", $info);
      return $this->fetch();
    }
  }
  
  //模型字段表
  public function  fields(){
    $id= Request::instance()->param("id");
    $model=new ModelsModel();
    $list=$model->getmodelfields($id);
   
    if(empty($list)){  //判断表是否存在， 如果不存在 创建表
      DataLogic::CreatTable($id);
    }
    $this->assign("list",$list);
    $this->assign("id",$id);
    return $this->fetch();
  }
  
  public function addfield(){
    $parms= Request::instance()->param();
    $parms= CometUtil::trimparms($parms);
    $id= Request::instance()->param("id");
    if(Request::instance()->isPost()){
      $fieldinfo= FieldsModel::get(["modelid"=>$parms["modelid"],"fieldname"=>$parms["fieldname"]]);
      if(empty($fieldinfo)){
        $fieldinfo=new FieldsModel();
      }
      foreach ($parms as $key=>$value){
        $fieldinfo->$key=$value;
      }
      if($fieldinfo->save()!==false){
        DataLogic::AlertTable($fieldinfo);      
        return ["state"=>"ok", "msg"=>$this->_sucmsg];
      }else{
        return ["state"=>"err", "msg"=>$this->_errmsg];
      }
      
    }else{
      $mid= Request::instance()->param("mid");
      if(strlen($id)){
         $fieldinfo= FieldsModel::get($id);
         $mid=$fieldinfo->modelid;
      }else{
         $fieldinfo= null;
      }
      $this->assign("mid",$mid);
      $this->assign("info",$fieldinfo);
      return $this->fetch();
    }
    
  }

}
