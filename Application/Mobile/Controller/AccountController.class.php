<?php
namespace Mobile\Controller;
use Think\Upload;
class AccountController extends CommonController{

    /**
     * 我的账户
     */
    public function index(){

        $paramArr = $_REQUEST;

        //>> 判断用户是否登录
        if($this->isLogin != 1){
            $this->redirect('Login/index');
            exit;
        }
        $personModel = M('Member as a');
        $row = $personModel->where(['id'=>$this->userInfo['id'],'username'=>$this->userInfo['username']])->find();

        //>> 查询消费情况(支持)

        $consume_1 = $personModel->field('a.username,b.support_money,b.create_time,b.order_number')
            ->join('inner join an_member_support as b on a.id = b.member_id')
            ->where(['a.id'=>$this->userInfo['id']])
            ->select();
        if(!empty($consume_1)){
            foreach($consume_1 as $key => &$value){
                $value['type'] = '电影支持';
                $value['money'] = $value['support_money'];
            }
            unset($value);
        }
        //>> 查询消费情况(当演员)
        $consume_2 = $personModel->field('b.*')
            ->join('inner join an_member_star as b on a.id = b.member_id')
            ->where(['a.id'=>$this->userInfo['id']])
            ->select();


        if(isset($consume_2)){
            foreach($consume_2 as $key => &$value){
                $value['type'] = '演员申请';
                $value['money'] = 70000;
            }
            unset($value);
        }


        //>> 投票记录
        $consume_3 = $personModel->field('a.username,b.*,sum(b.money) as money')
            ->join('inner join an_member_consume as b on a.id = b.member_id')
            ->where(['a.id'=>$this->userInfo['id'],'b.type'=>'投票'])
            ->group('a.id')
            ->select();


        //>> 查最上级
        $topLeader = $row;


        //>> 所有消费




        //>> 查询当前用户的支持情况
        $rows = M('MemberSupport as a')->field('a.id as aid,a.support_money,b.*')
            ->join('left join an_project as b on a.project_id = b.id')
            ->where(['a.member_id'=>$this->userInfo['id'],'a.is_fh'=>0])
            ->select();
        $count_1 = ceil(count($rows)/4);
        $rows = $this->pagination($rows,1,4);
        //>> 查询积分制度表
        $integral = M('IntegralInstitution')->select();

        //>> 取出当前用户的积分
        $crrIntegral = $row['integral'];

        //>> 取出当前用户等级
        $crrLevel = $row['level'];

        //>> 取出积分表下一个等级对应的积分
        $allInfo = ['status'=>0,'integral'=>$row['integral']];
        foreach($integral as $key => $value){
            if($value['level'] == $crrLevel + 1 ){
                $expIntegral = $value['integral'];
                $allInfo['level'] = $value['level'];
                //>> 算出还需要多少积分
                $needIntegral = $expIntegral - $crrIntegral;
                $allInfo['integral'] = $needIntegral;
                $allInfo['status'] = 1;

            }
        }


        //>> 电影招募演员
        $films = M('ProjectRecruit')->select();


        //>> 查询收藏情况
        $collection = $personModel->where(['member_id'=>$this->userInfo['id']])
            ->join('left join an_member_collection as b on a.id = b.member_id')
            ->join('left join an_project as c on b.project_id = c.id')
            ->select();
        foreach($collection as $key => &$value){
            $value['date'] = date('Y-m-d',$value['showtime']);
            unset($value);
        }

        $collectionCount = ceil(count($collection)/4);
        $collectionList = $this->pagination($collection,1,4);

        $supportMoney = M('MemberSupport')->where(['member_id'=>$this->userInfo['id'],'is_true'=>0])->sum('support_money');


        //>> 查询提问
        $question = M('MemberConsult')->where(['member_id'=>$this->userInfo['id']])->select();

        //>> 支付方式
        $weixin = M('Pay')->where(['name'=>'微信'])->find();
        $ali = M('Pay')->where(['name'=>'支付宝'])->find();

        //>> 查询招募电影
        $recruit = M('ProjectRecruit')->select();

        //>> 查询我的下级
        $follower = M('Member')->where(['parent_id'=>$this->userInfo['id']])->select();

        //>> 查询充值订单
        $orderLst = M('MemberRecharge')->where(['member_id'=>$this->userInfo['id']])->select();
        $count = ceil(count($orderLst)/12);

        if(isset($paramArr['pgNum']) && !empty($paramArr['pgNum']) && is_numeric($paramArr['pgNum'])){
            $pgNum = $paramArr['pgNum'];
        }else{
            $pgNum = 1;
        }
        if(isset($paramArr['pgSize']) && !empty($paramArr['pgSize']) && is_numeric($paramArr['pgSize'])){
            $pgSize = $paramArr['pgSize'];
        }else{
            $pgSize = 12;
        }
        $orderList = $this->pagination($orderLst,$pgNum,$pgSize);
        //>> 账户安全等级
        $safePercent = [
            '1'=>'25%',
            '2'=>'50%',
            '3'=>'75%'
        ];
        $safeLevel = $safePercent[$row['safe_level']];
        //>> 组装电话号码
        $secretPhone = substr($row['username'],0,3).'****'.substr($row['username'],7,4);
        if(IS_AJAX){

            $this->ajaxReturn([
                'count'=>$count,
                'orderList'=>$orderList,
            ]);
        }
        $this->assign([
            'count'=>$count,
            'orderList'=>$orderList,
        ]);



        $this->assign([
            'weixin'=>$weixin,
            'ali'=>$ali,
            'films'=>$films,
            'follower'=>$follower,
            'recruit'=>$recruit,
            'topLeader'=>$topLeader,
            'consume_3'=>$consume_3,
            'consume_2'=>$consume_2,
            'consume_1'=>$consume_1,
            'question'=>$question,
            'allInfo'=>$allInfo,
            'personal'=>$row,
            'collectionCount'=>$collectionCount,
            'collection'=>$collectionList,
            'safeLevel'=>$safeLevel,
            'count_1'=>$count_1,
            'supportSituation'=>$rows,
            'supportMoney'=>$supportMoney ? $supportMoney : 0.00,
            'secretPhone'=>$secretPhone,
        ]);
        $this->display('account/index');
    }

