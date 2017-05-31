$(function(){
    $('#tijiao').click(function(){
       realname = $('input[name = realname]').val();
        id_card = $('input[name = id_card]').val();
        bank_card = $('input[name = bank_card]').val();
        bank_name = $('#bind_bank').val();
        bank_address = $('input[name = bank_card1]').val();
        bank_card_name = $('input[name = bank_card_name]').val();

        if(realname == ''){
            layer.open({
                content: '真实姓名必填',
                style:'color:black'
                ,time: 2,
            });
            return false;
        }
        var reg_1 =   /^[\u4E00-\u9FA5]+$/;
        if(!reg_1.test(realname)){
            layer.open({
                content: '真实姓名必须为汉字',
                style:'color:black'
                ,time: 2,
            });
            return false;
        }
        if(!isNaN(realname)){
            layer.open({
                content: '真实姓名不能为数字',
                style:'color:black'
                ,time: 2,
            });
            return false;
        }

        if(id_card == ''){
            layer.open({
                content: '身份证号必填',
                style:'color:black'
                ,time: 2,
            });
            return false;
        }
        var reg = /^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/;
        if(!reg.test(id_card)){
            layer.open({
                content: '身份证号格式不正确',
                style:'color:black'
                ,time: 2,
            });
            return false;
        }

        if(bank_card_name == ''){
            layer.open({
                content: '开户名必须填写',
                style:'color:black'
                ,time: 2,
            });
            return false;
        }
        if(bank_card_name != realname){
            layer.open({
                content: '开户名必须和真实姓名一致',
                style:'color:black'
                ,time: 2,
            });
            return false;
        }

        var reg_1 = /^\d{10,25}$/g;

        if(!reg_1.test(bank_card)){
            layer.open({
                content: '银行卡号格式不正确',
                style:'color:black'
                ,time: 2,
            });
            return false;
        }

        if(bank_address == ''){
            layer.open({
                content: '开户支行必须填写',
                style:'color:black'
                ,time: 2,
            });
            return false;
        }

        $.ajax({
            'type':'post',
            'dataType':'json',
            'url':location.protocol+'//'+window.location.host+'/Account/checkTrue',
            'data':{'realname':realname,'id_card':id_card,'bank_card':bank_card,'bank_name':bank_name,'bank_address':bank_address,'bank_card_name':bank_card_name},
            success:function(e){
                if(e.status == 1){
                    layer.open({
                        content: '保存成功',
                        style:'color:black'
                        ,btn: ['确定'],
                        yes:function(){
                            window.location.href = location.protocol+'//'+window.location.host+'/Account/index'
                        }
                    });
                }else{
                    layer.open({
                        content: '保存失败',
                        style:'color:black'
                        ,time: 2,
                    });
                    return false;
                }
            }
        });
    });
});