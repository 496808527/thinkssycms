<?php
namespace app\admin\controller;
use app\common\controller\AdminCommon;
use app\common\model\User;
use app\common\logic\WelComeLogic;
use app\common\util\imageutil;
use think\Request;
use think\Session;


class Index extends AdminCommon
{
 
    public function index(){
  
      return $this->fetch();
    }
    
    public function header(){
      return $this->fetch();
    }
    
    
    public function  footer(){
      return $this->fetch();
    }
    
    
    public function  menu(){
      return $this->fetch();
    }
    
    
    public function welcome(){
      $logic=new WelComeLogic();
      $agents = $logic->YesterdayAgents();
      $cars = $logic->YesterdayCars();
      $finance= $logic->YesterdayFinance();
      $orders = $logic->YesterdayOrders();
      $sorts = $logic->YesterdaySort();
      $this->assign("agents",$agents);
      $this->assign("cars",$cars);
      $this->assign("finance",$finance);
      $this->assign("orders",$orders);
      $this->assign("sorts",$sorts);
      return $this->fetch();
    }
    
    public function updatepwd(){
      if(Request::instance()->isPost()){
        $admin= User::get($this->_adminid);
        $oldpwd= Request::instance()->param("oldpwd","");
        $password= Request::instance()->param("password","");
        $pwdag= Request::instance()->param("pwdag","");
        if(strlen($oldpwd)===0){
          return  "原密码不能为空！";
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
        if(md5($oldpwd)!=$admin["password"]){
          return "原密码错误，请重新填写";
        }
        $admin->password=md5($password);
        if($admin->save()){
          return 1;
        }else{
          return  "系统繁忙，请稍后再试！";
        } 
      }else{
        return $this->fetch();
      }
    }
    
    public function loginout(){
      Session::delete("adminuser");
      $this->redirect("login/index");
    }
           
    
}
