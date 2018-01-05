<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/11
 * Time: 12:09
 */
namespace app\common\util;
use think\Image;

class imageutil{

    var $error=[

        "1000"=>["code"=>1000,"msg"=>"上传成功" ],

        "1001"=>["code"=>1001,"msg"=>"上传图片过大"],

        "1002"=>["code"=>1002,"msg"=>"上传图片类型错误"],

        "1003"=>["code"=>1003,"msg"=>"保存图片错误"],
        
        "1004"=>["code"=>1003,"msg"=>"图片不存在"],

    ];

    static function  thumbImg($fileimg){
      if(!file_exists($fileimg)){
        return ["state"=>"err","msg"=> $this->error["1004"]["msg"]];
      }
      $img= Image::open($fileimg);
      $img->thumb(375, 675);
      $img->save("1.png");
    }
}