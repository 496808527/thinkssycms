<?php
namespace app\common\model;

use think\Model;

/**
 * 后台用户模型
 */
class User extends Model{
    protected $table="yt_admins";

    public function checkLogin($username,$password){
      $where=["username"=>$username];
      $info = self::get($where);
      $data=[
            "code"=>100,
            "msg"=>"没有此用户"
        ];
      if(empty($info)){
        return $data;
      }else{
        $password= md5($password);
        if($password===$info["password"]){
          $data["msg"]="登录成功";
          $data["code"]=200;
        }else{
           $data["msg"]="密码错误";
           $data["code"]=100;
        }
        return $data;
      }
    }
    
    public function Login($username,$password){
      $username= trim($username);
      $password= trim($password);
      $password= md5($password);
      $where=["username"=>$username];
      $info = self::get($where);
      if(empty($info)){
        return false;
      }else{
          if($password===$info["password"]){
             return $info;
          }else{
            return false;
          }
      }
    }
    
    public function  Regist($data){
      if(!empty($data)){
       return  $this->data($data)->save();
      }else{
        return false;
      }
    }
    
    /**
     * 
     * @param [] $where 查询条件
     * @param int|bool $pagesize 传入true 时查询所有的， int 表示每页多少条
     * @return [] 查询结果 
     */
    public function adminlist($where,$pagesize=15){
      $field="a.*,r.rolename";
      if($pagesize===TRUE){
        $list= $this->alias("a")->join("yt_roles r","a.roleid=r.id","left")->where($where)->field($field)->select();
      } else {
        $pagesize= is_numeric($pagesize)?$pagesize:15;
        $list= $this->alias("a")->join("yt_roles r","a.roleid=r.id","left")->where($where)->field($field)->paginate($pagesize);
      }
      return $list;
    }
}