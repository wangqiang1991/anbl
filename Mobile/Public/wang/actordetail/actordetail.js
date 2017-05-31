$(function(){
    $('select[name = role]').change(function(){
        //>> 获取当前所选角色的id
        roleId = $(this).val();
        //>> 获取当前电影的id
        movieId = $('input[name = movieId]').val();

        $('input[name = roleId]').val(roleId);
        //>> 请求角色详情
        $.ajax({
            'type':'post',
            'dataType':'json',
            'url':location.protocol+'//'+window.location.host+'/Star/roleChange',
            'data':{
                'roleId':roleId,
                'movieId':movieId
            },
            success:function(e){
                $('.intro').text(e.data.intro);
                $('.feature').text(e.data.feature);
                $('.figure').text(e.data.figure);
                $('.needMoney').text(e.data.money);
            }
        });
    });

    $('#ok').click(function(){
        //>> 获取当前的余额
        crrMoney = parseInt($('input[name = crrMoney]').val());
        //>>  获取申请需要的阿纳豆
        needMoney = parseInt($('.ned').text());

        if(crrMoney < needMoney){
            layer.open({
                content: '阿纳豆不足'+needMoney+'不能申请该角色',
                style:'color:black'
                ,btn: '确定'
            });
            return false;
        }

    });
});