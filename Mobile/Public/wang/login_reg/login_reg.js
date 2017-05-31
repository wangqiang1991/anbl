$(function(){
	
	$('#login').click(function(){

		//>> 获取用户名
		username = $('input[name = username]').val();
		password = $('input[name = password]').val();
		if(username == '' || password == ''){
			layer.open({
				content: '用户名或密码不能为空',
				style: 'color:black;'
				,btn: '我知道了'
			});
			return false;
		}
		//>> 请求后台
		$.ajax({
			'type':'post',
			'dataType':'json',
			'url':location.protocol+'//'+window.location.host+'/login/checkLogin',
			'data':{'username':username,'password':password},
			success:function(e){
				if(e.status == 1){
					//>> 登录成功,跳转
					window.location.href = location.protocol+'//'+window.location.host+'/index/index'
				}else{
					layer.open({
						content: e.msg,
						style: 'color:black;'
						,time:2
					});
				}
			}
		});

	});
	$('#reg').click(function(){
		username = $('input[name = phone]').val();
		password = $('input[name = passWord]').val();
		rePassword = $('input[name = rePassword]').val();
		captcha = $('input[name = captcha]').val();
		parent_id = $('input[name = parent_id]').val();

		if(username == ''){
			layer.open({
				content: '手机号不能为空',
				style: 'color:black;'
				,time:2
			});
			return false;
		}
		if(captcha == ''){
			layer.open({
				content: '验证码不能为空',
				style: 'color:black;'
				,time:2
			});
			return false;
		}
		if(captcha.length != 6){
			layer.open({
				content: '验证码格式不正确',
				style: 'color:black;'
				,time:2
			});
			return false;
		}
		if(password == ''){
			layer.open({
				content: '密码不能为空',
				style: 'color:black;'
				,time:2
			});
			return false;
		}
		if(password.length < 6){
			layer.open({
				content: '密码格式不正确',
				style: 'color:black;'
				,time:2
			});
			return false;
		}
		if(rePassword == ''){
			layer.open({
				content: '确认密码不能为空',
				style: 'color:black;'
				,time:2
			});
			return false;
		}
		if(rePassword.length < 6){
			layer.open({
				content: '确认密码长度至少为6位',
				style: 'color:black;'
				,time:2
			});
			return false;
		}
		if(password != rePassword){
			layer.open({
				content: '两次输入的密码不一致',
				style: 'color:black;'
				,time:2
			});
			return false;
		}

		//>> 注册
		$.ajax({
			'type':'post',
			'dataType':'json',
			'url':location.protocol+'//'+window.location.host+'/Register/register',
			'data':{'username':username,'password':password,'captcha':captcha,'parent_id':parent_id},
			success:function(e){
				if(e.status == 1){
					layer.open({
						content: '注册成功',
						style: 'color:black;'
						,btn:'确定',
						yes:function(){
							window.location.href = location.protocol+'//'+window.location.host+'/login/index';
						}
					});
				}else{
					layer.open({
						content: e.msg,
						style: 'color:black;'
						,time:2
					});
					return false;
				}
			}
		});

	})

	//切换
		$('.loginorreg>p').click(function(){
			$('.loginorreg>p').removeClass('choice')
			if($(this).text()=='登录'){
				$('.loginorreg>p').eq(0).addClass('choice');
				$('.login').show();
				$('.reg').hide();
			}
			if($(this).text()=='注册'){
				$('.loginorreg>p').eq(1).addClass('choice');
				$('.login').hide();
				$('.reg').show();
			}
		})

	// 短信验证码蒙板
	$('.song').click(function(){
		username = $('input[name = phone]').val();
		if(/^1(3|4|5|7|8)\d{9}$/.test($('.phoneinput').val())){
				 var time=60;
				 $('.mengban').show();
				 var timer = setInterval(function(){
					      time--;
					      $('.song').html(time+"s");
					      if(time===0){
					         $('.mengban').hide();
					         $('.song').html("重新获取");
					        clearInterval(timer);
					      }
					 },1000);

			//>> 获取验证码
			$.ajax({
				'type':'post',
				'dataType':'json',
				'url':location.protocol+'//'+window.location.host+'/Register/sendMessage',
				'data':{'username':username},
			});
		}else{
			layer.open({
				content: '请输入正确的手机号',
				style: 'color:black;'
				,time:2
			});
			return false;
		}
	})	


	//判断进来的是否是注册用户
	var url=window.location.href;
	console.log(url)
	var txt=url.slice(url.indexOf('/xt/')+4,url.indexOf('/xt/')+5);
	console.log(txt)
	if(txt==1){
		$('.loginorreg>p').removeClass('choice')
		$('.loginorreg>p').eq(1).addClass('choice');
		$('.login').hide();
		$('.reg').show();
	}
	
})