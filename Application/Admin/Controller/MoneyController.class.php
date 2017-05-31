<?php
namespace Admin\Controller;

class MoneyController extends CommonController
{
    /**
     * 浮动分红
     */
    public function index()
    {
        if (IS_POST && IS_AJAX) {
            $id = intval(I('post.id'));
            // 判断项目是否存在
            $projectInfo = M('Project')->find($id);
            if (!$projectInfo) {
                $this->ajaxReturn(['msg' => "项目不存在", 'status' => 0]);
            }

            if ($projectInfo['is_active'] == 1) {
                $this->ajaxReturn(['msg' => "项目还未下架，不能浮动分红", 'status' => 0]);
            }

          /*  if (!$projectInfo['float_rate']) {
                $this->ajaxReturn(['msg' => "项目未设置浮动分红比例", 'status' => 0]);
            }*/


            //=============================== 立即分红===========================//

            // 查询出当前项目所有支持订单
            $where = [
                'project_id' => $projectInfo['id'],//当前项目
//                'is_fh' => 0,//已分红的订单
                'is_true' => 0,//未返回金额
                'type'=>2     //订单为浮动分红方式的用户
            ];
            $supportInfo = M('MemberSupport')
                ->where($where)->select();

            if (!$supportInfo) {
                $this->ajaxReturn(['msg' => "该项目没有支持订单产生", 'status' => 0]);
            }
            M()->startTrans();
            // 静态分红
            foreach ($supportInfo as $info) {

                // 返还本金
                $money = $info['support_money'];
                $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money+' . $money]]);
                if ($rest === false) {
                    M()->rollback();
                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                }

                // 向会员收益表追加一条记录
                $rest = M('MemberProfit')->add([
                    'member_id' => $info['member_id'],
                    'money' => $money,
                    'create_time' => time(),
                    'type' => 1,
                    'is_ok' => 1,
                    'remark' => $projectInfo['name'] . "影片本金返还",
                ]);
                if ($rest === false) {
                    M()->rollback();
                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                }

                // 产生票房收益
                $money1 = $info['support_money'] * ($projectInfo['float_rate'] / 100);
                $rest = M('Member')->where(['id' => $info['member_id']])->save(['profit' => ['exp', 'profit+' . $money1]]);
                if ($rest === false) {
                    M()->rollback();
                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                }

                // 向会员收益表追加一条记录
                $rest = M('MemberProfit')->add([
                    'member_id' => $info['member_id'],
                    'money' => $money1,
                    'create_time' => time(),
                    'type' => 1,
                    'is_ok' => 1,
                    'remark' => $projectInfo['name'] . "影片票房分红",
                ]);
                if ($rest === false) {
                    M()->rollback();
                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                }

                // 修改订单状态
                $rest = M('MemberSupport')
                    ->where(['id' => $info['id']])
                    ->save([
                        'is_fh' => 1,
                        'float' => $info['support_money'] * $projectInfo['float_rate']/100,
                        'is_true' => 1,
                    ]);
                if ($rest === false) {
                    M()->rollback();
                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                }

            }
            M()->commit();

            $this->ajaxReturn(['msg' => "返还成功", 'status' => 1]);
        }
    }

    /**
     * 分发支持佣金 票房
     */
    public function fy()
    {
        if (IS_POST && IS_AJAX) {

            $id = intval(I('post.id'));
            // 判断项目是否存在
            $projectInfo = M('Project')->find($id);
            if (!$projectInfo) {
                $this->ajaxReturn(['msg' => "项目不存在", 'status' => 0]);
            }
            // 判断分佣所用到的参数是否完整
            if (!$projectInfo['first_rate']) {
                $this->ajaxReturn(['msg' => "分佣参数未设置", 'status' => 0]);
            }
            if (!$projectInfo['two_rate']) {
                $this->ajaxReturn(['msg' => "分佣参数未设置", 'status' => 0]);
            }
            if (!$projectInfo['three_rate']) {
                $this->ajaxReturn(['msg' => "分佣参数未设置", 'status' => 0]);
            }
            if ($projectInfo['is_active'] == 1) {
                $this->ajaxReturn(['msg' => "项目还未下架，不能进行票房分佣", 'status' => 0]);
            }

            // 查询出所有会员
            $members = M('Member')->select();
            if (!$members) {
                $this->ajaxReturn(['msg' => "该系统没有注册会员", 'status' => 0]);
            }

            //查询出所有支持订单且状态为未票房分佣的订单
            $where = [
                'project_id' => $projectInfo['id'],//当前项目
                'is_pf' => 0,//未分佣的订单
                'is_fh' => 1,//已浮动分红订单
                'type' =>2,
            ];

            $supportInfo = M('MemberSupport')->where($where)->select();


            if (!$supportInfo) {
                $this->ajaxReturn(['msg' => "该项目没有票房分佣的订单", 'status' => 0]);
            }


            // 开启事物
            M()->startTrans();

            foreach ($supportInfo as $info) {//拿到每一笔支持订单

                //接受请求参数
                $member_id = $info['member_id'];//当前订单会员id
                // 查询出当前会员信息
                $memberInfo = M('Member')->find($member_id);

                $box = $info['float'];// 票房收益

                if($box<=0){
                    continue;
                }

                //进行分佣
                $this->genCommission($info, $box, $projectInfo, $memberInfo['parent_id'], 1);
            }
            // 提交事物
            M()->commit();
            $this->ajaxReturn(['msg' => "分佣成功", 'status' => 1]);

        }
    }

