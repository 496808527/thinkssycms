<?php
namespace app\admin\controller;
use \app\common\controller\NormalCommon;
use  \think\Request;
use  \app\common\model\User;
use  \think\Session;
use  \think\Cookie;
use \app\common\model\RoleModel;

class Login extends NormalCommon {
  
   public function  index(){
     if(Request::instance()->isPost()){
        $username = Request::instance()->param("username","","htmlspecialchars");
        $password =  Request::instance()->param('password',"","htmlspecialchars");
        $token= Request::instance()->param("__token__");
        $stoken= Session::get("__token__");
        if($stoken===$token){
           $user=new User();
          $info=$user->Login($username, $password);
          if($info===FALSE){
            $this->fetch();
          }else{
            $roleinfo = RoleModel::get($info->roleid);
            $rolename=$roleinfo->rolename;
           
            $rolename =($info->id==1)?"超级管理员":$rolename;
            Session::set("adminuser",$info->id);
            Cookie::set("roolname",$rolename);
            Cookie::set("nickname",$info->username);
            $this->redirect("index/index");
          }
        }else{
          $this->fetch();
        }
     }else{
        return  $this->fetch();
     }
   }
   
   /**
    * 验证码图片
    */
   public function  image(){
     $config =    [
    'fontSize'    =>    16,    
    'length'      =>    4,   
    "imageH"      =>    41,
    "useNoise"   =>    false,
    "imageW"     =>   120,
    ];
     $captcha=new \think\captcha\Captcha($config);
     return $captcha->entry("imagecode");
   }
   
   
   /**
    * Ajax验证信息，返回
    */
    public function checkuserinfo(){
        $username = Request::instance()->param("username","","htmlspecialchars");
        $password =  Request::instance()->param('password',"","htmlspecialchars");
        $code = Request::instance()->param('code',"","htmlspecialchars");
        !captcha_check($code,"imagecode") && exit(json_encode(array('code'=>10000,'msg'=>'验证码错误')));   
        $user=new User();
        $info = $user->checkLogin($username, $password);
        exit(json_encode($info));
    }
    

}

