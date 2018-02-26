<?php

namespace app\common\controller;

use \think\Session;
use app\common\model\User;
use app\common\model\RoleModel;
use app\common\model\PermModel;
use app\common\util\Arrayutil;
use think\Cache;
use think\Request;

class AdminCommon extends NormalCommon {

  protected $curRoleid; //当前角色ID
  protected $curPermid;  //当前权限ID
  protected $modelid;
  protected $_adminid;
  protected $_errmsg="系统繁忙，请稍后再试";
  protected $_sucmsg="恭喜您，操作成功";

  public function __construct(\think\Request $request = null) {

    parent::__construct($request);
    if (!Session::has("adminuser")) {
      $this->redirect("login/index");
    }
    $this->_adminid = Session::get("adminuser");
    $this->roleperm();
    $this->headmenu();
    $this->actionopt();
  }

  /**
   * 生成面包屑导航条
   */
  private function headmenu() {
    $this->curPermid = $tagrole = Request::instance()->param("tagrole");
    $url = "role/index";
    if (!empty($tagrole)) {
      $roleid = $this->curRoleid;
      $role = new RoleModel();
      $menulist = $role->rolePermList($roleid);
      if (in_array($tagrole, $menulist)) {
        $list = (new PermModel())->permParents($tagrole);
        $menustr = '<i class="Hui-iconfont">&#xe67f;</i> 首页 ';
        $list = Arrayutil::order_arraybykey($list, FALSE);
        foreach ($list as $menu) {
          $menustr .= '<span class="c-gray en">&gt;</span> ' . $menu["permname"];
        }
        $this->assign("menustr", $menustr);
      } else {
        $this->error("对不起，您暂时没有权限，请联系管理员", $url);
      }
    }
  }

  /**
   * 获取用户的权限，并且缓存
   */
  private function roleperm() {
    $adminid = Session::get("adminuser");
    $user = new User();
    $admin = $user->get($adminid);
    $this->curRoleid = $roleid = $admin->roleid;
    $role = new RoleModel();
    $caCheName = "Cache_" . $adminid . "_" . $roleid;
    if (!Cache::has($caCheName)) {
      $menulist = $role->rolePermList($roleid, TRUE);
      Cache::set($caCheName, $menulist, 0);
    } else {
      $menulist = Cache::get($caCheName);
    }
    $this->assign("menulist", $menulist);
  }

  private function actionopt() {
    $permid = $this->curPermid;
    if (!empty($permid)) {
      $permModel = new PermModel();
      $role = new RoleModel();
      $info = $permModel->get($permid);
      $permids = $role->getRolePermIds($permid, $this->curRoleid);
      if (!empty($permids)) {
        $where = [
            "id" => ["in", $permids]
        ];
        $childs = $permModel->permChilds($where);
        $childs = $permModel->formatPerm($childs);
        $this->assign("actions", $childs);
      }
      if(strlen($info["modelid"])){
        $this->modelid=$info["modelid"];
      }
      $this->assign("title", $info["permname"]);
    }
  }

}
