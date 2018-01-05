<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:68:"E:\PHP\thinkssycms\public/../application/admin\view\login\index.html";i:1515136680;}*/ ?>
<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="renderer" content="webkit|ie-comp|ie-stand">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no" />
    <meta http-equiv="Cache-Control" content="no-siteapp" />
    <!--[if lt IE 9]>
    <script type="text/javascript" src="__ADMIN__/lib/html5shiv.js"></script>
    <script type="text/javascript" src="__ADMIN__/lib/respond.min.js"></script>
    <![endif]-->
    <link href="__ADMIN__/h-ui/css/H-ui.min.css" rel="stylesheet" type="text/css" />
    <link href="__ADMIN__/h-ui.admin/css/H-ui.login.css" rel="stylesheet" type="text/css" />
    <link href="__ADMIN__/h-ui.admin/css/style.css" rel="stylesheet" type="text/css" />
    <link href="__ADMIN__/lib/Hui-iconfont/1.0.8/iconfont.css" rel="stylesheet" type="text/css" />
    <!--[if IE 6]>
    <script type="text/javascript" src="__ADMIN__/lib/DD_belatedPNG_0.0.8a-min.js" ></script>
    <script>DD_belatedPNG.fix('*');</script>
    <![endif]-->
    <title><?php echo $webinfo["websitename"]; ?></title>
    <meta name="keywords" content="<?php echo $webinfo['webkeyword']; ?>">
    <meta name="description" content="<?php echo $webinfo['webdisciption']; ?>">
  </head>
  <body>
    <input type="hidden" id="TenantId" name="TenantId" value="" />
    <div class="header"><?php echo $webinfo["websitename"]; ?><?php echo $webinfo["versions"]; ?>管理后台</div>
    <div class="loginWraper">
      <div id="loginform" class="loginBox">
        <form class="form form-horizontal" action="<?php echo url('admin/login/index'); ?>" method="post"  name="myform">
          <div class="row cl">
            <label class="form-label col-xs-3"><i class="Hui-iconfont">&#xe60d;</i></label>
            <div class="formControls col-xs-8">
              <input id="username" name="username" type="text" placeholder="账户" class="input-text size-L">
            </div>
          </div>
          <div class="row cl">
            <label class="form-label col-xs-3"><i class="Hui-iconfont">&#xe60e;</i></label>
            <div class="formControls col-xs-8">
              <input id="password" name="password" type="password" placeholder="密码" class="input-text size-L">
            </div>
          </div>
    


      <div class="row cl">
        <div class="formControls col-xs-8 col-xs-offset-3">
          <input class="input-text size-L" type="text" id="code" name="code" placeholder="验证码" onblur="if (this.value == '') {
                this.value = '验证码:'
              }" onclick="if (this.value == '验证码:') {
                    this.value = '';}" value="验证码:" style="width:150px;">
          <td><img style="cursor:pointer;" onClick="verify()" class="yzm" title="点击切换" src="<?php echo url('admin/login/image'); ?>" /></td>
          <label><a href="javascript:verify()">看不清？点击更换</a></label>
        </div>
        <div class="row cl">
          <!--<div class="formControls col-xs-8 col-xs-offset-3">
            <label for="online">
              <input type="checkbox" name="online" id="online" value="">
              使我保持登录状态</label>
          </div>-->
        </div>
        <div class="row cl">
          <div class="formControls col-xs-8 col-xs-offset-3">
            <input type="hidden" name="__token__" value="<?php echo \think\Request::instance()->token(); ?>" />
            <input name="login"  type="button" onClick="checkinfo();"  class="btn btn-success radius size-L" value="&nbsp;登&nbsp;&nbsp;&nbsp;&nbsp;录&nbsp;">
            <!--<input name="" type="reset" class="btn btn-default radius size-L" value="&nbsp;取&nbsp;&nbsp;&nbsp;&nbsp;消&nbsp;">-->
          </div>
        </div>
        </form>
      </div>
    </div>
    <div class="footer">Copyright <?php echo $webinfo["footver"]; ?></div>
    <script type="text/javascript" src="__ADMIN__/lib/jquery/1.9.1/jquery.min.js"></script> 
    <script type="text/javascript" src="__ADMIN__/h-ui/js/H-ui.min.js"></script>
     <script type="text/javascript" src="__ADMIN__/lib/layer/2.4/layer.js"></script>
     
    <!--此乃百度统计代码，请自行删除-->
    <script>
                var _hmt = _hmt || [];
                (function () {
                  var hm = document.createElement("script");
                  hm.src = "//hm.baidu.com/hm.js?080836300300be57b7f34f4b3e97d911";
                  var s = document.getElementsByTagName("script")[0];
                  s.parentNode.insertBefore(hm, s);
                })();
                var _bdhmProtocol = (("https:" == document.location.protocol) ? " https://" : " http://");
                document.write(unescape("%3Cscript src='" + _bdhmProtocol + "hm.baidu.com/h.js%3F080836300300be57b7f34f4b3e97d911' type='text/javascript'%3E%3C/script%3E"));
    </script>
    <script>
      //得到文本框的值
      function checkinfo() {
        var username = $.trim($('#username').val());
        var password = $.trim($('#password').val());
        var code = $.trim($('#code').val());

        //判断文本框是否为空
        if ('' == username || '' == password) {
          layer.alert("用户名或密码不能为空");
          return false;
        }
        if ('' == code || '验证码:' == code) {
          layer.alert("请填写正确的验证码");
          return false;
        }

        checkuserinfo();

      }

      $(document).keydown(function (event) {
        if (13 == event.keyCode) {
          checkinfo();
        }
      });


      function checkuserinfo() {
        var username = $.trim($('#username').val());
        var password = $.trim($('#password').val());
        var code = $.trim($('#code').val());

        $.ajax({
          url: "<?php echo url('admin/login/checkuserinfo'); ?>", //提交的路径
          type: 'post', //提交的方式
          cache: false, //设置缓存
          data: {'username': username, 'password': password, 'code': code},
          dataType: 'json',
          success: function (ret) {
            console.log(ret.code);
            if (ret.code == 200) {
              myform.submit();

            } else if (ret.code == 100) {
              layer.alert(ret.msg);
              verify();

              return false;
            } else if (ret.code == 10000) {
              layer.alert(ret.msg);
              verify();
              return false;
            }
          }
        });

      }

      function verify() {
        $rand = Math.random();
        var src = "<?php echo url('admin/login/image'); ?>";
        src = src.replace('.html', '');
        src += "/" + $rand + ".html";
        $(".yzm").attr('src', src);
      }
    </script>
  </body>
</html>