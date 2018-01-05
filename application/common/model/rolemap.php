<?php

namespace app\common\model;

use think\Model;

class rolemap extends Model {

  public function getPermList($roleid = 0) {
    $roleid = intval($roleid);
    if ($roleid === 0) {
      return [];
    } else {
      return self::where(["roleid" => $roleid,"status" => 1])->column("permid");
    }
  }

  public function addrolemap($data) {
    if (empty($data)) {
      return  [];
    } else {
      $this->data($data);
      return $this->save();
    }
  }

  public function delrolemap($data) {
    if (empty($data)) {
      return  [];
    } else {
      return self::where($data)->delete();
    }
  }
  
  public  function  getRolePermList($where,$field=""){
    if(empty($field)){
      return self::where($where)->select();
    }else{
      return self::where($where)->column($field);
    }
  }

}
