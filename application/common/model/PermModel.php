<?php
namespace app\common\model;

use think\Model;


/**
 * 用户模型
 */
class PermModel extends Model{
    protected $table="yt_permission";

  /**
   * 获取去权限列表
   * @param [] $where 查询条件
   * @param $pagesize 是数字的时候，表示每页多少条， True 的时候，表示不分页
   * @return [] 数据列表
   */
  public function permList($where,$pagesize){
      if(empty($where["parentid"])){
        $where["parentid"]=0;
      }
      if($pagesize===TRUE){
        $list= self::where($where)->select();
      } else {
        $pagesize= is_numeric($pagesize)?$pagesize:15;
        $list= self::where($where)->paginate($pagesize);
      }
      
      foreach ($list as $key=>$perm){
        $where["parentid"]=$perm["id"];
        $perm["childs"]= $this->permChilds($where);
        $list[$key]=$perm;
      }
      return $list;
  }
  

  /**
   * 获取某条权限的记录
   */
  public function permInfo($where){
    return self::get($where);
  }
  
  /**
   *  前端下拉列表控件
   * @param [] $data 权限列表
   * @param string $split 选项表示符
   * @return string  前端html 字符串
   */

  public function htmlSelect($data,$split,$curid=""){
    if(!empty($data)){ 
      $tree="";
      foreach ($data as $key =>$value){
        $selected  =  ($curid === $value["id"])?"selected":"";
        $temp="";
        for($i=0;$i<$value["level"];$i++){
          $temp.=$split;
        }
        $tree.='<option value="'.$value["id"].'" '.$selected.' >'.$temp."|--".$value["permname"].'</option>';
        $tree.= $this->htmlSelect($value["childs"], $split,$curid);
      }
      return $tree;
    }else{
      return "";
    }
  }
  
 

  /**
   * 下级权限集合
   */
  public function permChilds($where){
    if(empty($where)){
      return [];
    }
    $list = self::where($where)->select();
    if(empty($list)){
      return [];
    }else{
      foreach ($list as $key=>$perm){
        if(array_key_exists("id", $where)){
          unset($where["id"]);
        }
        $where["parentid"]=$perm["id"];
        $perm["childs"]= $this->permChilds($where);
        $list[$key]=$perm;
      }
      return $list;
    }
  }
  
  public function  permParents($permid,$list=[]){ 
    $info= self::get($permid);
    if(empty($info)){
      return  $list;
    }else{
      $list[]=$info;
      return $this->permParents($info["parentid"],$list);
    }
  }
  
  /**
   * 
   * @param type $childs
   * @param type $list   
   * @return []
   */
  public function  formatPerm($childs,&$list=[]){
    if(empty($childs)){
      return $list;
    }else{
      foreach ($childs as $perm){
        if($perm["actiontype"]=="1"){
          $list["PanelAction"][]=$perm;
        }else if($perm["actiontype"]=="2"){
          $list["LineAction"][]=$perm;
        }
        $this->formatPerm($perm["childs"],$list);
      }
      return $list;
    }
  }
  
  /**
   * 根据条件查询权限表的某一列数据， 最好返回id
   */
  public function perm_list($where,$feild=""){
    if(empty($feild)){
       return self::where($where)->select();
    }else{
      return self::where($where)->column($feild);
    }
   
  }
  
 
  


}