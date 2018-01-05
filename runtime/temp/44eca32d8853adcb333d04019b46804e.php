<?php if (!defined('THINK_PATH')) exit(); /*a:5:{s:68:"E:\PHP\thinkssycms\public/../application/admin\view\index\index.html";i:1515136680;s:67:"E:\PHP\thinkssycms\public/../application/admin\view\index\meta.html";i:1515136680;s:69:"E:\PHP\thinkssycms\public/../application/admin\view\index\header.html";i:1515136680;s:67:"E:\PHP\thinkssycms\public/../application/admin\view\index\menu.html";i:1515139737;s:69:"E:\PHP\thinkssycms\public/../application/admin\view\index\footer.html";i:1515136680;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta name="renderer" content="webkit|ie-comp|ie-stand">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
<meta http-equiv="Pragma" content="no-cache">
<meta HTTP-EQUIV="Cache-Control" CONTENT="no-cache, must-revalidate">
<meta HTTP-EQUIV="expires" CONTENT="0">
<meta http-equiv="Cache-Control" content="no-siteapp" />

<link rel="Bookmark" href="/favicon.ico" >
<link rel="Shortcut Icon" href="/favicon.ico" />
<!--[if lt IE 9]>
<script type="text/javascript" src="__ADMIN__/lib/html5shiv.js"></script>
<script type="text/javascript" src="__ADMIN__/lib/respond.min.js"></script>
<![endif]-->
<link rel="stylesheet" type="text/css" href="__ADMIN__/h-ui/css/H-ui.min.css" />
<link rel="stylesheet" type="text/css" href="__ADMIN__/h-ui.admin/css/H-ui.admin.css" />
<link rel="stylesheet" type="text/css" href="__ADMIN__/lib/Hui-iconfont/1.0.8/iconfont.css" />
<link rel="stylesheet" type="text/css" href="__ADMIN__/h-ui.admin/skin/default/skin.css" id="skin" />
<link rel="stylesheet" type="text/css" href="__ADMIN__/h-ui.admin/css/style.css" />
<!--[if IE 6]>
<script type="text/javascript" src="__ADMIN__/lib/DD_belatedPNG_0.0.8a-min.js" ></script>
<script>DD_belatedPNG.fix('*');</script>
<![endif]-->
<title><?php echo $webinfo["websitename"]; ?></title>
<meta name="keywords" content="<?php echo $webinfo['webkeyword']; ?>">
<meta name="description" content="<?php echo $webinfo['webdisciption']; ?>">
<header class="navbar-wrapper">
	<div class="navbar navbar-fixed-top">
		<div class="container-fluid cl"> 
      <a class="logo navbar-logo f-l mr-10 hidden-xs" href="/aboutHui.shtml"><?php echo $webinfo["websitename"]; ?></a> 
      <a class="logo navbar-logo-m f-l mr-10 visible-xs" href="/aboutHui.shtml">H-ui</a> 
      <span class="logo navbar-slogan f-l mr-10 hidden-xs">v<?php echo $webinfo["versions"]; ?></span>
      <a aria-hidden="false" class="nav-toggle Hui-iconfont visible-xs" href="javascript:;">&#xe667;</a>
			<nav class="nav navbar-nav">
				<ul class="cl">
<!--					<li class="dropDown dropDown_hover"><a href="javascript:;" class="dropDown_A"><i class="Hui-iconfont">&#xe600;</i> 新增 <i class="Hui-iconfont">&#xe6d5;</i></a>
						<ul class="dropDown-menu menu radius box-shadow">
							<li><a href="javascript:;" onclick="article_add('添加资讯','article-add.html')"><i class="Hui-iconfont">&#xe616;</i> 资讯</a></li>
							<li><a href="javascript:;" onclick="picture_add('添加资讯','picture-add.html')"><i class="Hui-iconfont">&#xe613;</i> 图片</a></li>
							<li><a href="javascript:;" onclick="product_add('添加资讯','product-add.html')"><i class="Hui-iconfont">&#xe620;</i> 产品</a></li>
							<li><a href="javascript:;" onclick="member_add('添加用户','member-add.html','','510')"><i class="Hui-iconfont">&#xe60d;</i> 用户</a></li>
						</ul>
					</li>-->
          
          <li class="dropDown dropDown_hover"><a href="javascript:clearCache();" class="dropDown_A"><i class="Hui-iconfont">&#xe6e2;</i> 清除缓存</a></li>
				</ul>
			</nav>
			<nav id="Hui-userbar" class="nav navbar-nav navbar-userbar hidden-xs">
				<ul class="cl">
          <li><?php echo \think\Cookie::get('roolname'); ?></li>
					<li class="dropDown dropDown_hover"> <a href="#" class="dropDown_A"><?php echo \think\Cookie::get('nickname'); ?> <i class="Hui-iconfont">&#xe6d5;</i></a>
						<ul class="dropDown-menu menu radius box-shadow">
							<li><a onclick="baseOpen('修改密码','<?php echo url("index/updatepwd"); ?>',600,460)">修改密码</a></li>
							<li><a href="<?php echo url('index/loginout'); ?>">退出</li>
						</ul>
					</li>
					<li id="Hui-msg"> <a href="#" title="消息"><span class="badge badge-danger">1</span><i class="Hui-iconfont" style="font-size:18px">&#xe68a;</i></a> </li>
					<li id="Hui-skin" class="dropDown right dropDown_hover"> <a href="javascript:;" class="dropDown_A" title="换肤"><i class="Hui-iconfont" style="font-size:18px">&#xe62a;</i></a>
						<ul class="dropDown-menu menu radius box-shadow">
							<li><a href="javascript:;" data-val="default" title="默认（黑色）">默认（黑色）</a></li>
							<li><a href="javascript:;" data-val="blue" title="蓝色">蓝色</a></li>
							<li><a href="javascript:;" data-val="green" title="绿色">绿色</a></li>
							<li><a href="javascript:;" data-val="red" title="红色">红色</a></li>
							<li><a href="javascript:;" data-val="yellow" title="黄色">黄色</a></li>
							<li><a href="javascript:;" data-val="orange" title="橙色">橙色</a></li>
						</ul>
					</li>
				</ul>
			</nav>
		</div>
	</div>
</header>
<aside class="Hui-aside">
	<div class="menu_dropdown bk_2">
   <?php if(!(empty($menulist) || (($menulist instanceof \think\Collection || $menulist instanceof \think\Paginator ) && $menulist->isEmpty()))): if(is_array($menulist) || $menulist instanceof \think\Collection || $menulist instanceof \think\Paginator): if( count($menulist)==0 ) : echo "" ;else: foreach($menulist as $key=>$menu): ?>
      <dl id="menu-article">
        <dt><i class="Hui-iconfont"><?php echo $menu["permcss"]; ?></i> <?php echo $menu["permname"]; ?><i class="Hui-iconfont menu_dropdown-arrow">&#xe6d5;</i></dt>
        <dd>
          <ul>
            <?php if(is_array($menu['childs']) || $menu['childs'] instanceof \think\Collection || $menu['childs'] instanceof \think\Paginator): if( count($menu['childs'])==0 ) : echo "" ;else: foreach($menu['childs'] as $key=>$item): ?>
            <li><a data-href="<?php if($item['permurl'])echo url($item['permurl'],['tagrole'=>$item['id']]);else echo 'javascript:;' ?>" 
                   data-title="<?php echo $item['permname']; ?>" href="javascript:void(0)"><?php echo $item['permname']; ?></a></li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
          </ul>
        </dd>
      </dl>
    <?php endforeach; endif; else: echo "" ;endif; endif; ?>
	</div>
</aside>
<div class="dislpayArrow hidden-xs"><a class="pngfix" href="javascript:void(0);" onClick="displaynavbar(this)"></a></div>

<div class="dislpayArrow hidden-xs"><a class="pngfix" href="javascript:void(0);" onClick="displaynavbar(this)"></a></div>
<section class="Hui-article-box">
	<div id="Hui-tabNav" class="Hui-tabNav hidden-xs">
		<div class="Hui-tabNav-wp">
			<ul id="min_title_list" class="acrossTab cl">
				<li class="active">
					<span title="我的桌面" data-href="<?php echo url('welcome'); ?>">我的桌面</span>
					<em></em></li>
		</ul>
	</div>
		<div class="Hui-tabNav-more btn-group"><a id="js-tabNav-prev" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d4;</i></a><a id="js-tabNav-next" class="btn radius btn-default size-S" href="javascript:;"><i class="Hui-iconfont">&#xe6d7;</i></a></div>
</div>
	<div id="iframe_box" class="Hui-article">
		<div class="show_iframe">
			<div style="display:none" class="loading"></div>
			<iframe scrolling="yes" frameborder="0" src="<?php echo url('welcome'); ?>"></iframe>
	</div>
</div>
</section>

<div class="contextMenu" id="Huiadminmenu">
	<ul>
		<li id="closethis">关闭当前 </li>
		<li id="closeall">关闭全部 </li>
</ul>
</div>




<script type="text/javascript" src="__ADMIN__/lib/jquery/1.9.1/jquery.min.js"></script> 
<script type="text/javascript" src="__ADMIN__/lib/jquery-ui/1.9.1/jquery-ui.min.js"></script> 
<script type="text/javascript" src="__ADMIN__/lib/jquery.SuperSlide/2.1.1/jquery.SuperSlide.min.js"></script>
<script type="text/javascript" src="__ADMIN__/h-ui/js/H-ui.js"></script>
<script type="text/javascript" src="__ADMIN__/h-ui.admin/js/H-ui.admin.js"></script> 
<script type="text/javascript" src="__ADMIN__/lib/layer/2.4/layer.js"></script>
<script type="text/javascript">
  function clearCache(){
    $.get("<?php echo url('Websystem/clearCache'); ?>",function(){
      window.location.reload();
    })
  }
  
  function baseOpen(title,url,w,h){
    layer_show(title, url,w,h);
  }
  
</script>
