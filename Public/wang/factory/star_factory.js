$(function(){
	var div=$('.star_content>div').eq(0);
   
	$('.sb-container>div').click(function(){
		var id=$(this)[0].id;
      $('.warn').hide();
        if(id!==div.attr('class')&&$(this).attr('class')!='last_title'&&$(this).attr('class')!='last_title ff-active'){
      var newdiv;
      for(var i=0;i<=$('.star_content>div').length;i++){
        if(id==$('.star_content>div').eq(i).attr('class')){
          newdiv=$('.star_content>div').eq(i);
          break;
        }
      }
			newdiv.show();
			div.hide();
			div=newdiv;
		}	
	});


	$('.classify p').click(function(){
    var txt=$(this)[0].innerHTML;
     $('.warn').hide();
        $('.sb-container>div').removeClass("ff-active");
        $('.star_content>div').hide();
    $('.classify p').removeClass('choice');
    if(txt==='优秀演员'){
      $('.classify p').eq(1).addClass('choice');
      $('.nice_actor').show();
      $('.nice_director').hide();
      $('.nice_work').hide();
      $('.choice_role').hide();
        $('#sb-container>div').eq(0).addClass('ff-active')
        $('#star_content>div').eq(0).show();
        div=$('#star_content>div').eq(0);
        $( '#sb-container' ).swatchbook( {
            openAt : 0
        } );
    }
    if(txt==='优秀导演'){
      $('.classify p').eq(2).addClass('choice');
      $('.nice_actor').hide();
      $('.nice_director').show();
      $('.nice_work').hide();
      $('.choice_role').hide();
        $('#sb-container1>div').eq(0).addClass('ff-active')
        $('#star_content1>div').eq(0).show();
        div=$('#star_content1>div').eq(0);
      $( '#sb-container1' ).swatchbook( {
          openAt : 0
        } );
    }
    if(txt==='优秀作品'){
      $('.classify p').eq(3).addClass('choice');
      $('.nice_actor').hide();
      $('.nice_director').hide();
      $('.nice_work').show();
      $('.choice_role').hide();
        $('#sb-container2>div').eq(0).addClass('ff-active')
        $('#star_content2>div').eq(0).show();
        div=$('#star_content2>div').eq(0);
      $( '#sb-container2' ).swatchbook( {
          openAt : 0
        } );
    }
    if(txt==='评选角色'){
      $('.classify p').eq(0).addClass('choice');
      $('.nice_actor').hide();
      $('.nice_director').hide();
      $('.nice_work').hide();
      $('.choice_role').show();
        $('#sb-container3>div').eq(0).addClass('ff-active')
        $('#star_content3>div').eq(0).show();
        div=$('#star_content3>div').eq(0);
      $( '#sb-container3' ).swatchbook( {
          openAt : 0
        } );
    }
  });

  $('.vote_btn').click(function(){
   $('.warn').show();
  })
$('.warn>input[name=cancel]').click(function(){
  $('.warn').hide();
})
   window.onresize=function(){
  screen_auto();
 }


 //不同屏幕的适配
 function screen_auto (){
  var bodyWidth=$('.body').width();
  if(bodyWidth>=1200&&bodyWidth<=1400){
    $('.star_content').css({
      marginLeft:0
    })
  }else if (bodyWidth>1400&&bodyWidth<=1600) {
    $('.star_content').css({
      marginLeft:50
    })
  }else if (bodyWidth>1600&&bodyWidth<=1800) {
    $('.star_content').css({
      marginLeft:250
    })
  }else {
    $('.star_content').css({
      marginLeft:350
    })
  }
 }
  screen_auto();  

  if($('html').height()>868){
      $('.menu_content').css({
      height:$('html').height()
    })
  }else{
      $('.menu_content').css({
      height:868
    })
  }


});