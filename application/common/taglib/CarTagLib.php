<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CarTagLib
 *
 * @author ytkj
 */

namespace app\common\taglib;

use think\template\TagLib;

class CarTagLib extends TagLib {

  protected $tags = [
      // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
      'carnotempty' => ['attr' => 'name', 'close' => 1],
  ];

  public function tagCarnotempty($attr, $content) {
    $name = $tag['name'];
    $name = $this->autoBuildVar($name);
    $parseStr = '<?php if(!empty('.$name.')||strlen(' . $name . ') )?>' . $content . '<?php endif; ?>';
    return $parseStr;
  }

}
