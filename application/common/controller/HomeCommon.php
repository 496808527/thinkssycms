<?php
namespace app\common\controller;
use think\Session;

class HomeCommon extends NormalCommon{
  protected $agentno;
  public function __construct(\think\Request $request = null) {
   parent::__construct($request);
   if(!Session::has("userinfo")){
     $this->redirect("index/index");
   }else{
     $this->agentno= Session::get("userinfo")["agentno"];
   }
   
 }
}

