$(function(){
  // $('#title_img').on('mousemove',function(){
  //   var x=getMousePosition().x;
  //   var y=getMousePosition().y;
  //   $('#title').show().css({
  //     left:x,
  //     top:y
  //   });

  // }).on('mouseleave',function(){
  //    $('#title').hide();
  // });
  // function getMousePosition(e){
  //   var a=e||window.event;
  //   var x=a.pageX||(a.clientX+(document.documentElement.scrollLeft||document.body.scrollLeft));
  //   var y=a.pageY||(a.clientY+(document.documentElement.scrollTop||document.body.scrollTop));
  //   return{
  //   'x':x,
  //   'y':y
  //   }
  // }
        var right=$('#title').innerWidth()/2;
        console.log(-right)
      $('#title').css({
        right:-right
      })

       var backflag=false;
      var frameworkflag=true;
      var analyseflag=true;
      var commentflag=true;
    $('.movie_introduce>p').click(function(){
        var id = $(this)[0].id;
        if(id=='back'){
            $('.back').fadeToggle('slow')
            if(backflag){
                $('#back>span').text('-');
                backflag=false;
            }else{
                $('#back>span').text('+');
                backflag=true;
            }
        }
        if(id=='framework'){
            $('.framework').fadeToggle('slow')
            if(frameworkflag){
                $('#framework>span').text('-');
                frameworkflag=false;
            }else{
                $('#framework>span').text('+');
                frameworkflag=true;
            }
        }
        if(id=='analyse'){
            $('.analyse').fadeToggle('slow')
            if(analyseflag){
                $('#analyse>span').text('-');
                analyseflag=false;
            }else{
                $('#analyse>span').text('+');
                analyseflag=true;
            }
        }
        if(id=='comment_film'){
            $('.comment_film').fadeToggle('slow')
            if(commentflag){
                $('#comment_film>span').text('-');
                commentflag=false;
            }else{
                $('#comment_film>span').text('+');
                commentflag=true;
            }
        }
    });

$('#anbl_argement').click(function(){
  $('.argement').fadeIn('slow');
})
$('.close_btn').click(function(){
  $('.argement').fadeOut('slow');
})

 $('#cancel').click(function(){
  $('.suport_bg').fadeOut('slow');
 })

$('.argement').click(function(){
  return false;
})

    //评论区选择类型
    $('.comment_ul>li').click(function(){
        var text=$(this).text();
        $('.comment_ul>li').removeClass('comment_choice');
        if(text=='导演'){
            $('.comment_ul>li').eq(0).addClass('comment_choice');
        }
        if(text=='演员'){
            $('.comment_ul>li').eq(1).addClass('comment_choice');
        }
        if(text=='故事'){
            $('.comment_ul>li').eq(2).addClass('comment_choice');
        }
        if(text=='后期'){
            $('.comment_ul>li').eq(3).addClass('comment_choice');
        }

    })

    //项目进度 收益预测 评论的li之间的切换

    $('.switch>li').click(function(){
        var id=$(this)[0].id;
        $('.switch>li').removeClass('choice');
        $('.comment>div').hide();
        $(this).addClass('choice');
        if(id=='schedule'){
            $('.schedule').show();
        }
        if(id=='forecast'){
            $('.forecast').show();
        }
        if(id=='commentLi'){
            $('.commentLi').show();
        }
    })

});
