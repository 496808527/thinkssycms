<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\common\model;
use app\common\util\Arrayutil;


/**
 * Description of AgentExcelModel
 *
 * @author ytkj
 */
class AgentExcelModel extends \think\Model{
  protected  $table="yt_agentexcel";
  
  public function  excellist($where ,$pagesize){
     //审核订单
    $fix = \think\Config::get("database.prefix");
    $field = "a.*,b.agentname,b.agentarea,b.id as uid";

    if ($pagesize === TRUE) {
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->select();
    } else {
      $pagesize = is_numeric($pagesize) ? $pagesize : 15;
      $list = $this->alias("a")->join($fix . "agent b", "a.agentno=b.agentno", "left")->field($field)->where($where)->paginate($pagesize);
    }
    foreach ($list as $key=>$item){
      $files =Arrayutil::stringToArray($item["filepath"], ";");
      $datafile=[];
      foreach ($files as $file){
       $tempitem["path"]=$file;
       $pathinfo= pathinfo($file);
       $fileinfo= FilesModel::get(["filename"=>$pathinfo["basename"]]);
       $tempitem["name"]=$fileinfo["oldfilename"];
       $datafile[]=$tempitem;
      }
      $item["filepath"]=$datafile;
              
      $list[$key]=$item;
    }
    
    return $list;
  }
  
}
