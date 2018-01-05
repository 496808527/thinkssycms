<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

if(!function_exists("del_caches")){
  function del_caches($dirname,$self=true){
      if (!file_exists($dirname)) {
         return false;
     }
     if (is_file($dirname) || is_link($dirname)) {
         return unlink($dirname);
     }
     $dir = dir($dirname);
     if ($dir) {
         while (false !== $entry = $dir->read()) {
             if ($entry == '.' || $entry == '..') {
                 continue;
             }
             del_caches($dirname . DIRECTORY_SEPARATOR . $entry);
         }
     }
     $dir->close();
     $self && rmdir($dirname);
  }
}

if(!function_exists("export")){
  function export($files,$filename){
    if(empty($files)){
      return ["state"=>"err","msg"=>"文件写入失败"];
    }
    $zip = new \ZipArchive();
    $filename = $filename . ".zip";
    $zip->open($filename, ZipArchive::CREATE);   //打开压缩包
    foreach ($files as $file) {
      $zip->addFile($file, $file);   //向压缩包中添加文件
    }
    $zip->close();  //关闭压缩包
    foreach ($files as $file) {
      if(file_exists($file)){
          unlink($file); //删除csv临时文件
      }
    }
    return $filename;
  }
}




return [

    "ccie_path" =>  "",  //curl 证书地址path

    "ccie_pwd" =>  "",   //curl 证书密码

    "ccie_sslkey" =>  "", //curl 证书，私匙存放地址  一般与证书存放在同一目录

    "img_maxwidth" =>  "",  //上传图片最大宽度， 单位px    超过做压缩

    "img_maxlength" =>  "",  //上传图片最大长度  单位px    超过做压缩

    "img_maxsize" =>  "2",     //允许上传图片最大大小 单位M

    'img_filetype'  =>  [".jpg",".png",".gif",".bmp",".jpeg"],  //允许上传图片文件的类型

    'upload_filetype'  =>  [],   //允许上传文件的类型

    "upload_maxsize" =>  "",   //允许上传文件最大的大小  单位M
    
    "taglib_pre_load"=>"app\common\taglib\CarTagLib",

];
