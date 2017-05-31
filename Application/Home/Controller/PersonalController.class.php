<?php
namespace Home\Controller;

use Think\Controller;
use Think\Upload;

class PersonalController extends CommonController
{


    /**
     * 查询下级
     */
    public function find()
    {

        $paramArr = $_REQUEST;
        if (!empty($paramArr)) {

            $rows = M('Member')->where(['parent_id' => $paramArr['id']])->select();
            foreach ($rows as &$row) {
                //查询支持金额
                $row['support_money'] = M('MemberSupport')->where(['member_id' => $row['id']])->sum('support_money');
                if (!$row['support_money']) {
                    $row['support_money'] = 0;
                }
            }
            unset($row);
            $this->ajaxReturn($rows);
        }
    }

    /**
     * 递归查询最上级
     */
    private function groupLeader($id)
    {


        $res = M('Member')->where(['id' => $id])->find();

        if ($res['parent_id'] != 0) {
            return $this->groupLeader($res['parent_id']);
        } else {
            return $res;
        }
    }

    /**
     * 递归查询所有下级
     */
    public function getMenuTree($id, $lev = 0)
    {

        static $arrTree = array();
        //>> 查询子类
        $childTree = M('Member')->where(['parent_id' => $id])->select();
        $lev++;
        if (!empty($childTree)) {
            $arrTree['level_' . $lev] = $childTree;
            foreach ($childTree as $key => $value) {
                $this->getMenuTree($value['id'], $lev);
            }
        }
        return $arrTree;
    }


