$(function(){
  $('.classify p').click(function(){
    var txt=$(this)[0].innerHTML;
    $('.classify p').removeClass('choice');
    $('.mk-carousel').hide();
    if(txt==='星级排序'){
      $('.classify p').eq(0).addClass('choice');
      $('#mkCarousel').show();
    }
    if(txt==='院线排序'){
      $('.classify p').eq(1).addClass('choice');
      $('#mkCarousel1').show();
      $('#mkCarousel1>div').eq(0).addClass('active')
    }
    if(txt==='网路IP'){
      $('.classify p').eq(2).addClass('choice');
      $('#mkCarousel2').show();
      
      $('#mkCarousel2>div').eq(0).addClass('active')
    }
    if(txt==='下载'){
      $('.classify p').eq(3).addClass('choice');
      $('#mkCarousel3').show();
    }
  });
$('#sure_download').click(function(){
  $('.down_bg').hide();
})
$('#cancel_download').click(function(){
  $('.down_bg').hide();
})

});
