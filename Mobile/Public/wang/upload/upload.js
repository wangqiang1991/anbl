
$(function(){
    $('#ok').click(function(){
        way = $('input[name = way]:checked').attr('data-id');
        money = $('input[name = money]').val();
        image = $('input[name = image_url]').val();
        if(isNaN(way)){
            layer.open({
                content: '请选择支付方式',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(money == ''){
            layer.open({
                content: '充值数目不能为空',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(isNaN(money)){
            layer.open({
                content: '充值数目必须为数字',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(money < 0){
            layer.open({
                content: '充值数目不能为负数',
                style:'color:black'
                ,time: 2
            });
            return false;
        }
        if(image.length == 0){
            layer.open({
                content: '请上传凭证',
                style:'color:black'
                ,time: 2
            });
            return false;
        }

        $.ajax({
            'type':'post',
            'dataType':'json',
            'url':location.protocol+'//'+window.location.host+'/Account/recharge',
            'data':{
                'money':money,
                'type':way,
                'image_url':image
            },
            success:function(e){

                if(e.status == 1){
                    layer.open({
                        content: '充值成功，请等待后台审核',
                        style:'color:black'
                        ,btn:['确定'],
                        yes:function(){
                            location.reload();
                        }
                    });

                }else{
                    layer.open({
                        content: e.msg,
                        style:'color:black'
                        ,time:2
                    });
                }
            }
        });
    });
});