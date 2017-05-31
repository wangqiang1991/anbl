$(function(){
    $('#suport').click(function(){
        roleId = $('input[name = roleId]').val();
        image_url = $('input[name = image_url]').val();
        movieId = $('input[name = movieId]').val();
        realname = $('input[name = realname]').val();
        sex = $('input[name = sex]').val();
        volk = $('input[name = volk]').val();
        birthday = $('input[name = birthday]').val();
        height_ = $('input[name = height_]').val();
        phone = $('input[name = phone]').val();
        address = $('input[name = address]').val();
        email = $('input[name = email]').val();
        skill = $('textarea[name = skill]').val();
        expirence = $('textarea[name = expirence]').val();
        if(realname == ''){
            layer.open({
                content: '请输入真实姓名',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(!isNaN(realname)){
            layer.open({
                content: '姓名不能为数字',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(sex == ''){
            layer.open({
                content: '请选择性别',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(volk == ''){
            layer.open({
                content: '请输入民族',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(!isNaN(volk)){
            layer.open({
                content: '民族不能为数字',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(birthday == ''){
            layer.open({
                content: '请输入出生日期',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(birthday.substr(4,1) != '-' || birthday.substr(7,1) != '-'){
            layer.open({
                content: '日期格式不正确',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(height_ == ''){
            layer.open({
                content: '请输入身高',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(isNaN(height_)){
            layer.open({
                content: '身高必须为数字',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(height_ < 0){
            layer.open({
                content: '身高不能为负数',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(phone == ''){
            layer.open({
                content: '请输入手机号',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        reg = /^0?(13|14|15|17|18)[0-9]{9}$/;
        if(!reg.test(phone)){
            layer.open({
                content: '手机号格式不正确',
                style:'color:black'
                ,time: 2
            });
            return false;
        }

        if(address == ''){
            layer.open({
                content: '请输入地址',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(!isNaN(address)){
            layer.open({
                content: '地址不能为数字',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(email == ''){
            layer.open({
                content: '请输入邮箱地址',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        reg_1 = /\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/;
        if(!reg_1.test(email)){
            layer.open({
                content: '邮箱格式不正确',
                style:'color:black'
                ,time: 2
            });
            return false;
        }

        $.ajax({
            'type':'post',
            'dataType':'json',
            'url':location.protocol+'//'+window.location.host+'/Star/starAdd',
            'data':{
               'realname':realname,
               'sex':sex,
               'volk':volk,
               'birthday':birthday,
               'height':height_,
               'phone':phone,
               'address':address,
               'email':email,
               'skill':skill ? skill : '',
               'expirence':expirence ? expirence : '',
                'movieId':movieId,
                'roleId':roleId,
                'image_url':image_url
            },
            success:function(e){
                if(e.status == 1){
                    layer.open({
                        content: '申请成功,请等待工作人员与您联系',
                        style:'color:black'
                        ,btn: ['确定'],
                        yes:function(){
                            window.location.href = location.protocol+'//'+window.location.host+'/Index/index'
                        }
                    });
                }else{
                    layer.open({
                        content: e.msg,
                        style:'color:red'
                        ,time:2
                    });
                    return false;
                }
            }
        });
    });
});