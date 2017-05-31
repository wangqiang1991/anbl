$(function(){
  $(".burger2").click(function(){
    $(".burger2").toggleClass("open");
    $('.menu_content').fadeToggle('slow');
  });

  $('#search_cont').on('mouseenter',function(){
    $('#search_cont').animate({
      width:'120px'
    },'slow');
  })
  $('.search_li').click(function(){
    return false;
  })
  $('#search_btn').click(function(){
    serch();
  });

  //这下里写搜索的函数
  function serch(){
    var text=$('#search_cont').val();
    if(text&&!(/\s/.test(text))){
      var url = location.protocol+'//'+window.location.host+'/home/index/search.html?text='+text;
      window.location=encodeURI(url) ;
    }

  }


  $('body').click(function(){
    $('#search_cont').animate({
      width:'40px'
    },'slow');
    $('#search_cont').val('');
  }).on('keyup',function(e){
    if(e.keyCode==13){
      serch();
    }
  });
  if($('body').height()>700){
        $('.menu_content').css({
          height:$('body').height()
        })
     }else{
        $('.menu_content').css({
          height:700
        })
     }
 

  function navMarginauto(){
    $('.nav>div').css({
      width:$('.logo').width()+$('.top_nav').width()+$('.login_reg').width()+240
    })
  }
  //navMarginauto();


});

