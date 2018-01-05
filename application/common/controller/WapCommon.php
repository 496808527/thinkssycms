<?php
namespace app\common\controller;
use think\Session;
use think\Request;



class WapCommon extends NormalCommon{
    public function __construct(\think\Request $request = null) {
      parent::__construct($request);
      if(!Session::has("frontuid")){
        $this->redirect("wap/login/index");
      }
    }
}

