(function(){
  //短信获取验证码
  $('#get').click(function(){
    $('#get').hide();
    $('#repeat_get').show();
    var time=60;
  var timer = setInterval(function(){
      time--;
      $('#get').hide();
      $('#repeat_get').show();
      $('#repeat_get').html(time+"秒后重新获取");

      if(time===0){
        $('#get').show();
        $('#repeat_get').hide();
        clearInterval(timer);
      }
    },1000);
  });
    $('.user>input').on('keyup',function(){
        var phone=$('.user>input').val();
        if(/^1(3|4|5|7|8)\d{9}$/.test(phone)){
            $('#menban').hide();
        }else{
            $('#menban').show();
        }
    })
})();
$(function(){
    //>> 获取验证码
    $('#get').click(function(){
        var phone  = $('input[name = phone]').val();
        if(phone == ''){
            layer.tips('请先填写手机号码!', 'input[name = phone]');
        }else{
            //>> 验证手机号
            var r = /^0?(13|14|15|17|18)[0-9]{9}$/;
            if(!r.test(phone)){
                layer.tips('手机号码格式不正确', 'input[name = phone]');
                return ;
            }
            var url = location.protocol+'//'+window.location.host+'/Home/Register/sendMessage';
            $.ajax({
                'type':'post',
                'dataType':'json',
                'url':url,
                'data':{
                    'phone':phone
                },
                success:function(result){

                }
            });
        }
    });
    function regto(){
        var password = $('input[name = password]').val();
        var repassword = $('input[name = repassword]').val();
        var captcha = $('input[name = captcha]').val();
        var phone = $('input[name = phone]').val();
        var invite_key = $('input[name = invite_key]').val();
        if(phone == ''){
            layer.tips('手机号不能为空!', 'input[name = phone]');

        }else{
            //>> 验证手机号
            var r = /^0?(13|14|15|17|18)[0-9]{9}$/;
            if(!r.test(phone)){
                layer.tips('手机号码格式不正确', 'input[name = phone]');

                return ;
            }
            //>> 验证密码
            if(password == ''){
                layer.tips('密码不能为空!', 'input[name = password]');

            }else{
                //var e = /^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/ ;

                //if(!e.test(password)){
                //    layer.tips('密码格式不正确!', 'input[name = password]');
                //
                //    return ;
                //}

                if(password.length < 6){
                    layer.tips('密码长度至少6位!', 'input[name = password]');

                    return  false;
                }
                if(repassword == ''){
                    layer.tips('确认密码不能为空!', 'input[name = repassword]');

                    return  false;
                }
                if(password != repassword){
                    layer.tips('两次输入的密码不一致!', 'input[name = repassword]');

                    return  false;
                }

                //>> 验证验证码
                if(captcha == ''){
                    layer.tips('验证码不能为空!', 'input[name = captcha]');
                }else{
                    //>> 验证邀请码
                    if(invite_key == ''){
                        layer.tips('邀请码不能为空!', 'input[name = invite_key]');
                    }else{
                        var url_ = location.protocol+'//'+window.location.host+'/Home/Register/register';
                        $.ajax({
                            'type':'post',
                            'dataType':'json',
                            'url':url_,
                            'data':{
                                'phone':phone,
                                'password':password,
                                'captcha':captcha,
                                'invite_key':invite_key
                            },
                            success:function(result){
                                if(result.status == 1){
                                   layer.msg('注册成功',{time:2000},function(){
                                       window.location.href = location.protocol+'//'+window.location.host+'/Home/Login/index'
                                   });
                                }else{
                                    layer.msg(result.msg);
                                }
                            }
                        });
                    }
                }
            }

        }
     
    }
    $('#ok').click(function(){
        regto()
    });
    $('body').on('keyup',function(e){
            if(e.keyCode==13){
                regto()
            }
            
        })

});
