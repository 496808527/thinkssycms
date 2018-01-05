{include file='index/meta' /}

<title>错误提示</title>

</head>
<body>

{include file='index/footer' /}


    <script type="text/javascript">
      modalalertdemo();
      function modalalertdemo(){
        $.Huimodalalert('<?=strip_tags($msg)?>',2000);
          setTimeout(function(){
          var index = parent.layer.getFrameIndex(window.name);
          parent.$('.btn-refresh').click();
          parent.layer.close(index);
          {empty name="data.isopen"}
               removeIframe();
          {/empty}
            
          {empty name="url"}
               window.history.go(-1);
               {else}
               window.location.href="{$url}";
          {/empty}
        
        },1500);     
      }
    </script>
  </body>
</html>