    /**
     * 充值界面
     */
    public function cashIndex(){

        //>> 查询支付方式
        $payWay = M('Pay')->select();
        $weixin = [];
        $ali = [];
       foreach($payWay as $key => $value){
           if($value['name'] == '微信'){
               $weixin = $value;
           }
           if($value['name'] == '支付宝'){

               $ali = $value;
           }
       }

        $this->assign(['weixin'=>$weixin,'ali'=>$ali]);
        $this->display('account/cash');
    }

    /**
     * 提现申请
     */
    public function cash(){

        $paramArr = $_REQUEST;

        $crrDay = date('Y-m-d');
        $lastDay = $this->getTheMonth();


        switch($paramArr['type']){
            case 1:
                //>> 余额提现
                if (!empty($paramArr)) {

                    if (isset($paramArr['money']) && !empty($paramArr['money']) && is_numeric($paramArr['money'])) {
                        //>> 查询余额
                        $row = M('Member')->where(['id' => $this->userInfo['id']])->find();
                        if (empty($row)) {

                            die($this->_printError('1056'));
                        }

                        //>> 判断金额是否大于余额
                        if ($paramArr['money'] > $row['money']) {

                            die($this->_printError('1052'));
                        }

                        //>> 提取现金，生成订单
                        $updateData = [
                            'money' => $row['money'] - $paramArr['money'],
                        ];

                        M()->startTrans();
                        $res = M('Member')->where(['id' => $this->userInfo['id']])->save($updateData);
                        //>> 生成订单
                        $orderNumber = 'CS' . date('Ymd') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);

                        $insertData = [
                            'money' => $paramArr['money'],
                            'member_id' => $this->userInfo['id'],
                            'create_time' => time(),
                            'is_pass' => 0,
                            'order_number' => $orderNumber,
                            'charge' => $paramArr['money'] * 0.1,
                            'type'=>'余额提现'
                        ];

                        //>> 保存订单
                        $ros = M('MemberCash')->add($insertData);
                        if ($ros && $res) {
                            M()->commit();
                            die($this->_printSuccess());
                        } else {
                            M()->rollback();
                            die($this->_printError('1070'));
                        }

                    } else {

                        die($this->_printError('1070'));
                    }
                } else {

                    die($this->_printError('1070'));
                }
                break;

            //>> 收益提现
            case 2:
                //>> 判断当前时间是否是周五
                if (date('w') == 5) {
                    if (!empty($paramArr)) {

                        if (isset($paramArr['money']) && !empty($paramArr['money']) && is_numeric($paramArr['money'])) {
                            //>> 查询余额
                            $row = M('Member')->where(['id' => $this->userInfo['id']])->find();
                            if (empty($row)) {

                                die($this->_printError('1056'));
                            }
                            //>> 判断余额是否大于350

                            if ($row['profit'] < 350) {

                                die($this->_printError('1072'));
                            }

                            //>> 判断金额是否大于余额
                            if ($paramArr['money'] > $row['profit']) {

                                die($this->_printError('1052'));
                            }

                            //>> 提取现金，生成订单
                            $updateData = [
                                'profit' => $row['profit'] - $paramArr['money'],
                            ];

                            M('Member')->startTrans();
                            $res = M('Member')->where(['id' => $this->userInfo['id']])->save($updateData);
                            //>> 生成订单
                            $orderNumber = 'CS' . date('Ymd') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);

                            $insertData = [
                                'money' => $paramArr['money'],
                                'member_id' => $this->userInfo['id'],
                                'create_time' => time(),
                                'is_pass' => 0,
                                'order_number' => $orderNumber,
                                'charge' => $paramArr['money'] * 0.1,
                                'type'=>'收益提现'
                            ];

                            //>> 保存订单
                            $ros = M('MemberCash')->add($insertData);
                            if ($ros && $res) {
                                M('Member')->commit();
                                die($this->_printSuccess());
                            } else {

                                die($this->_printError('1070'));
                            }

                        } else {

                            die($this->_printError('1070'));
                        }
                    } else {

                        die($this->_printError('1070'));
                    }
                } elseif ($crrDay == $lastDay) {
                    if (!empty($paramArr)) {
                        if (isset($paramArr['money']) && !empty($paramArr['money']) && is_numeric($paramArr['money'])) {
                            //>> 查询余额
                            $row = M('Member')->where(['id' => $this->userInfo['id']])->find();
                            if (empty($row)) {

                                die($this->_printError(''));
                            }

                            //>> 判断金额是否大于余额
                            if ($paramArr['money'] > $row['profit']) {

                                die($this->_printError('1052'));
                            }

                            //>> 提取现金，生成订单
                            $updateData = [
                                'profit' => $row['profit'] - $paramArr['money'],
                            ];

                            M()->startTrans();
                            $res = M('Member')->where(['id' => $this->userInfo['id']])->save($updateData);

                            //>> 生成订单
                            $orderNumber = 'CS' . date('Ymd') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);

                            $insertData = [
                                'money' => $paramArr['money'],
                                'member_id' => $this->userInfo['id'],
                                'create_time' => time(),
                                'is_pass' => 0,
                                'order_number' => $orderNumber,
                                'charge' => $paramArr['money'] * 0,
                                'type'=>'收益提现'
                            ];

                            //>> 保存订单
                            $ros = M('MemberCash')->add($insertData);
                            if ($res && $ros) {

                                M('Member')->commit();
                                die($this->_printSuccess());
                            } else {
                                M()->rollback();
                                die($this->_printError('1054'));
                            }

                        } else {

                            die($this->_printError('1054'));
                        }
                    } else {

                        die($this->_printError('1054'));
                    }
                } else {
                    die($this->_printError('1056'));
                }
                break;

            case 3:
                //>> 佣金提现
                if (date('w') == 5) {
                    if (!empty($paramArr)) {

                        if (isset($paramArr['money']) && !empty($paramArr['money']) && is_numeric($paramArr['money'])) {
                            //>> 查询余额
                            $row = M('Member')->where(['id' => $this->userInfo['id']])->find();
                            if (empty($row)) {

                                die($this->_printError('1056'));
                            }
                            //>> 判断余额是否大于350
                            if ($row['commission'] < 350) {

                                die($this->_printError('1072'));
                            }

                            //>> 判断金额是否大于余额
                            if ($paramArr['money'] > $row['commission']) {

                                die($this->_printError('1052'));
                            }


                            //>> 提取现金，生成订单
                            $updateData = [
                                'commission' => $row['commission'] - $paramArr['money'],
                            ];

                            M()->startTrans();
                            $res = M('Member')->where(['id' => $this->userInfo['id']])->save($updateData);
                            //>> 生成订单
                            $orderNumber = 'CS' . date('Ymd') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);

                            $insertData = [
                                'money' => $paramArr['money'],
                                'member_id' => $this->userInfo['id'],
                                'create_time' => time(),
                                'is_pass' => 0,
                                'order_number' => $orderNumber,
                                'charge' => 0,
                                'type'=>'佣金提现'
                            ];

                            //>> 保存订单
                            $ros = M('MemberCash')->add($insertData);
                            if ($ros && $res) {
                                M()->commit();
                                die($this->_printSuccess());
                            } else {
                                M()->rollback();
                                die($this->_printError('1070'));
                            }

                        } else {

                            die($this->_printError('1070'));
                        }
                    } else {

                        die($this->_printError('1070'));
                    }
                }else{
                    die($this->_printError('1050'));
                }
                break;
        }

    }


    /**
     * 提现界面
     */
    public function rechargeIndex(){

        $row = M('Member')->where(['id'=>$this->userInfo['id'],'username'=>$this->userInfo['username']])->find();
        $this->assign('user',$row);
        $this->display('account/recharge');
    }

    /**
     * 账户充值
     */
    public function recharge(){

        $paramArr = $_REQUEST;

        //>> 判断用户是否登录
        if($this->isLogin == 0){
            $this->redirect('Login/index');
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
            die($this->_printError('1068'));
        }


        if(!empty($paramArr)){

            if(isset($paramArr['money']) && !empty($paramArr['money']) && is_numeric($paramArr['money'])){

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

    /**
     * 上传凭证
     */
    public function upCredence(){

        //>> 判断是否登录
        if($this->isLogin == 0){
            $this->redirect('Login/index');
            return false;
        }

        //>> 查询支付方式
        $payMode = M('Pay')->where(['name'=>'支付宝'])->select();

        $this->assign('pay',$payMode);
        $this->display('account/credence');
    }

    /**
     * 实名认证
     */
    public function checkTrue(){

       if(IS_POST){
           $paramArr = $_REQUEST;
           if(!empty($paramArr)){

               $updateData = [
                   'realname'=>$paramArr['realname'],
                   'id_card'=>$paramArr['id_card'],
                   'is_true'=>1,
                   'bank_name'=>$paramArr['bank_name'],
                   'bank_card'=>$paramArr['bank_card'],
                   'address'=>$paramArr['bank_address'],
                   'bank_card_name'=>$paramArr['bank_card_name'],
               ];

               $res = M('Member')->where(['id'=>$this->userInfo['id']])->save($updateData);
               if($res === false){
                   $this->ajaxReturn(['status'=>0,'msg'=>'保存失败']);
               }
               $this->ajaxReturn(['status'=>1,'msg'=>'保存成功']);
           }
       }
        $this->display('account/check');
    }

    /**
     * 获取当月最后一天
     */
    function getTheMonth()
    {
        $firstDay = date('Y-m-01', strtotime(date("Y-m-d")));
        $lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));

        return $lastDay;
    }

    /**
     * 上传方法
     */
    public function upToQiniu(){

        $config = [
            'exts'          =>  array('jpg','png','gif','bmp','jpeg'), //允许上传的文件后缀
            'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'      =>  'Upload/', //保存根路径
        ];
        $upload = new Upload($config);
        $res = $upload->uploadOne(array_shift($_FILES));
        // 判断是否上传成功
        if(!$res){
            $this->ajaxReturn([
                'status' => 0,
                'msg' => $upload->getError()
            ]);
        }
        $this->ajaxReturn([
            'status' => 1,
            'url' => $res['url']
        ]);
    }


    public function upToken(){
        require '../ThinkPHP/Library/Vendor/Qiniu/autoload.php';
        // 用于签名的公钥和私钥
        $accessKey = 'Z5oNrz5L2D_XZXW4sEAv_KHOVflPgUKaAXukAKvB';
        $secretKey = 'V9vJxZ7Wc5AeZKXq0XbnJStPDovpLQsKX8qCUeQr';
        // 初始化签权对象
        $auth = new \Qiniu\Auth($accessKey, $secretKey);
        $bucket = 'macarin';
        // 生成上传Token
        $token = $auth->uploadToken($bucket,null,600);
        $this->ajaxReturn(['uptoken'=>$token]);
    }
}