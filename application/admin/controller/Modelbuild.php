<?php

namespace app\admin\controller;
use app\common\controller\AdminCommon;

class Modelbuild extends AdminCommon  {
  
  public function  index(){
    
    return $this->fetch();
  }
  
}
