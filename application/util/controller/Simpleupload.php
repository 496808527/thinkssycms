<?php

namespace app\util\controller;

use \think\Request;
use think\Session;
use \think\Controller;
use \app\common\model\FilesModel;

class Simpleupload extends Controller {

  var $mime = [".txt", ".zip", ".doc", ".docx", ".xls", ".xlsx", ".gif", ".jpg", ".png", ".ico", ".bmp",".csv"];

  function index() {
    $uploadid = md5(uniqid());
    $savepath = Request::instance()->param('savepath');
    $control = Request::instance()->param('con');
    $savepath = $savepath ? $savepath : "util";
    Session::set("uploadid", $uploadid);
    $this->assign("uploadid", $uploadid);
    $this->assign("savepath", $savepath);
    $this->assign("control", $control);
    return $this->fetch();
  }

  function upload() {
    if (Request::instance()->isPost()) {
      $uploadid = Request::instance()->param("uploadid");
      $total = Request::instance()->param("blockcount");
      $index = Request::instance()->param("index");
      $size = Request::instance()->param("size");
      $savepath = Request::instance()->param("path");
      $savepath = $savepath ? $savepath : "util";
      $oldname = Request::instance()->param("filename");
      $type = Request::instance()->param("filetype");
   
      if ($uploadid == Session::get("uploadid")) {
        $info = Request::instance()->file("data")->getInfo();
        if ($info["error"] == 0) {
          $data = file_get_contents($info["tmp_name"]);
          $isize = file_put_contents($uploadid, $data, FILE_APPEND);
          if ($isize >= $size) {
            if ($total == $index + 1) {
              $fi = new \finfo(FILEINFO_MIME_TYPE);
              $mime_type = $fi->file($uploadid);
              $ext = $this->getMimeType($mime_type,$type);
              if (!in_array($ext, $this->mime)) {
                if ($ext == "") {
                  if (!in_array($type, $this->mime)) {
                    if (file_exists($uploadid)) {
                      @unlink($uploadid);
                    }
                    return ["state" => "err", "msg" => "非法的上传文件"];
                  }else{
                    $ext=$type;
                  }
                }
              }
              
              $filesize = filesize($uploadid);
              $md5filename = md5_file($uploadid);

              $fileinfo = FilesModel::get(["md5name" => $md5filename]);
              if (empty($fileinfo)) {
                $dirname = "Upload" . DIRECTORY_SEPARATOR . $savepath . DIRECTORY_SEPARATOR . date("Y-m-d", time()) . DIRECTORY_SEPARATOR;
                $this->mkdirs($dirname);
                $filename = time() . $ext;
                $indexfile = $dirname . $filename;
                copy($uploadid, $indexfile);
                @unlink($uploadid);
                $fileinfo = new FilesModel();
                $fileinfo->md5name = $md5filename;
                $fileinfo->filename = $filename;
                $fileinfo->savepath = $dirname;
                $fileinfo->size = $filesize;
                $fileinfo->oldfilename = $oldname;
                $fileinfo->save();
              } else { 
                @unlink($uploadid);
                $indexfile = $fileinfo->savepath . $fileinfo->filename;
              }
              $uploadid = md5(uniqid());
              Session::set("uploadid", $uploadid);
              $result = ["state" => "ok", "index" => $index + 1, "total" => $total, "msg" => "上传成功", "filepath" => $indexfile, "uploadid" => $uploadid, "oldname" => $oldname];
            } else {
              $result = ["state" => "doing", "index" => $index + 1, "total" => $total];
            }
            return $result;
          } else {
            return ["msg" => "数据包丢失", "state" => "err"];
          }
        } else {
          return ["msg" => "上传失败", "state" => "err"];
        }
      } else {
        return ["msg" => "非法上传", "state" => "err", "uploadid" => $uploadid];
      }
    }
  }

  function getMimeType($type,$ext) {
    switch ($type) {
       case "application/vnd.openxmlformats-officedocument.wordprocessingml.document":
        $_ext = ".docx";
        break;
      case "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet":
        $_ext = ".xlsx";
        break;
      case "application/zip":
        if($ext=="application/vnd.openxmlformats-officedocument.wordprocessingml.document"){  //docx 上传上来的类型是zip 这里处理 ，还原成原来的文件类型
           $_ext = ".docx";
        }else if($ext=="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
          $_ext = ".xlsx";
        }else{
           $_ext = ".zip";
        }
        break;
      case "application/msword":
        $_ext = ".doc";
        break;

      case "application/vnd.ms-excel":
        $_ext = ".xls";
        break;
      case "application/vnd.ms-office":
        $_ext = ".xls";
        break;
      case "text/plain":
        $_ext = ".txt";
        break;
      case "image/jpeg":
        $_ext = ".jpg";
        break;
      case "image/gif":
        $_ext = ".gif";
        break;
      case "image/png":
        $_ext = ".png";
      case "image/bmp":
        $_ext = ".bmp";
        break;
      case "image/x-icon":
        $_ext = ".ico";
        break;
      case "audio/mpeg":
        $_ext = ".mp3";
        break;
      case "video/mp4":
        $_ext = ".mp4";
        break;
      default:
        $_ext = "";
    }
    return $_ext;
  }

  function mkdirs($dir, $mode = 0777) {
    if (is_dir($dir) || @mkdir($dir, $mode))
      return TRUE;
    if (!$this->mkdirs(dirname($dir), $mode))
      return FALSE;
    return @mkdir($dir, $mode);
  }

}
