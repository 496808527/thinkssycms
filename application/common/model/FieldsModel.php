<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;
use think\Model;

/**
 * Description of FieldsModel
 *
 * @author ytkj
 */
class FieldsModel  extends Model {
  protected  $table='yt_fields';
  
  public function getmodelfields($modelid){
    if(empty($modelid)){
      return [];
    }else{
      $list=self::where(["modelid"=>$modelid])->select();
    }
    return $list;
  }
  
}
