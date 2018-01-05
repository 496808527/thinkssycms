<?php
namespace app\index\controller;


use app\common\controller\NormalCommon;
use think\Request;
use app\common\model\AgentModel;
use app\common\util\CometUtil;
use app\common\model\OrdersModel;
use app\common\util\SmsSend;
use app\common\util\Arrayutil;
use app\common\model\AgentExcelModel;
use \think\Session;
use think\Cache;



class Index extends NormalCommon
{
  //返回的状态码
  var $RESULT_LIST = [
	'0' =>'发送成功',
    '101'=>'无此用户',
    '102'=>'密码错',
    '103'=>'提交过快',
    '104'=>'系统忙',
    '105'=>'敏感短信',
    '106'=>'消息长度错',
    '107'=>'错误的手机号码',
    '108'=>'手机号码个数错',
    '109'=>'无发送额度',
    '110'=>'不在发送时间内',
    '111'=>'超出该账户当月发送额度限制',
    '112'=>'无此产品',
    '113'=>'extno格式错',
    '115'=>'自动审核驳回',
    '116'=>'签名不合法，未带签名',
    '117'=>'IP地址认证错',
    '118'=>'用户没有相应的发送权限',
    '119'=>'用户已过期',
    '120'=>'内容不是白名单',
    '121'=>'必填参数。是否需要状态报告，取值true或false',
    '122'=>'5分钟内相同账号提交相同消息内容过多',
    '123'=>'发送类型错误(账号发送短信接口权限)',
    '124'=>'白模板匹配错误',
    '125'=>'驳回模板匹配错误',
    '128'=>'内容解码失败'
   ];

  
  
  //登录
    public function index()
    {
      if(Request::instance()->isPost()){
        $tel= Request::instance()->param("username");
        $password= Request::instance()->param("password");
        $agentinfo= AgentModel::get(["agenttel"=>$tel]);
        $token= Request::instance()->param("__token__");
        $stoken= Session::get("__token__");
        if($token==$stoken){
          if(empty($agentinfo)){
            return ["state"=>"err","msg"=>"此手机尚没有注册，无法登陆"];
          } else {
            $password= md5($password.$agentinfo->regtime);
            if($agentinfo->state=="0"){
              return  ["state"=>"err","msg"=>"此账号已经被禁用，无法登陆， 请联系管理员"];
            }
//            echo $password;die
            if($password==$agentinfo->password){
              Session::set("userinfo",$agentinfo);
              return ["state"=>"ok","msg"=>"登陆成功","tourl"=>url("Orders/personcenter")];
            }else{
              return ["state"=>"err","msg"=>"密码错误"];
            }
          }
        }else{
          return ["state"=>"ok","msg"=>"请不要连续点击","tourl"=>url("index/index")];
        }
        
      }else{
        $this->assign("name","登陆");
        return  $this->fetch();
      }
    }
    
    //注册
    public function reg(){
      if(Request::instance()->isPost()){
        $tel= Request::instance()->param("agenttel");
        $agentname= Request::instance()->param("agentname");
        $agentparent= Request::instance()->param("agentparent");
        $agentaddr= Request::instance()->param("agentaddr");
        $agentidcard= Request::instance()->param("agentidcard");
        $agentbankcard= Request::instance()->param("agentbankcard");
        $agentarea= Request::instance()->param("agentarea");
        $bankaddr= Request::instance()->param("bankaddr");
        $bankname= Request::instance()->param("bankname");
        $agent=AgentModel::get(['agenttel'=>$tel]);
        $password= Request::instance()->param("password");
        if(!empty($agent)){
          return ["state"=>"err","msg"=>"对不起，手机号已经被注册，请更换手机注册"];
        }
        $agent= AgentModel::get(["agentno"=>$agentparent]);
        if(empty($agent)||empty($agentparent)){
          $agentparent="YT0000001"; //云堂总部编号
        }
        
        $agent=new AgentModel();
        $regtime= date("Y-m-d H:i:s");
        $tempagent=CometUtil::buildno();
        $agent->roleid=3;
        $agent->regtime=$regtime;
        $agent->agentno=$tempagent ;
        $agent->agentname=$agentname;
        $agent->agentparent=$agentparent;
        $agent->agenttel=$tel;
        $agent->agentaddr=$agentaddr;
        $agent->agentidcard=$agentidcard;
        $agent->agentbankcard=$agentbankcard;
        $agent->agentarea=$agentarea;
        $agent->password= md5($password.$regtime);
        $agent->bankname=$bankname;
        $agent->bankaddr=$bankaddr;
        $result=$agent->save();
        
        if($result){
          if(Session::has("NewUserOrders")){
            $ids= Session::get("NewUserOrders");
            $orders=new OrdersModel();
            $orders->where("id in ($ids)")->update(["agentno"=>$tempagent,"isrecome"=>"2"]);
            $biz=new \app\common\model\BizModel();
            $biz->where(["ordersid" => ["in", $ids],"state"=>"0"])->update(["state"=>1]);
          }
          return ["state"=>"ok","msg"=>"注册成功，您可以进行报单咯"];
        }else{
          return ["state"=>"err","msg"=>"系统繁忙，注册失败！"];
        }
      }else{
        $agentparent= Request::instance()->param("agentparent");
        $this->assign("parent",$agentparent);
        if(strlen($agentparent)){
           $this->assign("name","推单注册");
        }else{
           $this->assign("name","注册");
        }
        return $this->fetch();
      }
    }
    
