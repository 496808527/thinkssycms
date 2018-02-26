<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\controller;
use app\common\controller\AdminCommon;

/**
 * Description of News
 *
 * @author ytkj
 */
class News extends AdminCommon {
  
  public function newslist(){
    $this->modelid;
    
    return $this->fetch();
  }
  
}