    /**
     * @param $info 当前订单信息
     * @param $roi 票房分红
     * @param $projectInfox 当前项目信息
     * @param $parent_id 父级id
     * @param $level 级别
     */
    protected function genCommission($info, $roi, $projectInfox, $parent_id, $level)
    {

        if ($parent_id == 0) {
            $rest = M('MemberSupport')
                ->where(['id' => $info['id']])
                ->save([
                    'is_pf' => 1,
                    'is_ok' => 1,
                ]);
            if ($rest === false) {
                M()->rollback();
                $this->ajaxReturn(['msg' => "分佣失败", 'status' => 0]);
            }
            return;
        }
        $projectInfo = $projectInfox;

        $parent = M('Member')->where(['id' => $parent_id])->find();

        switch ($parent['role']) {
            case "1":
                if ($level > 1) {
                    return;
                }
                if ($level == 1) {//第一父
                    // 计算佣金
                    $commission = $roi * ($projectInfo['first_rate'] / 100);//一代投资额的5%  一代票房收益的5%
                    // 操作数据库
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                break;

            case "2":
                if ($level > 2) {
                    return;
                }
                if ($level == 1) {//第一父
                    // 计算佣金
                    $commission = $roi * ($projectInfo['first_rate'] / 100);// 5%


                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                if ($level == 2) {//第二父
                    $commission = $roi * ($projectInfo['two_rate'] / 100);//  3%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }

                break;

            case "3"://制片人
                //计算票房收益
                if ($level > 3) {
                    return;
                }
                if ($level == 1) {
                    // 计算佣金
                    $commission =  $roi * ($projectInfo['first_rate'] / 100);// 5%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);

                }
                if ($level == 2) {

                    $commission = $roi * ($projectInfo['two_rate'] / 100);// 3%

                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                if ($level == 3) {
                    $commission =  $roi * ($projectInfo['three_rate'] / 100);// 1%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }

                break;

            case "4"://出品人
                if ($level > 3) {
                    return;
                }
                if ($level == 1) {
                    // 计算佣金
                    $commission = $roi * ($projectInfo['first_rate'] / 100);// 5%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                if ($level == 2) {
                    $commission = $roi * ($projectInfo['two_rate'] / 100);// 3%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                if ($level == 3) {
                    $commission = $roi * ($projectInfo['three_rate'] / 100);// 1%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                break;

        }
        $this->genCommission($info, $roi, $projectInfo, $parent['parent_id'], $level + 1);
    }


    /**
     * @param $info 当前订单信息
     * @param $commission 佣金
     * @param $parent 父级
     * @param $projectName 项目名称
     */
    protected function insertDb($info, $commission, $parent, $projectName)
    {

        // 修改订单状态
        $rest = M('MemberSupport')
            ->where(['id' => $info['id']])
            ->save([
                'is_pf' => 1,
                'is_ok' => 1,
            ]);
        if ($rest === false) {
            M()->rollback();
            $this->ajaxReturn(['msg' => "分佣失败", 'status' => 0]);
        }
        // 更新会员余额
        $money = $commission;
        $rest = M('Member')->where(['id' => $parent['id']])->save(['commission' => ['exp', 'commission+' . $money]]);
        if ($rest === false) {
            M()->rollback();
            $this->ajaxReturn(['msg' => "分佣失败", 'status' => 0]);
        }

        // 向会员收益表追加一条记录
        $rest = M('MemberProfit')->add([
            'member_id' => $parent['id'],
            'money' => $money,
            'create_time' => time(),
            'support_id' =>$info['id'],
            'type' => 2,
            'remark' => $projectName . "影片票房分佣",
        ]);
        if ($rest === false) {
            M()->rollback();
            $this->ajaxReturn(['msg' => "分佣失败", 'status' => 0]);
        }
    }

    /**
     * 定时发放固定收益
     */
    public function test()
    {
        // 判断已分红的订单
        $where1 = [
            'is_fh' => 1,//已分红的订单
            'type' => 1,//未分红固定分红方式的订单
        ];
        $supportInfo1 = M('MemberSupport')
            ->where($where1)
            ->select();
        foreach ($supportInfo1 as &$info) {
            // 查询出当前项目
            $projectInfo = M('Project')->find($info['project_id']);
            if (!$projectInfo) {// 项目不存在
                continue;
            }
            // 判断项目在线在线状态 在线就可以进行分红  不在线查看 目标金额是否达到 未达到 则分红失败
            if ($projectInfo['is_active'] == 0 && $projectInfo['is_ok'] == 0) {

                // 当前订单用户的分红收益全部失效
                $profits = M('MemberProfit')->where(['support_id' => $info['id'],'type'=>1])->select();
                // 统计要扣除的收益
                $memberInfo = M('Member')->where(['id' => $info['member_id']])->find();

                // 统计收益共计金额
                $count = M('MemberProfit')->where(['support_id' => $info['id'],'type'=>1])->sum('money');

                // 当前钱包收益余额
                $profitMoney = $memberInfo['profit'];

                if($profitMoney>$count){// 够扣，直接减去
                    $money = $info['support_money'];
                    if($info['is_true'] == 0){
                        $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money+' . $money]]);
                        if ($rest === false) {
                            M()->rollback();
                            $this->ajaxReturn(['msg' => "本金返还失败", 'status' => 0]);
                        }

                        // 向会员收益表追加一条记录
                        $rest = M('MemberProfit')->add([
                            'member_id' => $info['member_id'],
                            'money' => $money,
                            'create_time' => time(),
                            'type' => 1,
                            'remark' => $projectInfo['name'] . "影片本金返还",
                        ]);
                        if ($rest === false) {
                            M()->rollback();
                            $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                        }
                    }

                    foreach ($profits as $profit) {
                        // 修改收益状态
                        $rest = M('MemberProfit')->where(['id' => $profit['id']])->save(['is_ok' => 0, 'intro' => $projectInfo['name'] . "目标金额未达到",]);
                        // 扣除用户收益余额
                        $money = $profit['money'];
                        $rest = M('Member')->where(['id' => $profit['member_id']])->save(['profit' => ['exp', 'profit-' . $money]]);
                    }
                }else{// 钱包不够扣 从本金中扣除 后返回剩余本金
                    if($info['is_true'] == 0){
                        // 返还本金
                        $money = $info['support_money'];
                        $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money+' . $money]]);
                        if ($rest === false) {
                            M()->rollback();
                            $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                        }

                        // 向会员收益表追加一条记录
                        $rest = M('MemberProfit')->add([
                            'member_id' => $info['member_id'],
                            'money' => $money,
                            'create_time' => time(),
                            'type' => 1,
                            'remark' => $projectInfo['name'] . "影片本金返还",
                        ]);
                        if ($rest === false) {
                            M()->rollback();
                            $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                        }
                    }

                    // 减去收益  从本金从补差
                    $bucha = $count-$profitMoney;

                    // 生成补差记录
                    $rest = M('MemberProfit')->add([
                        'member_id' => $info['member_id'],
                        'money' => $bucha,
                        'create_time' => time(),
                        'type' => 4,
                        'is_ok' => 0,
                        'remark' => $projectInfo['name'] . "收益失效补差",
                    ]);
                    if ($rest === false) {
                        M()->rollback();
                        $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                    }

                    $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money-' . $bucha]]);// 减去余额钱包

                    foreach ($profits as $profit) {
                        // 修改收益状态
                        $rest = M('MemberProfit')->where(['id' => $profit['id']])->save(['is_ok' => 0, 'intro' => $projectInfo['name'] . "目标金额未达到",]);
                        // 扣除用户收益钱包
                        $money = $profit['money'];
                        $rest = M('Member')->where(['id' => $profit['member_id']])->save(['profit' => ['exp', 'profit-' . $money]]);

                    }
                }
                // 修改订单状态
                $rest = M('MemberSupport')
                    ->where(['id' => $info['id']])
                    ->save([
                        'fixed' => 0,//每天的收益
                        'is_fh' => 2,//失效订单
                      //  'is_fy' => 2,//失效订单
                        'is_true' => 1,//本金已返还
                    ]);
                if ($rest === false) {
                    M()->rollback();
                    exit;
                }
                continue;
            }
        }


        // 未分红的订单 进行分红
        $where = [
            'is_fh' => 0,//未分红的订单或者分红还未结束的订单
            'type' => 1,//未分红固定分红方式的订单
        ];
        $supportInfo = M('MemberSupport')
            ->where($where)
            ->select();
        if (!$supportInfo) {
            exit;
        }

        M()->startTrans();
        // 静态分红
        foreach ($supportInfo as &$info) {
            // 查询出当前项目
            $projectInfo = M('Project')->find($info['project_id']);
            if (!$projectInfo) {// 项目不存在
                continue;
            }

            // 判断项目在线在线状态 在线就可以进行分红  不在线查看 目标金额是否达到 未达到 则分红失败
            if ($projectInfo['is_active'] == 0 && $projectInfo['is_ok'] == 0) {

                // 当前订单用户的收益全部失效
                $profits = M('MemberProfit')->where(['support_id' => $info['id'],'type'=>1])->select();
                // 统计要扣除的收益
                $memberInfo = M('Member')->where(['id' => $info['member_id']])->find();

                // 统计收益共计金额
                $count = M('MemberProfit')->where(['support_id' => $info['id'],'type'=>1])->sum('money');


                // 当前钱包收益余额
                $profitMoney = $memberInfo['profit'];


                if($profitMoney>=$count){// 够扣，直接减去

                    $money = $info['support_money'];
                    if($info['is_true'] == 0){
                        $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money+' . $money]]);
                        if ($rest === false) {
                            M()->rollback();
                            $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                        }

                        // 向会员收益表追加一条记录
                        $rest = M('MemberProfit')->add([
                            'member_id' => $info['member_id'],
                            'money' => $money,
                            'create_time' => time(),
                            'type' => 1,
                            'remark' => $projectInfo['name'] . "影片本金返还",
                        ]);
                        if ($rest === false) {
                            M()->rollback();
                            $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                        }
                    }
                    foreach ($profits as $profit) {
                        // 修改收益状态
                        $rest = M('MemberProfit')->where(['id' => $profit['id']])->save(['is_ok' => 0, 'intro' => $projectInfo['name'] . "目标金额未达到",]);
                        // 扣除用户余额
                        $money = $profit['money'];
                        $rest = M('Member')->where(['id' => $profit['member_id']])->save(['profit' => ['exp', 'profit-' . $money]]);
                    }
                }else{// 钱包不够扣 从本金中扣除 后返回剩余本金

                    // 返还本金
                    $money = $info['support_money'];
                    if($info['is_true'] == 0){
                        $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money+' . $money]]);
                        if ($rest === false) {
                            M()->rollback();
                            $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                        }

                        // 向会员收益表追加一条记录
                        $rest = M('MemberProfit')->add([
                            'member_id' => $info['member_id'],
                            'money' => $money,
                            'create_time' => time(),
                            'type' => 1,
                            'remark' => $projectInfo['name'] . "影片本金返还",
                        ]);
                        if ($rest === false) {
                            M()->rollback();
                            $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                        }
                    }

                    // 减去收益  从本金从补差
                    $bucha = $count-$profitMoney;

                    // 生成补差记录
                    $rest = M('MemberProfit')->add([
                        'member_id' => $info['member_id'],
                        'money' => $bucha,
                        'create_time' => time(),
                        'type' => 4,
                        'is_ok' => 0,
                        'remark' => $projectInfo['name'] . "收益失效补差",
                    ]);
                    if ($rest === false) {
                        M()->rollback();
                        $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                    }

                    $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money-' . $bucha]]);// 减去余额钱包

                    foreach ($profits as $profit) {
                        // 修改收益状态
                        $rest = M('MemberProfit')->where(['id' => $profit['id']])->save(['is_ok' => 0, 'intro' => $projectInfo['name'] . "目标金额未达到",]);
                    }
                    $rest = M('Member')->where(['id' => $info['member_id']])->save(['profit' => ['exp', 'profit-' . $profitMoney]]);
                }
                // 修改订单状态
                $rest = M('MemberSupport')
                    ->where(['id' => $info['id']])
                    ->save([
                        'fixed' => 0,//每天的收益
                        'is_fh' => 2,//失效订单
                     //   'is_fy' => 2,//失效订单
                        'is_true' => 1,//本金已返还
                    ]);
                if ($rest === false) {
                    M()->rollback();
                    exit;
                }
                continue;
            }

            // 获取影片拍摄周期
            $end_time = $projectInfo['cycle'];// 周期时间 例如6个月

            $bigMony = $info['support_money']*($projectInfo['fixed_rate'] / 100)*3;


            // 获取系统当前时间
            $time = time();

            if ($end_time > 3 && $time < $projectInfo['end_time'] && $bigMony>$info['fixed']) {
                // 查看分红时间是不是已经够 3个月
                if ($info['num'] >= 90) {//不在进行分红 //修改订单状态
                    $rest = M('MemberSupport')
                        ->where(['id' => $info['id']])
                        ->save([
                            'is_fh' => 1,//分红结束
                        ]);
                    if ($rest === false) {
                        M()->rollback();
                        exit;
                    }
                    continue;
                }

                if($info['fixed']+($info['support_money'] * ($projectInfo['fixed_rate'] / 100)) / 30>$bigMony){
                    $money =$bigMony-$info['fixed'];
                }else{
                    $money =($info['support_money'] * ($projectInfo['fixed_rate'] / 100)) / 30;
                }

                $rest = M('MemberSupport')
                    ->where(['id' => $info['id']])
                    ->save([
                        'fixed' => $money + $info['fixed'],//每天的收益
                        'num' => $info['num'] + 1
                    ]);
                if ($rest === false) {
                    M()->rollback();
                    exit;
                }

                // 向会员收益表追加一条记录
                $rest = M('MemberProfit')->add([
                    'member_id' => $info['member_id'],
                    'money' => $money,
                    'create_time' => time(),
                    'type' => 1,
                    'remark' => $projectInfo['name'] . "影片每日分红",
                    'support_id' => $info['id'],
                    'is_ok' => 1,
                ]);
                if ($rest === false) {
                    M()->rollback();
                    exit;
                }
                // 更新用户收益余额
                $rest = M('Member')->where(['id' => $info['member_id']])->save(['profit' => ['exp', 'profit+' . $money]]);
                if ($rest === false) {
                    M()->rollback();
                    exit;
                }

            } elseif ($end_time <= 3 && $time < $projectInfo['end_time'] && $bigMony>$info['fixed']) {
                $rest = M('MemberSupport')
                    ->where(['id' => $info['id']])
                    ->save([
                        'fixed' => ($info['support_money'] * ($projectInfo['fixed_rate'] / 100)) / 30 + $info['fixed'],//每天的收益
                        'num' => $info['num'] + 1
                    ]);
                if ($rest === false) {
                    M()->rollback();
                    exit;
                }
                $money = ($info['support_money'] * ($projectInfo['fixed_rate'] / 100)) / 30;//每天的收益

                // 向会员收益表追加一条记录
                $rest = M('MemberProfit')->add([
                    'member_id' => $info['member_id'],
                    'money' => $money,
                    'create_time' => time(),
                    'type' => 1,
                    'remark' => $projectInfo['name'] . "影片每日分红",
                    'support_id' => $info['id'],
                    'is_ok' => 1,
                ]);
                if ($rest === false) {
                    M()->rollback();
                    exit;
                }
                $rest = M('Member')->where(['id' => $info['member_id']])->save(['profit' => ['exp', 'profit+' . $money]]);
                if ($rest === false) {
                    M()->rollback();
                    exit;
                }
            } else {
                // 修改当前订单分红状态 未分红结束
                $rest = M('MemberSupport')
                    ->where(['id' => $info['id']])
                    ->save([
                        'is_fh' => 1,//每天的收益
                    ]);
                if ($rest === false) {
                    M()->rollback();
                    exit;
                }
            }
        }
        M()->commit();
    }


    /**
     * 返还固定分红会员的本金
     */
    public function benjin(){

        // 未分红的订单
        $where = [
            'is_true'=>0,//未返还本金的
            'type' => 1,//未分红固定分红方式的订单
        ];
        $supportInfo = M('MemberSupport')
            ->where($where)
            ->select();
        if (!$supportInfo) {
            exit;
        }

        M()->startTrans();
        foreach ($supportInfo as $info) {
            $projectInfo = M('Project')->find($info['project_id']);
            if (!$projectInfo) {// 项目不存在
                continue;
            }
            // 项目下架自动返还本金
            if($projectInfo['is_active'] == 0 && $projectInfo['is_ok'] == 1){

                // 更新会员余额
                $money = $info['support_money'];
                $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money+' . $money]]);
                if ($rest === false) {
                    M()->rollback();
                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                }

                // 向会员收益表追加一条记录
                $rest = M('MemberProfit')->add([
                    'member_id' => $info['member_id'],
                    'money' => $money,
                    'create_time' => time(),
                    'type' => 1,
                    'remark' => $projectInfo['name'] . "影片本金返还",
                ]);
                if ($rest === false) {
                    M()->rollback();
                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                }
                $rest = M('MemberSupport')
                    ->where(['id' => $info['id']])
                    ->save([
                        'is_true' => 1,
                    ]);
                if ($rest === false) {
                    M()->rollback();
                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                }
            }
        }
        M()->commit();
    }



    /**
     * 出品人的会员新增业绩
     */
    public function cYj()
    {
        // 查询出等级为 出品人的会员
        $memberInfo = M('Member')->where(['role' => 4])->select();

        foreach ($memberInfo as $info) {
            $parent_id = $info['id'];
            // 根据parent_id 找下级
            $money = $this->sum($parent_id);

            if (!$money) {
                continue;
            }
            // 生成收益详情
            $rest = M('MemberProfit')->add([
                'member_id' => $info['id'],
                'money' => $money*($this->systemInfo['crate']/100),
                'create_time' => time(),
                'type' => 3,
                'remark' => "出品人新增业绩分佣",
                'is_ok' => 1,
            ]);

            // 更新佣金钱包余额
            $rest = M('Member')->where(['id' => $info['id']])->save(['commission' => ['exp', 'commission+' . $money]]);
        }
    }


    private function sum($id){

        $firstDay=date('Y-m-01', strtotime(date("Y-m-d")));

        $lastDay = date('Y-m-d', strtotime("$firstDay +1 month -1 day"));


        $lastMonthFirstDay = date('Y-m-01', strtotime('-1 month'));
        $lastMonthLastDay = date('Y-m-t', strtotime('-1 month'));

        $dataArr = $this->difference($id);

        static $nowArr = [];
        $nowSum = 0;
        static $beforeArr = [];
        $beforeSum = 0;


        foreach($dataArr as $key => $value){

            foreach($value as $k => $j){
                if(strtotime($firstDay) <= $j['create_time'] && $j['create_time'] <= strtotime($lastDay)){

                    $nowArr[] = $j;
                }
            }
        }


        foreach($dataArr as $ke => $val){

            foreach($val as $i => $o){

                if(strtotime($lastMonthFirstDay) <= $o['create_time'] && $o['create_time'] <= strtotime($lastMonthLastDay)){

                    $beforeArr[] = $o;
                }
            }
        }

        foreach($nowArr as $s => $x){
            $nowSum += $x['money'];
        }
        foreach($beforeArr as $t => $p){
            $beforeSum += $p['money'];
        }

        //>> 差值
        $difference = ($nowSum - $beforeSum) > 0 ? ($nowSum - $beforeSum):0;
        $nowSum = 0;
        $beforeSum = 0;
        return $difference;
    }

    private function difference($id,$level = 0){

        if(empty($id) || !is_numeric($id)) return false;

        static $group = [];
        $where = [
            'parent_id'=>$id
        ];
        $child = M('Member')->where($where)->select();


        if(!empty($child)){
            $level += 1;
            if($level > 3){
                $children = [];
                foreach($child as $key => $value){
                    $children = M('MemberRecharge')->where(['member_id'=>$value['id']])->select();
                }
                $group[] = $children;
            }
            foreach($child as $k => $v){
                $this->difference($v['id'],$level);
            }
        }
        return $group;
    }

}