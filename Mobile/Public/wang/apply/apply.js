$(function(){

    $('.apply').click(function(){

       var  type = $('input[name = type]:checked').val();

       var  number = $('input[name = number]').val();


        if(!type){
            layer.open({
                content: '请选择提现类型',
                style:'color:black'
                ,time:2
            });
            return false;
        }
        if(number == ''){
            layer.open({
                content: '请填写提现阿纳豆数目',
                style:'color:black'
                ,time:2
            });
            return false;
        }

        if(number < 0){
            layer.open({
                content: '阿纳豆数目不能为负',
                style:'color:black'
                ,time: 2
            });
            return false;

        }
        if(number % 100 != 0){
            layer.open({
                content: '阿纳豆数目必须为100的倍数',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(number < 350){
            layer.open({
                content: '阿纳豆数目必须大于350',
                style:'color:black'
                ,time: 2
            });
            return false;
        }

        $.ajax({
            'type':'post',
            'dataType':'json',
            'url':location.protocol+'//'+window.location.host+'/Account/cash',
            'data':{'money':number,'type':type},
            success:function(e){
                if(e.status == 1){
                    layer.open({
                        content: '提现申请成功,请等待审核',
                        style:'color:black'
                        ,btn: ['确定'],
                        yes:function(){
                            location.reload();
                        }
                    });
                }else{
                    layer.open({
                        content: e.msg,
                        style:'color:black'
                        ,btn: ['确定'],
                        yes:function(){
                            location.reload();
                        }
                    });
                }
            }
        });
    });
});