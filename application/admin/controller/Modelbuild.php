<?php

namespace app\admin\controller;

use app\common\controller\AdminCommon;
use app\common\model\ModelsModel;
use app\common\util\CometUtil;
use app\common\logic\DataLogic;
use app\common\model\FieldsModel;
use app\common\util\Arrayutil;
use think\Request;

class Modelbuild extends AdminCommon {

  private $inputtype = [1 => "文本框", 2 => "选择框", 3 => "文本域", 4 => "多项选择", 5 => "单项选择", 6 => "富文本框", 7 => "图片",];
  private $indextype = [1 => "普通索引", 2 => "不是索引", 3 => "唯一索引"];
  private $isempty = [1 => "允许为空", "2" => "不允许为空"];

  public function __construct(Request $request = null) {
    parent::__construct($request);
    $this->assign("inputtype", $this->inputtype);
    $this->assign("indextype", $this->indextype);
    $this->assign("isempty", $this->isempty);
  }

  public function index() {
    $model = new ModelsModel();
    $where = [];
    $list = $model->modellist($where, 15);
    $page = $list->render();
    $this->assign("list", $list);
    $this->assign("page", $page);
    return $this->fetch();
  }

  //新增模型
  public function addmodel() {
    $id = Request::instance()->param("id");
    if ($id) {
      $info = ModelsModel::get($id);
    } else {
      $info = null;
    }
    if (Request::instance()->isPost()) {
      $parms = Request::instance()->param();
      $parms = CometUtil::trimparms($parms);
      if (empty($info)) {
        $info = new ModelsModel();
      }
      unset($parms["id"]);
      foreach ($parms as $key => $value) {
        $info->$key = $value;
      }
      if ($info->save() !== FALSE) {
        return ["state" => "ok", "msg" => $this->_sucmsg];
      } else {
        return ["state" => "err", "msg" => $this->_errmsg];
      }
    } else {
      $this->assign("info", $info);
      return $this->fetch();
    }
  }

  //模型字段表
  public function fields() {
    $id = Request::instance()->param("id");
    $model = new ModelsModel();
    $list = $model->getmodelfields($id);
    if (empty($list)) {  //判断表是否存在， 如果不存在 创建表
      DataLogic::CreatTable($id);
    }
    $this->assign("list", $list);
    $this->assign("id", $id);
    return $this->fetch();
  }

  //模型字段的添加或者修改
  public function addfield() {
    $parms = Request::instance()->param();
    $parms = CometUtil::trimparms($parms);
    $id = Request::instance()->param("id");
    if (Request::instance()->isPost()) {
      $fieldinfo = FieldsModel::get(["modelid" => $parms["modelid"], "fieldname" => $parms["fieldname"]]);
      if (empty($fieldinfo)) {
        $fieldinfo = new FieldsModel();
      }
      foreach ($parms as $key => $value) {
        if ($key == "fieldoptions") {
          if (strlen($value)) {
            $array = Arrayutil::stringToArray($value, ";");
            foreach ($array as $a => $temp) {
              $array[$a] = Arrayutil::stringToArray($temp, ":");
            }
            $fieldinfo->$key = Arrayutil::arrayToJson($array);
          }
        } else {
          $fieldinfo->$key = $value;
        }
      }
      if ($fieldinfo->save() !== false) {
        DataLogic::AlertTable($fieldinfo);
        return ["state" => "ok", "msg" => $this->_sucmsg];
      } else {
        return ["state" => "err", "msg" => $this->_errmsg];
      }
    } else {
      $mid = Request::instance()->param("mid");
      if (strlen($id)) {
        $fieldinfo = FieldsModel::get($id);
        $mid = $fieldinfo->modelid;
      } else {
        $fieldinfo = null;
      }
      $this->assign("mid", $mid);
      $this->assign("info", $fieldinfo);
      return $this->fetch();
    }
  }

  //模型字段的删除
  public function delfield() {
    $id = Request::instance()->param("id");
    if ($id) {
      $fieldinfo = FieldsModel::get(["id" => $id]);
      DataLogic::DropField($fieldinfo);
      if ($fieldinfo->delete()) {
        return ["state" => "ok", "msg" => $this->_sucmsg];
      } else {
        return ["state" => "err", "msg" => $this->_errmsg];
      }
    } else {
      return ["state" => "err", "msg" => "非法的参数传入"];
    }
  }

}
