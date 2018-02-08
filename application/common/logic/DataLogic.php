<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\logic;
use app\common\model\ModelsModel;
use app\common\model\FieldsModel;
use think\Db;
/**
 * Description of DataLogic
 *
 * @author ytkj
 */
class DataLogic {
  /**
   * 创建表
   * @param string $mid  模型ID
   */
  static function CreatTable($mid){
    $modelinfo = ModelsModel::get($mid);
    $sql="DROP TABLE IF EXISTS `".$modelinfo->tablename."`; CREATE TABLE `".$modelinfo->tablename."`(";
    $sql.="`id` int(11) NOT NULL AUTO_INCREMENT,";
    $sql.="PRIMARY KEY (`id`)";
    $sql.=") ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='".$modelinfo->modelname."'";
    return Db::execute($sql);
  }
 

  /**
   * 修改字段信息
   * @param FieldsModel $fieldsmodel 字段信息
   */
  static function AlertTable(FieldsModel $fieldsmodel){
    $modelinfo = ModelsModel::get($fieldsmodel->modelid);
    $sql="show columns from `".$modelinfo->tablename."` like '".$fieldsmodel->fieldname."' ";
    $info = Db::query($sql);
    if(empty($info)){ //字段不存在 添加
      $sql="ALTER TABLE ".$modelinfo->tablename." ADD ".$fieldsmodel->fieldname." ".$fieldsmodel->fieldtype;
      if($fieldsmodel->isempty==1){  //不允许为空
        $sql.=" NOT NULL";
      }
      if(!empty($fieldsmodel->fieldvalue)){  //默认值
        $sql.=" DEFAULT ".$fieldsmodel->fieldvalue;
      }
      $sql .=" COMMENT '".$fieldsmodel->fielddiscribe;
      if($fieldsmodel->fieldrelation!="暂无"){
        $sql.=" 关联关系：".$fieldsmodel->fieldrelation;
      }
      $sql.="';";
      if($fieldsmodel->isindex=="1"){
        $sql.="ALTER TABLE ".$modelinfo->tablename." ADD INDEX index_".$fieldsmodel->fieldname." (`".$fieldsmodel->fieldname."`);";
      }
      if($fieldsmodel->isindex=="3"){
        $sql.="ALTER TABLE ".$modelinfo->tablename." ADD UNIQUE index_".$fieldsmodel->fieldname." (`".$fieldsmodel->fieldname."`);";
      }
      
    }else{
      $sql="ALTER TABLE ".$modelinfo->tablename." CHANGE ".$fieldsmodel->fieldname." ".$fieldsmodel->fieldname." ".$fieldsmodel->fieldtype;
      if($fieldsmodel->isempty==1){  //不允许为空
        $sql.=" NOT NULL";
      }
      if(!empty($fieldsmodel->fieldvalue)){  //默认值
        $sql.=" DEFAULT ".$fieldsmodel->fieldvalue;
      }
      $sql .=" COMMENT '".$fieldsmodel->fielddiscribe;
      if($fieldsmodel->fieldrelation!="暂无"){
        $sql.=" 关联关系：".$fieldsmodel->fieldrelation;
      }
      $sql.="';";
      
      $sql.="ALTER TABLE ".$modelinfo->tablename." DROP INDEX index_".$fieldsmodel->fieldname." (`".$fieldsmodel->fieldname."`);";
      $sql.="ALTER TABLE ".$modelinfo->tablename." DROP UNIQUE index_".$fieldsmodel->fieldname." (`".$fieldsmodel->fieldname."`);";
      
      if($fieldsmodel->isindex=="1"){
        $sql.="ALTER TABLE ".$modelinfo->tablename." ADD INDEX index_".$fieldsmodel->fieldname." (`".$fieldsmodel->fieldname."`);";
      }
      if($fieldsmodel->isindex=="3"){
        $sql.="ALTER TABLE ".$modelinfo->tablename." ADD UNIQUE index_".$fieldsmodel->fieldname." (`".$fieldsmodel->fieldname."`);";
      }
    }
    return Db::execute($sql);
  }
  
static function DropField(FieldsModel $fieldsmodel){
    
  }
  
}