    //排单列表
    public function main(){
      $this->assign("name","排单列表");
      return $this->fetch();
    }
    
    public function findpwd(){
      if(Request::instance()->isPost()){
        $mobile= Request::instance()->param("username");
        $password= Request::instance()->param("password");
        $code= Request::instance()->param("code");
        $cacheName="Mobile_".$mobile;
        if(strlen($mobile)==0|| strlen($password)==0|| strlen($code)==0){
          return ["state"=>"err","msg"=>"请填写完整的信息"];
        }
        if(!Cache::has($cacheName)){
          return ["state"=>"err","msg"=>"对不起，手机号码和短信手机不一致"];
        }
        if(Session::get("mobilecode")!=$code){
          return ["state"=>"err","msg"=>"对不起，验证码错误"];
        }
        $agent= AgentModel::get(["agenttel"=>$mobile]);
        if(empty($agent)){
          return ["state"=>"err","msg"=>"非常抱歉，您的手机没有注册，不能用于找回密码"];
        }
        $password=md5($password.$agent->regtime);
        $agent->password=$password;
        if($agent->save()){
             return ["state"=>"ok","msg"=>"修改成功，请重新登录","tourl"=> url("index/index")];
        }else{
             return ["state"=>"err","msg"=>"系统繁忙，请稍后再试 "];
        }  
      }else{
        $this->assign("name","找回密码");
        return $this->fetch();
      }
    }
    
    public function  getmobilecode(){
      $smssend=new SmsSend();
      $code= rand(100000, 999999);
      $mobile= Request::instance()->param("tel");
      if(strlen($mobile)===0){
        return ["state"=>"err","msg"=>"手机号码不能为空！"];
      }
      $agent= AgentModel::get(["agenttel"=>$mobile]);
      
      if(empty($agent)){
        return ["state"=>"err","msg"=>"非常抱歉，您的手机没有注册，不能用于找回密码"];
      }
      $cacheName="Mobile_".$mobile;
      Cache::set($cacheName, $mobile,600);
     
      $msg="【云堂网络科技】您的验证码是".$code.",有效时间10分钟，请妥善保管。";
      $result = $smssend->sendSMS($mobile, $msg);
      $result =$smssend->execResult($result);
      
      if(isset($result[1])){
        Session::set("mobilecode",$code);
        return ["state"=>"ok","msg"=> $this->RESULT_LIST[$result[1]]];
      }else{
        return ["state"=>"err","msg"=>"系统繁忙，请稍后再试 "];
     }
    }
    
    
    public function  upfile(){
      if(Request::instance()->isPost()){
         $agentno = Session::get("userinfo")["agentno"];
         if(empty($agentno)){
           return  ["state"=>"err","msg"=>"sorry，请先登录！","tourl"=>url("index")];
         }
         $files= Request::instance()->param();
         if(empty($files["files"])){
           return ["state"=>"err","msg"=>"请选择上传的文件"];
         }
         $strfile= Arrayutil::arrayTostring($files["files"], ";");
         $agentexcel=new AgentExcelModel();
         $agentexcel->agentno=$agentno;
         $agentexcel->filepath=$strfile;
         $agentexcel->putintime= date("Y-m-d H:i:s");
         if($agentexcel->save()){
           return  ["state"=>"ok","msg"=>"谢谢合作，上传成功","tourl"=>url("orders/personcenter")];
         }else{
           return  ["state"=>"err","msg"=>"系统繁忙，请稍后再试","tourl"=>url("uploadfile")];
         }
      }else{
        $this->assign("name","上传奖金与直通车对账单");
        $uploadid = md5(uniqid());
        $savepath = Request::instance()->param('savepath');
        $savepath = $savepath ? $savepath : "util";
        Session::set("uploadid", $uploadid);
        $this->assign("uploadid", $uploadid);
        $this->assign("savepath", $savepath);
        return $this->fetch();
      }
    }
  
}
