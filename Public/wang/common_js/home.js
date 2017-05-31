$(function(){

	for(var i=0;i<$('.complate_point').length;i++){
		var number=$('.complate_point').eq(i).text();
		var height=1-toPoint(number);
		$(".top").eq(i).animate({
			height:toPercent(height)
		},'slow');
	}

//百分数转小数
	function toPoint(percent){
		var str=percent.replace("%","");
		str= str/100;
		return str;
	}
//小数转百分数
	function toPercent(point){
		var str=Number(point*100).toFixed(1);
		str+="%";
		return str;
	}

//转换影院
	$('.move_list p').click(function(){
		var cont=$(this)[0].innerHTML;
		$('.main').hide()
		$('.move_list p').removeClass('choice');
		if(cont==='星级'){
			$('.move_list p').eq(0).addClass('choice');
			$('.movie_star').show();

		}
		if(cont==='院线'){
			$('.move_list p').eq(1).addClass('choice');
			$('.cname').show();

		}
		if(cont==='网路IP'){
			$('.move_list p').eq(2).addClass('choice');
			$('.webip').show();

		}
	});



	window.onscroll = function(){
		var t = document.documentElement.scrollTop || document.body.scrollTop;
		//console.log(t)
		if( t >= 1000 ) {
			$('.move_list').css({
				top:t-700
			});
		} else {
			$('.move_list').css({
				top:100
			});
		}

	};

	window.onresize=function(){
		screen_auto();
	}


	//不同屏幕的适配
	function screen_auto (){
		var bodyWidth=$('body').width();
		if(bodyWidth>=1200&&bodyWidth<=1400){
			$('header').height(600);
			$('#center').height(600);
			$('#slider .slide').height(600).width(860);
		}else if (bodyWidth>1400&&bodyWidth<=1600) {
			$('header').height(700);
			$('#center').height(700);
			$('#slider .slide').height(700).width(980);
		}else if (bodyWidth>1600&&bodyWidth<=1800) {
			$('header').height(800);
			$('#center').height(800);
			$('#slider .slide').height(800).width(1100);
		}else {
			$('header').height(900);
			$('#center').height(900);
			$('#slider .slide').height(900).width(1200);
		}
	}
	screen_auto();



	//新闻标题轮播
	var marginLeft=0;
	var maxLeft=0;
	for (var i = 0; i < $('.news_carousel>div>a').length; i++) {
		maxLeft=maxLeft+$('.news_carousel>div>a').eq(i).width()+30
	}
	//console.log(-maxLeft)
	setInterval(function(){
		marginLeft-=2;
		$('.news_carousel>div').css({
			marginLeft:marginLeft
		})
		if(marginLeft<=-maxLeft){
			marginLeft=10;
		}
	},60)

});
