$(function(){
	$('.bg>li').click(function () {
			var id=$(this)[0].id;
			$('.content_right>div>p').css({
				visibility: 'hidden',
				animationName: 'none'
			}).removeClass('animated')
			$('.bg>li').removeClass('p_choice');
			$('.content_right>div').hide();
			if(id=='title1'){
				$('.bg>li').eq(0).addClass('p_choice');
			    $('.content_right>div').eq(0).show();
			    $('.content_right>div:nth-child(1)>p').css({
			    	visibility: 'visible',
					animationName: 'fadeInUp'
			    }).addClass('animated');
			}
			if(id=='title2'){
				$('.bg>li').eq(1).addClass('p_choice');
			    $('.content_right>div').eq(1).show();
			    $('.content_right>div:nth-child(2)>p').css({
			    	visibility: 'visible',
					animationName: 'fadeInUp'
			    }).addClass('animated');
			}
			if(id=='title3'){
				$('.bg>li').eq(2).addClass('p_choice');
			    $('.content_right>div').eq(2).show();
			    $('.content_right>div:nth-child(3)>p').css({
			    	visibility: 'visible',
					animationName: 'fadeInUp'
			    }).addClass('animated');
			}
			if(id=='title4'){
				$('.bg>li').eq(3).addClass('p_choice');
			    $('.content_right>div').eq(3).show();
			    $('.content_right>div:nth-child(4)>p').css({
			    	visibility: 'visible',
					animationName: 'fadeInUp'
			    }).addClass('animated');
			}
		})
	$('.content_right>div:nth-child(1)>p').css({
			    	visibility: 'visible',
					animationName: 'fadeInUp'
			    }).addClass('animated');



	var divflag=true;
	var div2flag=true;
	$('#div1').click(function(){
		if(divflag){
			divflag=false;
			$('.div1').fadeIn('slow');
			$('#div1>span').html('-');
		}else{
			divflag=true;
			$('.div1').fadeOut('slow');
			$('#div1>span').html('+');
		}
	})
	$('#div2').click(function(){
		if(div2flag){
			div2flag=false;
			$('.div2').fadeIn('slow');
			$('#div2>span').html('-');
		}else{
			div2flag=true;
			$('.div2').fadeOut('slow');
			$('#div2>span').html('+');
		}
	})



	
	
})