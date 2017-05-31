$(function(){
	$('.way p').click(function(){
		$('.way p').removeClass('choice');
		$(this).addClass('choice');
		var text=$(this).text();
		if (text=='支付宝') {
			$('.zhifubao').show();
			$('.weixing').hide();
		}if(text=='微信'){
			$('.zhifubao').hide();
			$('.weixing').show();
		}
	})
})