<?php
namespace app\common\model;

use think\Model;


/**
 * 角色模型
 */
class RoleModel extends Model{
    protected $table="yt_roles";

  /**
   * 按条件分页
   */
  public function roleList($where,$pagesize=15){
    if($pagesize===TRUE){
      return self::where($where)->select();
    }else{
        $pagesize= is_numeric($pagesize)?$pagesize:15;
       return self::where($where)->paginate($pagesize);
    }
   
  }
  
  /**
   * 根据角色获取用户信息
   */
  public function  roleUsers($roleid=0){
    $roleid = intval($roleid);
    if(0===$roleid){
      return [];
    }else{
      return  User::where(["roleid"=>$roleid])->column("username","id");
    }
  }
  
  /**
   * 
   * @param int $roleid  角色ID
   * @param bool $getAll 默认是false 只返回权限的ID集合 ，true 返回所有权限的详细信息
   * @return []
   */
  public function  rolePermList($roleid=0,$getAll=false){
    $roleid = intval($roleid);
    $permids = (new rolemap())->getPermList($roleid);
    if($getAll===FALSE){
      return $permids;
    }else{
       $where["id"]=["in",$permids];
       return (new PermModel())->permList($where, TRUE);
    }
  }
  
  /**
   * 
   */
  public function getRolePermIds($parentid,$roleid){
    $where["parentid"]=$parentid;
    $list = (new PermModel())->perm_list($where,"id");
    if(empty($list)){
      return [];
    }
    unset($where);
    $where=[
        "roleid"=>["eq",$roleid],
        "permid"=>["in",$list],
    ];
    return (new rolemap())->getRolePermList($where, "permid");   
  }

}