<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;

use think\Model;
/**
 * Description of Models
 *
 * @author ytkj
 */
class ModelsModel extends Model {
  protected  $table="yt_models";
  
  public function  modellist($where,$pagesize){
    if($pagesize===TRUE){
      return self::where($where)->select();
    }else{
        $pagesize= is_numeric($pagesize)?$pagesize:15;
       return self::where($where)->paginate($pagesize);
    }
  }
}
