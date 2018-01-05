<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;

use app\common\util\stringutil;

use think\Model;

/**
 * Description of Resource
 *
 * @author ytkj
 */
class ResourceModel extends Model {
  protected  $table="yt_resource";
  
  public function resourselist($where ,$pagesize){
    $fix = \think\Config::get("database.prefix");
    
  $field="a.*,b.nickname";
    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix."admins b","a.adminid=b.id","left" )->field($field)->where($where)->order("a.id desc")->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix."admins b","a.adminid=b.id","left" )->field($field)->where($where)->order("a.id desc")->paginate($pagesize);
    }
    
    foreach ($list as $key=>$item){
      $filename=$item["filepath"];
      $find=DIRECTORY_SEPARATOR;          
      $index=stringutil::find_strIndex($filename, $find,FALSE)+1;
      $filename  = stringutil::substr($filename,$index);
      $fileinfo= FilesModel::get(["filename"=>$filename]);
      $item["oldfilename"]=$fileinfo["oldfilename"];
      $list[$key]=$item;
    }
    
    return  $list;
    
  }
}
