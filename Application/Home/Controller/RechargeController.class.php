<?php
namespace Home\Controller;

use Think\Controller;

class RechargeController extends CommonController{

    /**
     * 账户充值
     */
    public function recharge(){

        $paramArr = $_REQUEST;

        //>> 判断用户是否登录
        if($this->isLogin == 0){
            $this->redirect('Home/Login/index');
            exit;
        }
        //>> 判断用户是否有权限充值
        $memberModel = M('Member');

        $row = $memberModel->where(['id'=>$this->userInfo['id']])->find();

        if($row['is_allowed_recharge'] != 1){

            die($this->_printError('1046'));
    }
        //>> 判断用户是否已经有还未审核的充值订单，如果有，就不能再继续充值了
        $rechargeOrder = M('MemberRecharge')->where(['member_id'=>$row['id'],'is_pass'=>0])->find();

        if(!empty($rechargeOrder)){
            die($this->_printError('1064'));
        }

        if(!empty($paramArr)){

            if(isset($paramArr['money']) && !empty($paramArr['money']) && is_numeric($paramArr['money'])){

                if($paramArr['money'] <= 700){

                    //>> 判断用户是否点击确认协议
                    $agree = session('agree'.$this->userInfo['id']);
                    if(!$agree){

                        die($this->_printError('1062'));
                    }
                }

                //>> 判断用户是新增的订单还是重新提交被拒绝的订单
                $token = session(md5('order'.$this->userInfo['username']));

                if(!empty($token)){

                    //>> 直接更新
                   $res =  M('MemberRecharge')->where(['order_number'=>session(md5('order'.$this->userInfo['username'])),'member_id'=>$this->userInfo['id']])->save(['is_pass'=>0,'image_url'=>$paramArr['image_url'],'remark'=>'','create_time'=>time(),'money'=>$paramArr['money']]);

                   if($res === false){

                       die($this->_printError('1048'));
                   }else{

                       session(md5('order'.$this->userInfo['username']),null);
                       $this->ajaxReturn(['status'=>3,'msg'=>'更新成功,请等待管理员审核']);
                   }
                }

                M()->startTrans();
                //>> 生成流水号
                $orderNumber = 'RE'.date('Ymd') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
                $orderData = [
                    'member_id'=>$this->userInfo['id'],
                    'money'=>$paramArr['money'],
                    'create_time'=>time(),
                    'type'=>$paramArr['type'],
                    'is_pass'=>0,
                    'order_number'=>$orderNumber,
                    'image_url'=>$paramArr['image_url'],
                ];
                //>> 添加到充值订单表
                $ros = M('MemberRecharge')->add($orderData);

                if($ros){

                    M()->commit();

                    die($this->_printSuccess());

                }else{

                    die($this->_printError('1048'));
                }

            }else{

                die($this->_printError('1048'));
            }
        }else{

            die($this->_printError(''));
        }

    }
}