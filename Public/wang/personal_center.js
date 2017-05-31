$(function(){
	//关闭回到主界面
	$('.close_identity').click(function(){
		$('.identity').hide();
		$('.body_right>ul').show();
	})
	$('.close_attend').click(function(){
		$('.attend').hide();
		$('.body_right>ul').show();
	})
	$('.close_money').click(function(){
		$('.money_operat').hide();
		$('.body_right>ul').show();
	})
	$('.close_password').click(function(){
		$('.password').hide();
		$('.body_right>ul').show();
	})
	//进入某一个界面
	$('#identity').click(function(){
		$('.body_right>ul').hide();
		$('.identity').show();
	})
	$('#attend').click(function(){
		$('.body_right>ul').hide();
		$('.attend').show();
		$('#cancel_div').hide();
	})
	$('#password').click(function(){
		$('.body_right>ul').hide();
		$('.password').show();
	})
	$('#recharge').click(function(){
		$('.money_content>ul').eq(0).show();
		$('.money_content>ul').eq(1).hide();
		$('.body_right>ul').hide();
		$('.body_right>div').hide();
		$('.money_operat').show();
		$('.money_content>ul>li').removeClass('choice');
		$('.money_content>ul>li').eq(0).addClass('choice');
		$('.put_money').show();
		$('.get_money').hide();
		$('.money_record').hide();
		$('.money_deal').hide();
		$('.zhuanzhang').hide();
	})
	$('#post_cash').click(function(){
		$('.money_content>ul').eq(0).show();
		$('.money_content>ul').eq(1).hide();
		$('.body_right>ul').hide();
		$('.body_right>div').hide();
		$('.money_operat').show();
		$('.money_content>ul>li').removeClass('choice');
		$('.money_content>ul>li').eq(1).addClass('choice');
		$('.put_money').hide();
		$('.get_money').show();
		$('.money_record').hide();
		$('.money_deal').hide();
		$('.zhuanzhang').hide();
	})
	$('#zhuanzhang').click(function(){
		$('.money_content>ul').eq(0).show();
		$('.money_content>ul').eq(1).hide();
		$('.body_right>ul').hide();
		$('.body_right>div').hide();
		$('.money_operat').show();
		$('.money_content>ul>li').removeClass('choice');
		$('.money_content>ul>li').eq(2).addClass('choice');
		$('.put_money').hide();
		$('.get_money').hide();
		$('.money_record').hide();
		$('.zhuanzhang').show();
	})
	// 不同头部的li之间的div切换
	$('.identity_content>ul>li').click(function(){
		var txt=$(this)[0].innerHTML;
		$('.identity_content>ul>li').removeClass('choice');
		if(txt=='个人资料'){
			$('.identity_content>ul>li').eq(0).addClass('choice');
			$('.people_data').show();
			$('.safe_center').hide();
			$('.update_password').hide()
		}
		if(txt=='安全中心'){
			$('.identity_content>ul>li').eq(1).addClass('choice');
			$('.people_data').hide();
			$('.safe_center').show();
			$('.update_password').hide()
		}
		if(txt=='密码管理'){
			$('.identity_content>ul>li').eq(2).addClass('choice');
			$('.people_data').hide();
			$('.safe_center').hide();
			$('.update_password').show()
		}
	})
	$('.attend_content>ul>li').click(function(){
		var txt=$(this)[0].innerHTML;
		$('.attend_content>ul>li').removeClass('choice');
		if(txt=='我的支持'){
			$('.attend_content>ul>li').eq(0).addClass('choice');
			$('.my_suport').show();
			$('.my_collect').hide();
		}
		if(txt=='我的收藏'){
			$('.attend_content>ul>li').eq(1).addClass('choice');
			$('.my_suport').hide();
			$('.my_collect').show();
		}
	})
	$('.money_content>ul>li').click(function(){
		var txt=$(this)[0].innerHTML;
		//console.log(txt)
		if(txt=='充值'){
			$('.money_content>ul>li').removeClass('choice');
			$('.money_content>ul>li').eq(0).addClass('choice');
			$('.put_money').show();
			$('.get_money').hide();
			$('.money_record').hide();
			$('.zhuanzhang').hide();
		}
		if(txt=='提现'){
			$('.money_content>ul>li').removeClass('choice');
			$('.money_content>ul>li').eq(1).addClass('choice');
			$('.put_money').hide();
			$('.get_money').show();
			$('.money_record').hide();
			$('.zhuanzhang').hide();
			$('.get_money>input').removeClass('apply_choice')
			$('.get_money>input:first-child').addClass('apply_choice')
			$('.get_money>div').hide();
			$('.get_money .apply_yuer').show();
		}

		if(txt=='转账'){
			$('.money_content>ul>li').removeClass('choice');
			$('.money_content>ul>li').eq(2).addClass('choice');
			$('.put_money').hide();
			$('.get_money').hide();
			$('.money_record').hide();
			$('.zhuanzhang').show();
		}

		if(txt=='充值记录'){
			$('.money_content>ul:nth-child(2)>li').removeClass('choice');
			$('.money_content>ul:nth-child(2)>li').eq(0).addClass('choice');
			$('.put_money').hide();
			$('.get_money').hide();
			$('.money_record').show();
			$('.zhuanzhang').hide();

		}
		


	})
	$('.put_record').click(function(){
		$('.money_content>ul:nth-child(2)>li').eq(0).addClass('choice');      //x新增
		$('.money_content>ul').eq(0).hide();
		$('.money_content>ul').eq(1).show();
		$('.put_money').hide();
		$('.get_money').hide();
		$('.money_record').show();
		$('.zhuanzhang').hide();
	});


	$('.now_put').click(function(){
		$('.money_content>ul').eq(1).hide();
		$('.money_content>ul').eq(0).show();
		$('.put_money').show();
		$('.get_money').hide();
		$('.money_record').hide();
		$('.money_content>ul>li').removeClass('choice');
		$('.money_content>ul>li').eq(0).addClass('choice');
		$('.zhuanzhang').hide();
	});
	//性别选择
	$('.sex>span>p').click(function(){
		var id=$(this)[0].id;
		$('.sex>span').removeClass('sex_choice');
		if(id=='man'){
			$('.sex>span').eq(1).addClass('sex_choice');
			$('#man>i').show();
			$('#woman>i').hide();
		}
		if(id=='woman'){
			$('.sex>span').eq(2).addClass('sex_choice');
			$('#man>i').hide();
			$('#woman>i').show();
		}
	});
	//填写绑定手机 邮箱
	$('#bind_phone').click(function(){
		$('.bind_phone>span').eq(1).hide();
		$('.bind_phone>span').eq(2).show();
	})
	$('#bind_email').click(function(){
		$('.bind_email>span').eq(1).hide();
		$('.bind_email>span').eq(2).show();
	})
	//点击提升出现的界面
	$('.up_btn').click(function(){
		$('.body_right>ul').hide();
		$('.body_right>div').hide();
		$('.identity').show();
		$('.safe_center').show();
		$('.people_data').hide();
		$('.identity_content>ul>li').removeClass('choice');
		$('.identity_content>ul>li').eq(1).addClass('choice');
	});

//>> 验证表单
	$('#save').click(function(){

		var bank = $('#bind_bank').val();
		var realname = $('input[name = realname]').val();
		var id_card = $('input[name = id_card]').val();
		var bank_card_name = $('input[name = bank_card_name]').val();
		var bank_card = $('input[name = bank_card]').val();
		var obtain = $('#county ').find("option:selected").attr('data-area');
		if(obtain == -1){
			obtain = '';
		}
		var city = $('#province ').find("option:selected").attr('data-area')+$('#citys ').find("option:selected").attr('data-area')+obtain;
		var address = $('input[name = address]').val();

		var ifCity = $('#ifCity').val();

		if(ifCity != 'NaN' && ifCity != '' && ifCity != 'undifined'){

            city = ifCity
		}

		if(realname == ''){
			layer.tips('请输入真实姓名','input[name = realname]',{
				tips:4
			});
		}else{
			if(id_card == ''){
				layer.tips('请输入身份证','input[name = id_card]',{
					tips:4
				});
			}else{
				//>> 验证身份证格式
				var reg = /^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$/;
				if(!reg.test(id_card)){
					layer.tips('身份证格式不正确','input[name = id_card]',{
						tips:4
					});
					return ;
				}

				//>> 截取生日
				tyear = parseInt(id_card.substr(6,4));
				tmonth = parseInt(id_card.substr(10,2));
				tday = parseInt(id_card.substr(12,2));

				//>> 获取当前的年
				crrDate = new Date();
				crrYear = crrDate.getFullYear();

				if(crrYear - tyear < 18){

					layer.tips('持卡人必须年满18周岁','input[name = id_card]',{
						tips:4
					});
					return false;
				}
				if(tmonth < 0 || tmonth > 12){
					layer.tips('身份证出生日期不正确','input[name = bank_card_name]',{
						tips:4
					});
					return false;
				}

				crrAllDate = new Date(tyear,tmonth,0);
				crrAllDate = crrAllDate.getDate();


				if(tday < 0 || tmonth > crrAllDate){
					layer.tips('身份证出生日期不正确','input[name = bank_card_name]',{
						tips:4
					});
					return false;
				}


				if(bank_card_name == ''){
					layer.tips('请输入开户名','input[name = bank_card_name]',{
						tips:4
					});
				}else{

					if(bank_card_name != realname){
						layer.tips('开户名必须和真实姓名一致','input[name = bank_card_name]',{
							tips:4
						});
						return false;
					}
					if(bank_card == ''){
						layer.tips('请输入银行卡号','input[name = bank_card]',{
							tips:4
						});
					}else{
						//>> 验证银行卡
						var reg_ = /^\d{10,25}$/g;
						if(!reg_.test(bank_card)){
							layer.tips('银行卡格式不正确','input[name = bank_card]',{
								tips:4
							});
							return ;
						}
						if(city == ''){
							layer.tips('请选择开户地址','input[name = city]',{
								tips:4
							});

						}else{

                            if(($('#province ').find("option:selected").attr('data-area') == -1) || ($('#citys ').find("option:selected").attr('data-area') == -1)){

                                layer.tips('请选择开户地址','#province',{
                                    tips:4
                                });
                                return false;
                            }

							if(address == ''){
								layer.tips('请输入支行地址','input[name = address]',{
									tips:4
								});
								return false;
							}else{
								var url_ = location.protocol+'//'+window.location.host+'/Home/Personal/safeInfo';
								$.ajax({
									'type':'post',
									'dataType':'json',
									'url':url_,
									'data':{
										'realname':realname,
										'id_card':id_card,
										'bank_card_name':bank_card_name,
										'bank_card':bank_card,
										'city':city,
										'address':address,
										'bank_name':bank
									},
									success:function(result){
										if(result.status == 1){
											layer.msg('保存成功!',function(){
												location.reload();
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
		}
	});
	var safeLevel = $('input[name = safeLevel]').val();
	$('#safe').css('width',safeLevel);

	//>> 获取性别
	$('#sex').click(function(){
		var url = location.protocol+'//'+window.location.host+'/Home/Personal/save';

		var sex = $('input[type = radio]:checked').attr('data-sex');

		$.ajax({
			'type':'post',
			'dataType':'json',
			'url':url,
			'data':{
				'sex':sex
			},
			success:function(result){
				if(result.status == 1){
					layer.msg('保存成功！',{
						time:3,
					},function(){
						location.reload();
					});

				}else{
					layer.msg('保存失败!');
				}
			}
		});
	});


	$('.rechargeOk').click(function(){
		var money = $('input[name = rechargeMoney]').val();
		var path = $('#images').val();
		var type = $('.apply_style_choice').attr('data-id');
		if(path.length == 0){
			layer.tips('请上传凭证','#file_upload');
			return false;
		}
		if(money == ''){

			layer.tips('充值阿纳豆不能为空','input[name = rechargeMoney]');
		}else{
			//>> 判断是否是数字
			var isNum = /^[0-9]*$/;
			if(!isNum.test(money)){

				layer.tips('请输入正确的数字','input[name = rechargeMoney]');
			}else{
				var chargeUrl = location.protocol+'//'+window.location.host+'/Home/Recharge/recharge';
				$.ajax({
					'type':'post',
					'dataType':'json',
					'url':chargeUrl,
					'data':{
						'money':money,
						'image_url':path,
						'type':type
					},
					success:function(result){
						if(result.status == 1){
							$('#images').val('');
							layer.msg('提交成功!,请等待工作人员与您联系',function(){
								$('input[name = rechargeMoney]').val('');
							});
							return false;
						}
						if(result.status == 3){
                            $('#images').val('');
                            layer.msg(result.msg,function(){
                                $('input[name = rechargeMoney]').val('');
                            });
						}else{
                            layer.msg(result.msg,function(){
                                $('input[name = rechargeMoney]').val('');
                            });
						}
					}
				});

			}
		}
	});

$('.rechargeOk1').click(function(){
		var money = $('input[name = rechargeMoney1]').val();
		var path = $('#images').val();
		var type = $('.apply_style_choice').attr('data-id');
		if(path.length == 0){
			layer.tips('请上传凭证','#file_upload2');
			return false;
		}
		if(money == ''){


			layer.tips('充值阿纳豆不能为空','input[name = rechargeMoney1]');
		}else{
			//>> 判断是否是数字
			var isNum = /^[0-9]*$/;
			if(!isNum.test(money)){

				layer.tips('请输入正确的数字','input[name = rechargeMoney1]');
			}else{
				var chargeUrl = location.protocol+'//'+window.location.host+'/Home/Recharge/recharge';
				$.ajax({
					'type':'post',
					'dataType':'json',
					'url':chargeUrl,
					'data':{
						'money':money,
						'image_url':path,
						'type':type
					},
					success:function(result){
						if(result.status == 1){
							$('#images').val('');
							layer.msg('提交成功!,请等待工作人员与您联系',function(){
								$('input[name = rechargeMoney1]').val('');
							});
						}else{
							layer.msg(result.msg,function(){
								$('input[name = rechargeMoney1]').val('');
							});
						}
					}
				});

			}
		}
	});

	var data_id = 1;
	$('.tiqu').click(function(){
		data_id = $(this).attr('data-id');
	});

    /**
	 * 点击"充值",判断是否有被拒绝的订单
     */
    $('#recharge').click(function () {
    	username = $('input[name = myTelephone]').val();
		$.ajax({
			'type':'post',
			'dataType':'json',
			'url':location.protocol+'//'+window.location.host+'/Home/Personal/checkHasRefuse',
			'data':{'username':username},
			success:function (e) {
				if(e.status==1){
                    layer.open({
						btn:['确定','取消'],
                        type: 1,
						title:'阿纳巴里提示',
                        skin: 'layui-layer-rim', //加上边框
                        area: ['420px', '180px'], //宽高
                        content: '<p>亲爱的用户,系统检测到您于'+e.create_time+'有被拒绝的订单,需要重新上传凭证吗?</p>',
						btn1:function (k) {
                            $.ajax({
                                'type':'post',
                                'dataType':'json',
                                'url':location.protocol+'//'+window.location.host+'/Home/Personal/ifHasRefuse',
                                'data':{'username':username,'order':e.order},
                                complete:function () {
                                    layer.closeAll();
                                }
                            });
                        },
                        btn2:function () {
							location.reload();
                        }
                    });
				}
            }
		});
    });



	$('.ex').click(function(){
		var money = $('input[name = exMoney'+data_id+']').val();

		if(money == ''){
			 layer.tips('请输入提现阿纳豆','input[name = exMoney'+data_id+']');
		}else{

			//>> 检测是否为0
			if(money <= 0){
				layer.tips('提现阿纳豆不能为0或负数','input[name = exMoney'+data_id+']');
				return;
			}
			//>> 检测金额是否是数字
			var reg = /^[0-9]+.?[0-9]*$/;
			if(!reg.test(money)){

				layer.tips('请输入正确的阿纳豆数额','input[name = exMoney'+data_id+']');
			}else{

				var url =  location.protocol +'//'+ window.location.host+'/Home/Personal/cash';
				$.ajax({
					'type':'post',
					'dataType':'json',
					'url':url,
					'data':{
						'money':money,
						'id':data_id
					},
					success:function(result){
						if(result.status == 1){
							$('input[name = exMoney]').val('0');
							layer.msg('提现申请成功,请等待审核',{time:1000},function(){
								$('input[name = exMoney]').val('');
								location.reload();
							});
						}else{
							layer.msg(result.msg,function(){
								$('input[name = exMoney]').val('');
							});
						}
					}
				});
			}
		}
	});
	//屏幕宽度高度调节
	if($(window).height()>=768){
		$('.body').css({
			height:$(window).height()-120
		})
	}

	//平台质询的相关操作
	$('.close_ask').click(function(){
		$('.ask').hide();
		$('.body_right>ul').show();
	})
	$('#ask').click(function(){
		$('.body_right>ul').hide();
		$('.body_right>div').hide();
		$('.ask').show();
		$('.ask_content').show();
		$('.ask_answer').hide();
	})
	$('.ask_content>p').click(function(){
		$('.ask_content').hide();
		$('.ask_answer').show();
		$('.answer_div').show();
		$('.answer_form').hide();
	})
	$('.ask_answer>p>span').click(function(){
		$('.ask_content').show();
		$('.ask_answer').hide();
	})
	$('.answer_div>p').click(function(){
		$('.answer_div').hide();
		$('.answer_form').show();
	})
	$('.back_title').click(function(){
		$('.answer_div').show();
		$('.answer_form').hide();
	})

	//我的团队相关操作

	$('.close_team').click(function(){
		$('.team').hide();
		$('.body_right>ul').show();
	})
	$('#team').click(function(){
		$('.body_right>ul').hide();
		$('.body_right>div').hide();
		$('.close_modal').hide();
		$('.team>.bg').show();
		$('.team').show();
	})

	//点击替换背景
	var bgArry=['/Public/wang/img/bg.jpg','/Public/wang/img/bg1.jpg','/Public/wang/img/bg2.jpg','/Public/wang/img/bg3.jpg'];
	var imgNumber=1;
	$('#repeat_bg').click(function(){
		$('.body_left').css({
			'backgroundImage':'url('+bgArry[imgNumber]+')'
		})
		imgNumber++;
		if(imgNumber==bgArry.length){
			imgNumber=0;
		}
	})

	//我要当演员相关操作
	$('.close_actor').click(function(){
		$('.actor').hide();
		$('.body_right>ul').show();
	})
	$('#actor').click(function(){

		$('.body_right>ul').hide();
		$('.body_right>div').hide();
		$('.actor').show();
		$('.movie_list').show();
		$('.movie_intr').hide();
		$('.apply_role').hide();
	})
	//我要当演员 面包屑操作
	$('.backTo2').click(function(){
		$('.movie_list').hide();
		$('.movie_intr').show();
		$('.apply_role').hide();
	})
	$('.backTo1').click(function(){
		$('.movie_list').show();
		$('.movie_intr').hide();
		$('.apply_role').hide();
	})
	// 申请流程顺序点击
	$('.goTodetail').click(function(){
		$('.movie_list').hide();
		$('.movie_intr').show();
		$('.apply_role').hide();
	})
	$('.goToform').click(function(){
		//>> 获取当前余额
		integral = parseInt($('input[name = jifen]').val());
		//>> 获取所需余额
		needintegral = $('.needand').text();

		if(integral < needintegral){
			layer.tips('需要'+needintegral+'阿纳豆才能申请该角色', '.goToform', {
				tips: [3, 'black'],
				time: 4000
			});
			return false;
		}
		$('.movie_list').hide();
		$('.movie_intr').hide();
		$('.apply_role').show();
	})

	//消费明细模态框操作

	$('.join').click(function(){
		
		$('.close_modal').show();
		$('.money_modal').show();
		$('.team>.bg').hide()
	})
	$('.close_modal>input').click(function(){
		$('.close_modal').hide();
		$('.money_modal').hide();
		$('.team>.bg').show();
	})

	//取消支持 

	$('body').on('click','.cancel_suport',function(){
		$('#cancel_div').fadeIn('slow')
	});
	$('body').on('click','.cancek_input',function(){
		$('#cancel_div').fadeOut('slow')
	})

	//选择支付方式
	$('.apply_style').click(function(){
		$('.apply_style').removeClass('apply_style_choice');
		$('.put_money>div').hide();
		if($(this).html()=='微信'){
			$(this).addClass('apply_style_choice');
			$('#weixing').show();
			$('#zhifubao').hide();
			$('#feiyinglian').show();
			$('.yinglian').hide();
		}if($(this).html()=='支付宝'){
			$(this).addClass('apply_style_choice');
			$('#zhifubaoDiv').show();
		}if($(this).html()=='公司银联'){		
			$(this).addClass('apply_style_choice');
			$('#yinlianDiv').show();
		}
	})

	//支付方式说明
	$('.get_money p:nth-child(2)>img').on('mouseenter',function(){
		$('.get_money p:nth-child(2)>span').show();
	}).on('mouseleave',function(){
		$('.get_money p:nth-child(2)>span').css({display:'none'})
	})

	$(".yanzhen").click(function(){
		$(".yanzhen").attr("disabled", true);
		var time=60;
		var timer=setInterval(function(){
				time--;
			$(".yanzhen").val(time+'S');
			$(".yanzhen").attr("disabled", true);	
			if(time==0){
				clearInterval(timer);
				$(".yanzhen").val('重新发送');	
				$(".yanzhen").attr("disabled", false);
			}
		},1000)
	})


	$("#team_nav>li").click(function(){
        var value = $(this).text();
        $("#team_nav>li").removeClass('teamChoice');
        $('.team_content').hide();
        if(value=='收益'){
        	$("#team_nav>li").eq(0).addClass('teamChoice')
        	$('.apply_1').show();
        }
         if(value=='投票'){
         	$("#team_nav>li").eq(1).addClass('teamChoice')
        	$('.apply_2').show();
        }

         if(value=='转账'){
         	$("#team_nav>li").eq(2).addClass('teamChoice')
        	$('.apply_3').show();
        }
         if(value=='支持'){
         	$("#team_nav>li").eq(3).addClass('teamChoice')
        	$('.apply_4').show();
        }
         if(value=='演员申请'){
         	$("#team_nav>li").eq(4).addClass('teamChoice')
        	$('.apply_5').show();
        }
        if(value=='充值'){
         	$("#team_nav>li").eq(5).addClass('teamChoice')
        	$('.apply_6').show();
        }
        if(value=='提现'){
         	$("#team_nav>li").eq(6).addClass('teamChoice')
        	$('.apply_7').show();
        }
    });

    $('#close_search').click(function(){
        //console.log(13)
        $('.search_div').hide();
    });
    $('#search_xiaji').click(function(){

    	//>> 获取输入框内容
		 memberInfo = $('.search_xiaji').val();

		 if(memberInfo == ''){
             $('.search_xiaji').focus();
             return false;
		 }
		 $.ajax({
			 'type':'post',
			 'dataType':'json',
			 'url':location.protocol+'//'+window.location.host+'/home/Personal/findMemberByTel',
			 'data':{'username':memberInfo},
			 success:function (e) {

                 $('.search_div').show();
				 if(e.status == 1){
                     $('.realname').text(e.memberInfo.realname);
                     $('.all_support_money').text(e.memberInfo.all_support_money);
                     $('.role').text(e.memberInfo.role);
                     $('.group').text('000');
					 $('.sh').show();
					 $('#notFound').hide();
				 }else {
                     $('.sh').hide();
                     $('#notFound').show();
				 }

             }

		 });


    });


    //我的团队
    	var crrId = $('.people_top').attr('data-id');
        var parent = $('.people_top').parent().parent();
        for(var i=parent.children().length;i>0;i--){
            parent.children().eq(i).remove()
        }
		$.ajax({
			'type':'post',
			'dataType':'json',
			'url':location.protocol+'//'+window.location.host+'/home/Personal/groupInfo',
			'data':{'id':crrId},
			success:function (e) {
                //ajax sunccess函数
                var width=e.res.length*25+115;
                if(width>700){

                    var html='<div style="width:'+width+'px">';
                }
                else{
                    var html='<div>';
                }

                $.each(e.res,function(v,k){
                	if(k.is_true == 1){
                        html+="<div><p>账户:<span>"+k.username+"</span></p><p>支持:<span>"+k.all_support_money+"</span></p><p>角色:<span>"+k.role+"</span></p><p>团队: <span>"+k.children+"人</span></p><h3 people_id="+k.id+">"+k.realname+"</h3></div>";
					}else {
                        html+="<div><p>账户:<span>"+k.username+"</span></p><p>支持:<span>"+k.all_support_money+"</span></p><p>角色:<span>"+k.role+"</span></p><p>团队: <span>"+k.children+"人</span></p><h3 people_id="+k.id+" style='color:#ff8b0f'>"+k.realname+"</h3></div>";
					}

                })
                html+'</div>';
                $('.team_total').append(html)
            }
		});


    //下级会员点击的时候
    $('.team_total').on('click','h3',function(){
        //获取 id
        var people_id = $(this).attr('people_id');

        //点击这H3 变色以及div变长
        if($(this).css("background-color")=='rgb(0, 0, 0)'){
            var father=$(this).parent().parent();
            for(var i=0;i<father.children().length;i++){
                father.children().eq(i).animate({
                    width:25
                },'slow');
                father.children().eq(i).children().eq(4).css({
                    backgroundColor:'black'
                })
            }
            $(this).parent().animate({
                width:140
            },'slow');
            $(this).css({
                backgroundColor:'#e50909'
            })
            last_id=people_id;

            //移除所有后一级
            var father = $(this).parent().parent();
            if(father.next().length!==0){
                for(var i=father.parent().children().length;i>father.index();i--){
                    father.parent().children().eq(i).remove()
                }
            }

            if(father.index()<3){
                $.ajax({
                    'type':'post',
                    'dataType':'json',
                    'url':location.protocol+'//'+window.location.host+'/home/Personal/groupInfo',
                    'data':{'id':people_id},
                    success:function (e) {
                        //ajax sunccess函数
                        var width=e.res.length*25+115;
                        if(width>700){

                            var html='<div style="width:'+width+'px">';
                        }
                        else{
                            var html='<div>';
                        }
                        $.each(e.res,function(v,k){
                            if(k.is_true == 1){

                                html+="<div><p>账户:<span>"+k.username+"</span></p><p>支持:<span>"+k.all_support_money+"</span></p><p>角色:<span>"+k.role+"</span></p><p>团队: <span>"+k.children+"人</span></p><h3 people_id="+k.id+">"+k.realname+"</h3></div>";
							}else {
                                html+="<div><p>账户:<span>"+k.username+"</span></p><p>支持:<span>"+k.all_support_money+"</span></p><p>角色:<span>"+k.role+"</span></p><p>团队: <span>"+k.children+"人</span></p><h3 people_id="+k.id+" style='color:#ff8b0f'>"+k.realname+"</h3></div>";
							}
                        })
                        html+'</div>';
                        $('.team_total').append(html)
                    }
                });

            }



        }
    })


//提现方式的3种选择
	$('.get_money>input').click(function(){
		var choice=$(this)[0].id;
		$('.get_money>input').removeClass('apply_choice');
		$('.get_money>div').hide();
		$(this).addClass('apply_choice');
		if(choice=='apply_yuer'){
			$('.apply_yuer').show();
		}
		if(choice=='apply_shouyi'){
			$('.apply_shouyi').show();
		}
		if(choice=='apply_yongjing'){
			$('.apply_yongjing').show();
		}
	})

	

});