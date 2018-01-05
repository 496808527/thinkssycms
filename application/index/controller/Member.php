<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\index\controller;

use app\common\controller\HomeCommon;
use app\common\model\Banklog;
use app\common\model\AgentModel;

use think\Request;
use think\Session;
use think\Cache;

/**
 * Description of Member
 *
 * @author ytkj
 */
class Member extends HomeCommon {

  public function updatebank() {
    if (Request::instance()->isPost()) {
      $parm = Request::instance()->param();
      $parm= \app\common\util\CometUtil::trimparms($parm);
      $mobile = $parm["username"];
      $newbank = $parm["newbank"];
      $code = Request::instance()->param("code");
      $cacheName = "Mobile_" . $mobile;
      if (strlen($mobile) == 0 || strlen($newbank) == 0 || strlen($code) == 0) {
        return ["state" => "err", "msg" => "请填写完整的信息"];
      }
      if (!Cache::has($cacheName)) {
        return ["state" => "err", "msg" => "对不起，手机号码和短信手机不一致"];
      }
      if (Session::get("mobilecode") != $code) {
        return ["state" => "err", "msg" => "对不起，验证码错误"];
      }
      $agent = AgentModel::get(["agenttel" => $mobile]);
      if (empty($agent)) {
        return ["state" => "err", "msg" => "非常抱歉，您的手机没有注册，不能用于修改银行卡"];
      }
      if($agent["agentno"]!= $this->agentno){
        return  ["state"=>"err","msg"=>"此电话和登录电话号码不一致，不能修改"];
      }

      $banklog = Banklog::get(["agentno" => $this->agentno]);
      if (!empty($banklog)) {
        return ["state" => "err", "msg" => "每个人只能修改一次银行卡信息，如果需要修改，请联系客服"];
      }
      $agentinfo = Session::get("userinfo");
      $banklog = new Banklog();
      $banklog->agentno = $this->agentno;
      $banklog->oldbankcard = $agentinfo["agentbankcard"];
      $banklog->newbankcard = $parm["newbank"];
      $banklog->putintime = date("Y-m-d H:i:s");
      $banklog->bankinfo=$parm["bankinfo"];
      $banklog->bankowner=$parm["bankowner"];
      $banklog->state = 0;
      if($banklog->save()){
         return ["state" => "ok", "msg" => "修改银行卡申请提交成功，请等待管理员审核"];
      }else{
        return ["state" => "err", "msg" => "系统繁忙，请稍后再试"];
      }
    } else {
      $this->assign("name", "修改银行卡信息");
      return $this->fetch();
    }
  }

}