    public function personalCenterPaginationA()
    {

        $paramArr = $_REQUEST;
        //>> 所有收益
        $allGet = M('MemberProfit')->where(['member_id' => $this->userInfo['id']])->order('create_time desc')->select();

        $allGet = $this->pagination($allGet, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);
        foreach ($allGet as $key => &$value) {
            $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        unset($value);
        if (IS_AJAX) {
            $this->ajaxReturn($allGet);
        }
    }

    public function personalCenterPaginationB()
    {

        $paramArr = $_REQUEST;
        $personModel = M('Member as a');
        $consume_3 = $personModel->field('a.username,b.*')
            ->join('inner join an_member_consume as b on a.id = b.member_id')
            ->where(['a.id' => $this->userInfo['id'], 'b.type' => '投票'])
            ->select();
        $consume_3 = $this->pagination($consume_3, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);
        foreach ($consume_3 as $key => &$value) {
            $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        unset($value);
        if (IS_AJAX) {
            $this->ajaxReturn($consume_3);
        }
    }

    public function personalCenterPaginationC()
    {

        $paramArr = $_REQUEST;
        $allConsume = M('MemberConsume')->where(['member_id' => $this->userInfo['id'], 'type' => '转出'])->select();
        $allConsume = $this->pagination($allConsume, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);
        foreach ($allConsume as $key => &$value) {
            $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
        }
        unset($value);
        if (IS_AJAX) {
            $this->ajaxReturn($allConsume);
        }
    }

    public function personalCenterPaginationD()
    {

        $paramArr = $_REQUEST;
        $personModel = M('Member as a');
        $consume_1 = $personModel->field('a.username,b.support_money,b.create_time,b.order_number')
            ->join('inner join an_member_support as b on a.id = b.member_id')
            ->where(['a.id' => $this->userInfo['id']])
            ->select();
        if (!empty($consume_1)) {
            foreach ($consume_1 as $key => &$value) {
                $value['type'] = '电影支持';
                $value['money'] = $value['support_money'];
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            }
            unset($value);
        }

        $consume_1 = $this->pagination($consume_1, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);
        if (IS_AJAX) {
            $this->ajaxReturn($consume_1);
        }
    }

    public function personalCenterPaginationE()
    {

        $paramArr = $_REQUEST;
        $personModel = M('Member as a');
        $consume_2 = $personModel->field('b.*')
            ->join('inner join an_member_consume as b on a.id = b.member_id and b.type="演员申请"')
            ->where(['a.id' => $this->userInfo['id']])
            ->select();


        if (isset($consume_2)) {
            foreach ($consume_2 as $key => &$value) {
                $value['type'] = '演员申请';
                $value['create_time'] = date('Y-m-d H:i:s', $value['create_time']);
            }
            unset($value);
        }
        $consume_2 = $this->pagination($consume_2, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);
        if (IS_AJAX) {
            $this->ajaxReturn($consume_2);
        }
    }

    /**
     * 个人中心
     */
    public function index()
    {


        $paramArr = $_REQUEST;


        //>> 判断用户是否登录
        if ($this->isLogin != 1) {
            $this->redirect('Home/Login/index');
            exit;
        }
        $personModel = M('Member as a');
        $row = $personModel->where(['id' => $this->userInfo['id'], 'username' => $this->userInfo['username']])->find();

        //>> 查询消费情况(支持)

        $consume_1 = $personModel->field('a.username,b.support_money,b.create_time,b.order_number,c.name as cname')
            ->join('inner join an_member_support as b on a.id = b.member_id')
            ->join('inner join an_project as c on b.project_id = c.id')
            ->where(['a.id' => $this->userInfo['id'],'b.is_true'=>0])
            ->select();

        if (!empty($consume_1)) {
            foreach ($consume_1 as $key => &$value) {
                $value['type'] = '电影支持';
                $value['money'] = $value['support_money'];
            }
            unset($value);
        }

        $consume_count_1 = ceil(count($consume_1) / 17);
        $consume_1 = $this->pagination($consume_1, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);
        //>> 查询消费情况(当演员)
        $consume_2 = $personModel->field('b.*')
            ->join('inner join an_member_consume as b on a.id = b.member_id and b.type="演员申请"')
            ->where(['a.id' => $this->userInfo['id']])
            ->select();


        if (isset($consume_2)) {
            foreach ($consume_2 as $key => &$value) {
                $value['type'] = '演员申请';
            }
            unset($value);
        }
        $consume_count_2 = ceil(count($consume_2) / 17);
        $consume_2 = $this->pagination($consume_2, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);

        //>> 投票记录
        $consume_3 = $personModel->field('a.username,b.*')
            ->join('inner join an_member_consume as b on a.id = b.member_id')
            ->where(['a.id' => $this->userInfo['id'], 'b.type' => '投票'])
            ->select();

        $consume_count = ceil(count($consume_3) / 17);
        $consume_3 = $this->pagination($consume_3, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);

        //>> 查最上级
        $topLeader = $row;


        //>> 所有收益
        $allGet = M('MemberProfit')->where(['member_id' => $this->userInfo['id']])->order('create_time desc')->select();
        $allc = ceil(count($allGet) / 17);
        $allGet = $this->pagination($allGet, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);

        //>> 转账消费
        $allConsume = M('MemberConsume')->where(['member_id' => $this->userInfo['id'], 'type' => '转出'])->select();
        $allcon = ceil(count($allConsume) / 17);
        $allConsume = $this->pagination($allConsume, $paramArr['pgNum'] ? $paramArr['pgNum'] : 1, $paramArr['pgSize'] ? $paramArr['pgSize'] : 17);


        //>> 查询当前用户的支持情况
        $rows = M('MemberSupport as a')->field('a.id as aid,a.support_money,a.project_id,a.type as atype,a.float,a.fixed,b.*')
            ->join('left join an_project as b on a.project_id = b.id')
            ->where(['a.member_id' => $this->userInfo['id'], 'a.is_fh' => 0])
            ->select();

        $count_1 = ceil(count($rows) / 4);
        $rows = $this->pagination($rows, 1, 4);
//        //>> 查询积分制度表
//        $integral = M('IntegralInstitution')->select();
//
//        //>> 取出当前用户的积分
//        $crrIntegral = $row['integral'];
//
//        //>> 取出当前用户等级
//        $crrLevel = $row['level'];
//
//        //>> 取出积分表下一个等级对应的积分
//        $allInfo = ['status'=>0,'integral'=>$row['integral']];
//        foreach($integral as $key => $value){
//            if($value['level'] == $crrLevel + 1 ){
//                $expIntegral = $value['integral'];
//                $allInfo['level'] = $value['level'];
//                //>> 算出还需要多少积分
//                $needIntegral = $expIntegral - $crrIntegral;
//                $allInfo['integral'] = $needIntegral;
//                $allInfo['status'] = 1;
//
//            }
//        }


        //>> 电影招募演员
        $films = M('ProjectRecruit')->select();


        //>> 查询收藏情况
        $collection = $personModel
            ->field('a.*,b.id as cid,c.*')
            ->join('left join an_member_collection as b on a.id = b.member_id')
            ->join('left join an_project as c on b.project_id = c.id')
            ->where(['member_id' => $this->userInfo['id']])
            ->select();
        foreach ($collection as $key => &$value) {
            $value['date'] = date('Y-m-d', $value['showtime']);
            unset($value);
        }

        $collectionCount = ceil(count($collection) / 4);
        $collectionList = $this->pagination($collection, 1, 4);

        $supportMoney = M('MemberSupport')->where(['member_id' => $this->userInfo['id'], 'is_true' => 0])->sum('support_money');


        //>> 查询提问
        $question = M('MemberConsult')->where(['member_id' => $this->userInfo['id']])->select();

        //>> 支付方式
        $weixin = M('Pay')->where(['name' => '微信'])->find();
        $ali = M('Pay')->where(['name' => '支付宝'])->find();
        $yinlian = M('Pay')->where(['name' => '公司银联'])->select();

        //>> 查询招募电影
        $recruit = M('ProjectRecruit')->select();

        //>> 查询总直推人数
        $allFollowers = M('Member')->where(['parent_id' => $this->userInfo['id']])->count();

        //>> 计算当月的新增业绩


        //>> 查询我的下级(有效)
        $follower = M('Member')->where(['parent_id' => $this->userInfo['id'],'role'=>['egt',1]])->count();
        

        //>> 查询我的团队(总人数)
        $group = $this->allMembers($this->userInfo['id']);



        //>> 查询我的团队(有效)

        $notTrue = $this->notTrue($this->userInfo['id']);



        //>> 查询充值订单
        $orderLst = M('MemberRecharge as a')->field('a.*,b.name as payname')
            ->join('left join an_pay as b on a.type = b.id')
            ->where(['member_id' => $this->userInfo['id']])
            ->select();

        $count = ceil(count($orderLst) / 12);

        if (isset($paramArr['pgNum']) && !empty($paramArr['pgNum']) && is_numeric($paramArr['pgNum'])) {
            $pgNum = $paramArr['pgNum'];
        } else {
            $pgNum = 1;
        }
        if (isset($paramArr['pgSize']) && !empty($paramArr['pgSize']) && is_numeric($paramArr['pgSize'])) {
            $pgSize = $paramArr['pgSize'];
        } else {
            $pgSize = 12;
        }
        $orderList = $this->pagination($orderLst, $pgNum, $pgSize);
        //>> 账户安全等级
        $safePercent = [
            '1' => '35%',
            '2' => '65%',
            '3' => '100%'
        ];
        $safeLevel = $safePercent[$row['safe_level']];
        //>> 组装电话号码
        $secretPhone = substr($row['username'], 0, 3) . '****' . substr($row['username'], 7, 4);
        if (IS_AJAX) {

            $this->ajaxReturn([
                'count' => $count,
                'orderList' => $orderList,
            ]);
        }
        $this->assign([
            'count' => $count,
            'orderList' => $orderList,
        ]);


        $this->assign([
            'notTrue' => $notTrue,
            'group' => $group,
            'allConsume' => $allConsume,
            'weixin' => $weixin,
            'yinlian' => $yinlian,
            'ali' => $ali,
            'films' => $films,
            'allget' => $allGet,
            'allc' => $allc,
            'allcon' => $allcon,
            'consume_count_1' => $consume_count_1,
            'consume_count_2' => $consume_count_2,
            'consume_count' => $consume_count,
            'allFollowers'=>$allFollowers,
            'follower' => $follower,
            'recruit' => $recruit,
            'topLeader' => $topLeader,
            'consume_3' => $consume_3,
            'consume_2' => $consume_2,
            'consume_1' => $consume_1,
            'question' => $question,
            //'allInfo'=>$allInfo,
            'personal' => $row,
            'collectionCount' => $collectionCount,
            'collection' => $collectionList,
            'safeLevel' => $safeLevel,
            'count_1' => $count_1,
            'supportSituation' => $rows,
            'supportMoney' => $supportMoney ? $supportMoney : 0.00,
            'secretPhone' => $secretPhone,
        ]);
        $this->display('personal/index');
    }

    /**
     * 查询直推详情
     */
    public function groupInfo()
    {

        $paramArr = $_REQUEST;


        $res = M('Member')->field('username,role,all_support_money,realname,id,is_true')->where(['parent_id' => $paramArr['id']])->order('is_true')->select();
        $sum = 0;
        foreach ($res as &$value) {
            //>> 所有收益
            $value['sum'] = M('MemberProfit')->where(['member_id' => $value['id'], 'is_ok' => 1])->sum('money');
            $value['sum'] = $value['sum'] ? $value['sum'] : 0;
            if ($value['is_true'] == 0) {
                $value['realname'] = '未实名';
                $sum += 1;
            }

            //>> 直属下级
            $value['children'] = M('Member')->where(['parent_id' => $value['id']])->count();
            $value['children'] = $value['children'] ? $value['children'] : 0;

            switch ($value['role']) {
                case 0:
                    $value['role'] = '暂无';
                    break;
                case 1:
                    $value['role'] = '支持者';
                    break;
                case 2:
                    $value['role'] = '经纪人';
                    break;
                case 3:
                    $value['role'] = '制片人';
                    break;
                case 4:
                    $value['role'] = '出品人';
                    break;
            }
        }
        unset($value);

        if (!empty($res)) {

            $this->ajaxReturn([
                'res' => $res,
            ]);
        }
    }

    /**
     * 分页
     */
    public function pagination($data = [], $phNum, $pgSize)
    {

        if (empty($data)) return false;

        $start = ($phNum - 1) * $pgSize;

        $sliceArr = array_slice($data, $start, $pgSize);

        return $sliceArr;
    }

    /**
     * 保存信息
     */
    public function save()
    {

        $paramArr = $_REQUEST;

        if (!empty($paramArr)) {
            if (isset($paramArr['sex']) && !empty($paramArr['sex']) && is_numeric($paramArr['sex'])) {

                $insertData = [
                    'sex' => $paramArr['sex']
                ];


                $res = M('Member')->where(['id' => $this->userInfo['id']])->save($insertData);


                if ($res != 0) {

                    die($this->_printSuccess());
                } else {

                    die($this->_printError(''));
                }
            } else {

                die($this->_printError(''));
            }
        } else {

            die($this->_printError(''));
        }
    }

    /**
     * 安全信息
     */
    public function safeInfo()
    {

        $paramArr = $_REQUEST;

        if (!empty($paramArr)) {
            $memberModel = M('Member');
            $updateData = [
                'is_true' => isset($paramArr['realname']) ? 1 : 0,
                'bank_name' => isset($paramArr['bank_name']) ? $paramArr['bank_name'] : '',
                'realname' => $paramArr['realname'],
                'id_card' => $paramArr['id_card'],
                'bank_card_name' => $paramArr['bank_card_name'],
                'bank_card' => $paramArr['bank_card'],
                'city' => $paramArr['city'],
                'address' => $paramArr['address'],
                'safe_level' => 3
            ];
            $res = $memberModel->where(['id' => $this->userInfo['id']])->save($updateData);
            if ($res === false) {
                $this->ajaxReturn(['status' => 0]);
            }
            $this->ajaxReturn(['status' => 1]);
        }
    }

    /**
     * 提现
     */
    public function cash()
    {

        $paramArr = $_REQUEST;

        $crrDay = date('Y-m-d');
        $lastDay = $this->getTheMonth();
       switch($paramArr['id']){
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


                       //>> 判断金额和协议
                       if ($paramArr['money'] <= 700) {

                           $agree = session('export' . $this->userInfo['id'].$paramArr['id']);
                           if (!$agree) {

                               die($this->_printError('1062'));
                           }
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
                           die($this->_printError('1056'));
                       }

                   } else {

                       die($this->_printError('1056'));
                   }
               } else {

                   die($this->_printError('1056'));
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
                           if ($row['money'] < 350) {

                               die($this->_printError('1066'));
                           }

                           //>> 判断金额是否大于余额
                           if ($paramArr['money'] > $row['profit']) {

                               die($this->_printError('1052'));
                           }


                           //>> 判断金额和协议
                           if ($paramArr['money'] <= 700) {

                               $agree = session('export' . $this->userInfo['id'].$paramArr['id']);
                               if (!$agree) {

                                   die($this->_printError('1062'));
                               }
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

                               die($this->_printError('1056'));
                           }

                       } else {

                           die($this->_printError('1056'));
                       }
                   } else {

                       die($this->_printError('1056'));
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
                           if ($row['money'] < 350) {

                               die($this->_printError('1066'));
                           }

                           //>> 判断金额是否大于余额
                           if ($paramArr['money'] > $row['commission']) {

                               die($this->_printError('1052'));
                           }


                           //>> 判断金额和协议
                           if ($paramArr['money'] <= 700) {

                               $agree = session('export' . $this->userInfo['id'].$paramArr['id']);
                               if (!$agree) {

                                   die($this->_printError('1062'));
                               }
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
                               die($this->_printError('1056'));
                           }

                       } else {

                           die($this->_printError('1056'));
                       }
                   } else {

                       die($this->_printError('1056'));
                   }
               }else{
                   die($this->_printError('1050'));
               }
               break;
       }
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
     * 图片文件上传
     */
    public function upload()
    {
        $config = [
            'exts' => array('jpg', 'png', 'gif', 'bmp'), //允许上传的文件后缀
            'subName' => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => 'Upload/', //保存根路径
        ];
        $upload = new Upload($config);
        $rst = $upload->uploadOne(array_shift($_FILES));
        // 判断是否上传成功
        if ($rst == false) {
            $this->Msg['msg'] = $upload->getError();
            $this->ajaxReturn($this->Msg);
        }
        if (!$rst) {
            $this->ajaxReturn([
                'status' => 0,
                'msg' => '文件上传失败'
            ]);
        }

        $this->ajaxReturn([
            'status' => 1,
            'url' => $rst['url']
        ]);
    }

    /**
     * 忘记密码
     */
    public function modify()
    {


    }

    /**
     * 我的支持分页
     */

    public function mySupport()
    {

        $paramArr = $_REQUEST;

        $pgNum = $paramArr['pgNum'];
        $pgSize = 4;

        $rows = M('MemberSupport as a')->field('a.id as aid,a.support_money,a.project_id,a.type as atype,a.float,a.fixed,b.*')
            ->join('left join an_project as b on a.project_id = b.id')
            ->where(['a.member_id' => $this->userInfo['id'], 'a.is_fh' => 0])
            ->select();

        $rows = $this->pagination($rows, $pgNum, $pgSize);

        $this->ajaxReturn([
            'rows' => $rows,
        ]);
    }

    /*
     * 我的收藏分页
     */
    public function myCollection()
    {

        $paramArr = $_REQUEST;
        //>> 查询收藏情况
        $collection = M('Member as a')
            ->field('a.*,b.id as cid,c.*')
            ->join('left join an_member_collection as b on a.id = b.member_id')
            ->join('left join an_project as c on b.project_id = c.id')
            ->where(['member_id' => $this->userInfo['id']])
            ->select();
        foreach ($collection as $key => &$value) {
            $value['date'] = date('Y-m-d', $value['showtime']);
            unset($value);
        }

        $collectionList = $this->pagination($collection, $paramArr['pgNum'], 4);
        $this->ajaxReturn([
            'rows' => $collectionList,
        ]);
    }


    /**
     * 修改密码
     */
    public function editPassword()
    {

        $paramArr = $_REQUEST;

        if (!empty($paramArr)) {

            $user = M('Member')->where(['id' => $this->userInfo['id']])->find();

            if (!empty($user)) {

                if ($user['password'] != md5($paramArr['password'])) {

                    $this->ajaxReturn(['status' => 0, 'msg' => '当前密码错误']);
                }
            }
            $updateData = [
                'password' => md5($paramArr['newpassword']),
                'ori_password' => $paramArr['newpassword'],
            ];
            $res = M('Member')->where(['id' => $this->userInfo['id']])->save($updateData);
            if ($res != false) {
                //>> 删除session中的值
                session(md5('home'), null);
                $this->ajaxReturn([
                    'status' => 1,
                    'msg' => '修改成功',
                ]);
            } else {
                $this->ajaxReturn([
                    'status' => 0,
                    'msg' => '修改失败'
                ]);
            }
        } else {

            $this->ajaxReturn([
                'status' => 0
            ]);
        }
    }

    /**
     * 我要当演员
     */
    public function star()
    {

        $paramArr = $_REQUEST;
        if (!empty($paramArr)) {

            M('Member')->startTrans();

            //>> 将消费信息写入数据库
            $data = [
                'money' => $paramArr['money'],
                'create_time' => time(),
                'member_id' => $this->userInfo['id'],
                'type' => '演员申请',
                'project_id' => isset($paramArr['id']) ? $paramArr['id'] : 0,
            ];

            M('MemberConsume')->add($data);
            $insertData = [
                'name' => $paramArr['name'],
                'sex' => $paramArr['sex'],
                'volk' => $paramArr['volk'],
                'birthday' => $paramArr['birthday'],
                'height' => $paramArr['height'],
                'id_card' => $paramArr['id_card'],
                'phone' => $paramArr['phone'],
                'email' => $paramArr['email'],
                'address' => $paramArr['address'],
                'skill' => $paramArr['skill'],
                'expirence' => $paramArr['ex'],
                'image_url' => $paramArr['image_url'],
                'member_id' => $this->userInfo['id'],
                'project_id' => session('filmId'),
                'role_id' => session('roleId'),
                'is_pass' => 2,
                'create_time' => time(),
            ];

            $res = M('MemberStar')->add($insertData);
            $re = M('Member')->where(['id' => $this->userInfo['id']])->save(['money' => $this->userInfo['money'] - $paramArr['money']]);
            if ($res && $re) {

                M('Member')->commit();
                die($this->_printSuccess());
            } else {

                M('Member')->rollback();
                die($this->_printError(''));
            }
        } else {

            die($this->_printError(''));
        }
    }


    /**
     * 查看电影详情
     */
    public function filmDetail()
    {

        $paramArr = $_REQUEST;

        if (!empty($paramArr)) {
            session('filmId', $paramArr['id']);
            if (isset($paramArr['id']) && is_numeric($paramArr['id']) && !empty($paramArr['id'])) {

                $film = M('ProjectRecruit')->where(['id' => $paramArr['id']])->find();
                $film['role_id'] = json_decode($film['role_id']);

                //>> 默认查询第一个角色的详细信息
                $roleDetail = M('RoleDescription')->where(['recruit_id' => $film['id'], 'role_id' => $film['role_id'][0]])->find();
                if (!empty($film)) {
                    static $dataArr = [];
                    //>> 循环查询角色
                    foreach ($film['role_id'] as $key => $value) {
                        $row = M('ProjectRole')->where(['id' => $value])->find();
                        $dataArr[$key] = array_merge($row);
                    }
                    $this->ajaxReturn([
                        'status' => 0,
                        'role' => $dataArr,
                        'film' => $film,
                        'roleDetail' => $roleDetail
                    ]);
                }
            } else {

                $this->ajaxReturn([
                    'status' => 0,
                    'msg' => '查询失败'
                ]);
            }
        } else {

            $this->ajaxReturn([
                'status' => 0,
                'msg' => '数据为空'
            ]);
        }
        //$this->display('role/detail');
    }

    /**
     * 保存
     */
    public function saveId()
    {

        $paramArr = $_REQUEST;
        //>> 将电影id保存到session中
        if (isset($paramArr['roleId']) && !empty($paramArr['roleId'])) {
            session('roleId', $paramArr['roleId']);
        }
    }

    /**
     * 申请演员完成提示
     */
    public function tips()
    {

        $this->display('personal/tips');
    }

    /**
     * 同意充值
     */
    public function agree()
    {

        $paramArr = $_REQUEST;

        if (!empty($paramArr)) {
            if (isset($paramArr['agree']) && !empty($paramArr['agree']) && is_numeric($paramArr['agree'])) {
                //>> 保存到session中
                session('agree' . $this->userInfo['id'], $paramArr['agree']);
                $this->ajaxReturn(['status' => 1]);
            } else {

                session('agree' . $this->userInfo['id'], null);
                $this->ajaxReturn(['status' => 1]);
            }
        }
    }

    /**
     * 同意提现
     */
    public function exportAgree()
    {

        $paramArr = $_REQUEST;

        if (!empty($paramArr)) {
            if (isset($paramArr['export']) && !empty($paramArr['export']) && is_numeric($paramArr['export'])) {
                //>> 保存到session中
                session('export' . $this->userInfo['id'].$paramArr['id'], $paramArr['export']);
                $this->ajaxReturn(['status' => 1]);
            } else {

                session('export' . $this->userInfo['id'].$paramArr['id'], null);
                $this->ajaxReturn(['status' => 1]);
            }
        }
    }

    /**
     * 退款
     */
    public function feedBack()
    {

        $paramArr = $_REQUEST;

        if (!empty($paramArr)) {


            $res = M('MemberProfit')->where(['support_id' => $paramArr['orderId'], 'is_ok' => 1])->select();
            $order = M('MemberSupport')->where(['id' => $paramArr['orderId']])->find();

            M()->startTrans();
            if (!empty($res)) {

                foreach ($res as $value) {
                    $res = M('Member')->where(['id' => $value['member_id']])->save(['money' => ['exp', 'money-' . $value['money']]]);

                    if ($res === false) {

                        M()->rollback();
                        $this->ajaxReturn(['msg' => '退款失败', 'status' => 0]);
                    } else {

                        $result = M('MemberProfit')->where(['id' => $value['id']])->delete();
                        if ($result === false) {

                            M()->rollback();
                            $this->ajaxReturn(['msg' => '退款失败', 'status' => 0]);
                        }
                    }
                }
                $re = M('Member')->where(['id' => $order['member_id']])->save(['money' => ['exp', 'money+' . $order['support_money'] * 0.9]]);

                if ($re === false) {

                    M()->rollback();
                    $this->ajaxReturn(['msg' => '退款失败', 'status' => 0]);
                }

                // 修改项目支持金额
                $result = M('Project')->where(['id' => $order['project_id']])->save(['money' => ['exp', 'money-' . $order['support_money']]]);
                if ($result === false) {

                    M()->rollback();
                    $this->ajaxReturn(['msg' => '退款失败', 'status' => 0]);
                }

                $result = M('MemberSupport')->where(['id' => $order['id']])->delete();

                if ($result === false) {

                    M()->rollback();
                    $this->ajaxReturn(['msg' => '退款失败', 'status' => 0]);
                }

                M()->commit();
                $this->ajaxReturn([
                    'status' => 1,
                    'msg' => '退款成功',
                ]);


            } else {
                $rest = M('Member')->where(['id' => $order['member_id']])->save(['all_support_money' => ['exp', 'all_support_money-' . $order['support_money']]]);
                $re = M('Member')->where(['id' => $order['member_id']])->save(['money' => ['exp', 'money+' . $order['support_money'] * 0.9]]);

                if ($re === false) {

                    M()->rollback();
                    $this->ajaxReturn(['msg' => '退款失败', 'status' => 0]);
                }

                // 修改项目支持金额
                $result = M('Project')->where(['id' => $order['project_id']])->save(['money' => ['exp', 'money-' . $order['support_money']], 'support_number' => ['exp', 'support_number-' . 1]]);
                if ($result === false) {

                    M()->rollback();
                    $this->ajaxReturn(['msg' => '退款失败', 'status' => 0]);
                }

                $result = M('MemberSupport')->where(['id' => $order['id']])->delete();
                if ($result === false) {

                    M()->rollback();
                    $this->ajaxReturn(['msg' => '退款失败', 'status' => 0]);
                }

                M()->commit();
                $this->ajaxReturn([
                    'status' => 1,
                    'msg' => '退款成功',
                ]);

            }
        } else {

            $this->ajaxReturn([
                'msg' => '退款失败',
                'status' => 0
            ]);
        }
    }

    /*
     * 切换角色
     */
    public function getFilm()
    {

        $id = $_REQUEST;

        $row = M('RoleDescription')->where(['recruit_id' => $id['film_id'], 'role_id' => $id['role_id']])->find();

        if (!empty($row)) {

            $this->ajaxReturn([
                'status' => 1,
                'data' => $row,
            ]);
        } else {

            $this->ajaxReturn([
                'data' => [],
                'status' => 1
            ]);
        }
    }

    /**
     * 转账账号
     */
    public function checkAccount()
    {

        $paramArr = $_REQUEST;
        if (!empty($paramArr)) {

            //>> 根据账号查询
            $row = M('Member')->where(['username' => $paramArr['username']])->find();

            if (!empty($row)) {

                $this->ajaxReturn(['status' => 1, 'msg' => '请求成功']);
            } else {

                $this->ajaxReturn(['status' => 0, 'msg' => '请求失败']);
            }
        } else {

            $this->ajaxReturn(['status' => 0, 'msg' => '数据为空']);
        }
    }

    /**
     * 转入账户
     */
    public function changeMoney()
    {

        $paramArr = $_REQUEST;

        if (!empty($paramArr)) {

            //>> 验证验证码
            $captcha = session('change_code' . $this->userInfo['username']);

            if ($captcha != $paramArr['captcha'] || empty($captcha)) {

                $this->ajaxReturn(['status' => 0, 'msg' => '验证码错误']);
            } else {
                //>> 开启事务
                M()->startTrans();
                //>> 扣除当前用户的余额
                $res = M('Member')->where(['username' => $this->userInfo['username']])->save(['money' => ['exp', 'money-' . $paramArr['money']]]);
                $insertDataA = [
                    'member_id' => $this->userInfo['id'],
                    'to_username' => $paramArr['username'],
                    'money' => $paramArr['money'],
                    'type' => '转出',
                    'create_time' => time(),
                ];
                $res_1 = M('MemberConsume')->add($insertDataA);
                $ros = M('Member')->where(['username' => $paramArr['username']])->save(['money' => ['exp', 'money+' . $paramArr['money']]]);

                $user = M('Member')->where(['username' => $paramArr['username']])->find();
                $insertDataB = [
                    'member_id' => $user['id'],
                    'from_username' => $this->userInfo['username'],
                    'type' => 4,
                    'money' => $paramArr['money'],
                    'create_time' => time(),
                    'remark' => '转入',
                    'is_ok' => 1
                ];
                $res_2 = M('MemberProfit')->add($insertDataB);
                if ($res === false || $ros === false || $res_1 === false || $res_2 === false) {

                    M()->rollback();
                } else {

                    M()->commit();
                    session('change_code' . $this->userInfo['username'], null);
                    $this->ajaxReturn(['status' => 1, 'msg' => '操作成功']);
                }
            }
        } else {

            $this->ajaxReturn(['status' => 0, 'msg' => '数据为空']);
        }
    }

    /**
     * 发送短信
     */
    public function sendMessage()
    {

        $phone = $this->userInfo['username'];

        //>> 判断发短信时间是否大于60秒
        $crrtime = time();

        if ($crrtime - session('change_code_time' . $phone) < 60) {

            $this->ajaxReturn(['msg' => '请60秒以后再发送', 'status' => 0]);
        }

        $str = '0123456789';

        $strArr = str_split($str);

        shuffle($strArr);

        //>> 截取8位
        $endArr = array_slice($strArr, 0, 6);

        $code = implode('', $endArr);

        //>> 将验证码保存到session中
        session('change_code' . $phone, $code);
        session('change_code_time' . $phone, time());
        $res = sendSMS($phone, $code, $this->systemInfo, 1);
        if ($res) {

            //>> 保发送时间
            session('create_time' . $phone, time());

            die($this->_printSuccess());

        } else {

            die($this->_printError('1002'));

        }
    }

    public function cancel()
    {
        if (IS_POST && IS_AJAX) {
            $id = intval(i('post.id'));
            $rest = M('MemberCollection')->where(['id' => $id])->delete();
            if ($rest === false) {
                $this->ajaxReturn(['msg' => "取消失败", 'status' => 0]);
            } else {
                $this->ajaxReturn(['msg' => "取消成功", 'status' => 1]);
            }
        }
    }

    /**
     * 根据电话号码查找成员
     */
    function findMemberByTel()
    {

        $paramArr = $_REQUEST;

        if (empty($paramArr)) return false;

        $memberInfo = M('Member')->where(['username' => $paramArr['username']])->find();
        if (!empty($memberInfo)) {
            $memberInfo['realname'] = $memberInfo['realname'] ? $memberInfo['realname'] : '未实名';
            switch ($memberInfo['role']) {
                case 0:
                    $memberInfo['role'] = '暂无';
                    break;
                case 1:
                    $memberInfo['role'] = '支持者';
                    break;
                case 2:
                    $memberInfo['role'] = '经纪人';
                    break;
                case 3:
                    $memberInfo['role'] = '制片人';
                    break;
                case 4:
                    $memberInfo['role'] = '出品人';
                    break;
            }

            $this->ajaxReturn([
                'status'=>1,
                'memberInfo'=>$memberInfo
            ]);
        }
        $this->ajaxReturn(['status'=>0]);
    }


    /**
     * 查询是否有拒绝订单
     */
    public function checkHasRefuse(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){

            $order = M('MemberRecharge')->where(['member_id'=>$paramArr['username'],['is_pass'=>2],['create_time'=>['egt',1495209600]]])->find();
            if(!empty($order)){

                $create_time = date('Y-m-d  H:i:s',$order['create_time']);
                $this->ajaxReturn(['status'=>1,'order'=>$order['order_number'],'create_time'=>$create_time]);
            }else{


                $this->ajaxReturn(['status'=>0]);
            }

        }else{

            $this->ajaxReturn(['status'=>0]);
        }
    }

    /**
     * 如果有拒绝订单
     */
    public function ifHasRefuse(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr) && is_numeric($paramArr['username'])){

            $user = M('Member')->field('username')->where(['id'=>$paramArr['username']])->find();

            if(!empty($user)){

                session(md5('order'.$user['username']),$paramArr['order']);
            }
        }
    }
}