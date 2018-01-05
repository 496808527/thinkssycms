
$(".checkall").click(function () {
  if ($(this).attr("checkall") == "no") {
    $(this).text("取消全选").attr("checkall", "yes");
    $(".orderlist ul li span").addClass("active");
  } else {
    $(this).text("全选").attr("checkall", "no");
    $(".orderlist ul li span").removeClass("active");
  }
});


$(".menu ul li").click(function () {
  if(typeof( $(this).attr("topage"))!=="undefined"){
     window.location.href = $(this).attr("topage");
  }
 
});

$(".personmenu ul li").click(function () {
  if($(this).attr("topage")){
    window.location.href = $(this).attr("topage");
  }
  
});

$(".header b").click(function(){
    if($(".search input").css("width")!="0px"){
       $(".search input").animate({width:"0rem"},"slow").hide("fast");
    }else{
       $(".search input").animate({width:"7.6rem"},"slow").show();
    }});

$(".header span,.headerfindpwd span").click(function () {
  $(".menu").toggle();
});

$(".header i").click(function () {
  window.history.go(-1);
})


$('.orderlist>.content').on("click", "span", function () {
        totalspan = $(".orderlist ul li span").length;

        if ($(this).hasClass("active")) {
          $(this).removeClass("active");
        } else {
          $(this).addClass("active");
        }
        checkspan = $(".orderlist ul li span.active").length;
        (totalspan == checkspan) ? $(".checkall").text("取消全选").attr("checkall", "yes") : $(".checkall").text("全选").attr("checkall", "no");
      })