<?php

namespace app\admin\controller;

use app\common\controller\AdminCommon;
use app\common\model\RoleModel;
use app\common\model\PermModel;
use app\common\util\Arrayutil;
use app\common\model\User;
use think\Request;

class Role extends AdminCommon {

  private $Action_Perm = [
      "0" => "栏目",
      "1" => "权限",
      "2" => "操作"
  ];
  
  private $Jump_Way=[
      "1" => "新开弹窗",
      "2" => "询问弹窗",
      "3" => "静默窗口",
      "4" => "询问静默窗口",
      "5" => "普通跳转"
  ];

  /**
   * 
   * 角色列表
   */
  public function index() {
    $role = new RoleModel();
    $where = [];
    $info = $role->roleList($where, 10);
    $page = $info->render();
    $this->assign("list", $info);
    $this->assign("page", $page);
    return $this->fetch();
  }

  /**
   * 权限列表
   */
  public function roleperm() {
    $perm = new PermModel();
    $list = $perm->permList([], 10);
    $page = $list->render();
    $this->assign("page", $page);
    $this->assign("list", $list);
    return $this->fetch();
  }

  /**
   * 角色编辑
   */
  public function roleedit() {
    
    $role = new RoleModel();
    $id = intval(Request::instance()->param("id"));
    if (Request::instance()->isGet()) {
      $info = null;
      if ($id != 0) {
        $info = $role->get($id);
      }
      $this->assign("info", $info);
      return $this->fetch();
    } else {
      if ($id != 0) {
        $role = $role->get($id);
        $role->rolename = trim(Request::instance()->param("rolename"));
        $role->remark = trim(Request::instance()->param("remark"));
        $role->state = trim(Request::instance()->param("state"));
      } else {
        $role->rolename = trim(Request::instance()->param("rolename"));
        $role->remark = trim(Request::instance()->param("remark"));
        $role->state = trim(Request::instance()->param("state"));
      }
      return $role->save();
    }
  }

  /**
   * 角色启用或者禁用
   */
  public function roledelet() {
    $id = intval(Request::instance()->param("id"));
    $isdel = intval(Request::instance()->param("isdel"));
    $isdel = $isdel ? 0 : 1;
    $role = new RoleModel();
    if ($id === 0) {
      return $id;
    } else {
      $role = $role->get($id);
      $role->state = $isdel;
      return $role->save();
    }
  }

  /**
   * 给角色分配权限
   */
  public function allotperm() {
    $role = new RoleModel();
    $perm = new PermModel();

    $id = Request::instance()->param("id");
    //根据角色获取权限
    $roleperm = $role->rolePermList($id);
    $info = $role->get($id);
    if (Request::instance()->isGet()) {
      //获取所有权限
      $permlist = $perm->permList([], true);
      $this->assign("roleperm", $roleperm);
      $this->assign("permlist", $permlist);
      $this->assign("info", $info);
      return $this->fetch();
    } else {
      
      if( Request::instance()->has("permid")){
        $data= Request::instance()->param()["permid"];
      }else{
        $data = [];
      }
      $rolemap = new \app\common\model\rolemap;

      $info = empty($info) ? $role : $info;
      $info->rolename = Request::instance()->param("rolename");
      $info->remark = Request::instance()->param("remark");
      $info->save();

      $temp = Arrayutil::compare_Array($roleperm, $data);
      if (empty($temp)) {
        return 0;
      } else {
        foreach ($temp as $key => $v) {
          if ($key === "Add") {
            $dataSet = [];
            foreach ($v as $permid) {
              $dataSet[] = ["permid" => $permid, "roleid" => $id];
            }
            $rolemap->saveAll($dataSet);
          } else {
            $data = [
                "roleid" => $id,
                "permid" => ["in", $v],
            ];
            $rolemap->delrolemap($data);
          }
        }
        return  1;
      }
    }
  }

