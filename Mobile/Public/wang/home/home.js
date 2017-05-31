$(function () {

	//隐藏菜单的显示
	$('#nav').click(function(){
		$('.menu').toggle()
	})
	$('.content').click(function(){
		$('.menu').hide()
	})
})