  /**
   * 添加或编辑权限 
   */
  public function manageperm() {
    $id = Request::instance()->param("id");
    $id = intval($id);
    $perm = new PermModel();
    if (Request::instance()->isGet()) {
      $where = ["level" => ["neq", 2]];
      $permlist = $perm->permList($where, TRUE);
      $tree = $perm->htmlSelect($permlist, "&nbsp;&nbsp;");
      if ($id !== 0) {
        $where = ["id" => $id];
        $info = $perm->permInfo($where);
        $tree = $perm->htmlSelect($permlist, "&nbsp;&nbsp;", $info["parentid"]);
      } else {
        $info = NULL;
      }
      $this->assign("info", $info);
      $this->assign("tree", $tree);
      $this->assign("action_perm", $this->Action_Perm);
      $this->assign("jump_way", $this->Jump_Way);
      return $this->fetch();
    } else {
      $data = Request::instance()->post();
      if ($id !== 0) {
        $perm = $perm->permInfo(["id" => $id]);
        $temp = $perm->save($data);
      } else {
        $temp = $perm->save($data);
      }
      return $temp;
    }
  }

  /**
   * 禁用或者启用权限
   */
  public function delperm() {
    $id = intval(Request::instance()->param("id"));
    $isdel = intval(Request::instance()->param("isdel"));
    $isdel = $isdel ? 0 : 1;
    $perm = new PermModel();
    if ($id === 0) {
      return $id;
    } else {
      $perm = $perm->permInfo(["id" => $id]);
      $perm->status = $isdel;
      return $temp = $perm->save();
    }
  }
  
  
  public function  adminlist(){
    $user = new User();
    $where=["a.id"=>['neq',1]];
    if(Request::instance()->isPost()){
      $username= Request::instance()->param("username");
      if(!empty($username)){
        $where['a.username']=['like',"%$username%"];
      }
    }
    $list = $user->adminlist($where);
    $page = $list->render();
    $this->assign("list",$list);
    $this->assign("page",$page);
    return $this->fetch();
  }
  
  /**
   * 添加管理用户
   */
  public function  addadmin(){
    $id= Request::instance()->param("id");
    if(Request::instance()->isPost()){
      $username= Request::instance()->param("username","");
      $password= Request::instance()->param("password","");
      $pwdag= Request::instance()->param("pwdag","");
      $roleid= Request::instance()->param("roleid","");
      $state= Request::instance()->param("state",0);
      if(strlen($username)===0){
        return "用户名不能为空";
      }
      if(strlen($username)<5){
        return "用户名长度不能小于5";
      }
      
      if(strlen($password)===0|| strlen($pwdag)===0){
        return "用户密码不能为空";
      }
      if(strlen($password)<5||strlen($pwdag)<5){
        return "用户密码长度不能小于5";
      }
      if($password!=$pwdag){
        return "两次密码不一致";
      }
      if(strlen($roleid)===0||!$roleid){
        return "必须选择一个角色";
      }
      if($id&&!empty($id)){
        $user= User::get($id);
      }else{
        $user = new User();
      }
      $user->username=$username;
      $user->password= md5($password);
      $user->roleid=$roleid;
      $user->loginip= Request::instance()->ip();
      $user->regtime= date("Y-m-d H:i:s", time());
      $user->state=$state;
      if( $user->save()!==false){
        return 1;
      }else{
        return 0;
      }
    }else{
      $user = new User();
      $role=new RoleModel();
      $info = null;
      if ($id != 0&&!empty($id)) {
        $info = $user->get($id);
      }
      $rolelist=$role->roleList([], TRUE);
      $this->assign("rolelist",$rolelist);
      $this->assign("info", $info);
      return $this->fetch();
    }
  }
  
  function  userdelet(){
    $id = intval(Request::instance()->param("id"));
    $isdel = intval(Request::instance()->param("isdel"));
    $isdel = $isdel ? 0 : 1;
    $role = new User();
    if ($id === 0) {
      return $id;
    } else {
      $role = $role->get($id);
      $role->state = $isdel;
      return $role->save();
    }
    
  }

}
