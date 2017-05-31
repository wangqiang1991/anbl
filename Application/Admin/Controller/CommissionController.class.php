<?php
namespace Admin\Controller;

class CommissionController extends CommonController
{

    /**
     * 分发支持佣金,只看投资额
     */
    public function getYj()
    {
            $where = [
                'is_fy' => 1,//已分佣的订单
            ];
            // 查询出订单信息
            $supportInfo = M('MemberSupport')->where($where)->select();
           // 遍历订单
            foreach($supportInfo as $info){
                // 订单投资项目
                $projectInfo = M('Project')->find($info['project_id']);
                if (!$projectInfo) {// 项目不存在
                    continue;
                }
                // 项目项目下架，目标金额未达到 当前订单所有收益失效
                if ($projectInfo['is_active'] == 0 && $projectInfo['is_ok'] == 0) {

                    // 当前订单用户的佣金收益全部失效
                    $profits = M('MemberProfit')->where(['support_id' => $info['id'],'type'=>2])->select();

                    foreach ($profits as $profit) {
                        // 修改收益状态
                        $rest = M('MemberProfit')->where(['id' => $profit['id']])->save(['is_ok' => 0, 'intro' => $projectInfo['name'] . "目标金额未达到",]);
                        // 扣除用户佣金钱包余额
                        $money = $profit['money'];
                        // 获取会员信息
                        $memberInfos=M('Member')->where(['id' => $profit['member_id']])->find();
                        // 获取会员当前佣金钱包金额
                        $commissionMoney = $memberInfos['commission'];

                        if($commissionMoney>=$money){//佣金钱包够扣，直接扣除

                            $rest = M('Member')->where(['id' => $profit['member_id']])->save(['commission' => ['exp', 'commission-' . $money]]);

                        }else{//佣金钱包不够扣，从会员余额钱包补差

                            $bucha = $money-$commissionMoney;// 待补差金额balance

                            // 获取用户当前余额钱包金额
                            $balance = $memberInfos['money'];
                            if($balance>=$bucha){//钱包余额够扣除补差金额

                                // 减去余额钱包补差金额
                                $rest = M('Member')->where(['id' => $memberInfos['id']])->save(['money' => ['exp', 'money-' . $bucha]]);
                                // 生成补差记录
                                $rest = M('MemberProfit')->add([
                                    'member_id' => $memberInfos['id'],
                                    'money' => $bucha,
                                    'create_time' => time(),
                                    'type' => 4,
                                    'is_ok' => 0,
                                    'remark' => $projectInfo['name'] . "佣金失效补差",
                                ]);
                                if ($rest === false) {
                                    M()->rollback();
                                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                                }

                                $rest = M('Member')->where(['id' => $memberInfos['id']])->save(['commission' => ['exp', 'commission-' . $commissionMoney]]);


                            }else{//钱包余额不够扣除补差金额
                                $rest = M('Member')->where(['id' => $memberInfos['id']])->save(['money' => ['exp', 'money-' . $balance]]);
                                // 生成补差记录
                                $rest = M('MemberProfit')->add([
                                    'member_id' => $memberInfos['id'],
                                    'money' => $balance,
                                    'create_time' => time(),
                                    'type' => 4,
                                    'is_ok' => 0,
                                    'remark' => $projectInfo['name'] . "佣金失效补差",
                                ]);
                                if ($rest === false) {
                                    M()->rollback();
                                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                                }

                                $realMoney = $money-$balance;
                                $rest = M('Member')->where(['id' => $memberInfos['id']])->save(['commission' => ['exp', 'commission-' . $realMoney]]);

                            }
                        }
                    }
                    // 修改订单状态
                    $rest = M('MemberSupport')
                        ->where(['id' => $info['id']])
                        ->save([
                            'fixed' => 0,//每天的收益
                          //  'is_fh' => 2,//失效订单
                            'is_fy' => 2,//失效订单
                        ]);
                    if ($rest === false) {
                        M()->rollback();
                        exit;
                    }
                    continue;
                }
            }

            //查询出所有支持订单且状态为未分佣的订单
            $where = [
                'is_fy' => 0,//未分佣的订单
            ];

            $supportInfo = M('MemberSupport')->where($where)->select();


            if (!$supportInfo) {
                $this->ajaxReturn(['msg' => "该项目没有未分佣的订单", 'status' => 0]);
            }


            // 开启事物
            M()->startTrans();

            foreach ($supportInfo as $info) {//拿到每一笔支持订单
                $projectInfo = M('Project')->find($info['project_id']);
                if (!$projectInfo) {// 项目不存在
                    continue;
                }

                if ($projectInfo['is_active'] == 0 && $projectInfo['is_ok'] == 0) {

                    // 当前订单用户的收益全部失效
                    $profits = M('MemberProfit')->where(['support_id' => $info['id'],'type'=>2])->select();
                    foreach ($profits as $profit) {
                        // 修改收益状态
                        $rest = M('MemberProfit')->where(['id' => $profit['id']])->save(['is_ok' => 0, 'intro' => $projectInfo['name'] . "目标金额未达到",]);
                        // 扣除用户佣金钱包余额
                        $money = $profit['money'];
                        // 获取会员信息
                        $memberInfos=M('Member')->where(['id' => $profit['member_id']])->find();
                        // 获取会员当前佣金钱包金额
                        $commissionMoney = $memberInfos['commission'];

                        if($commissionMoney>=$money){//佣金钱包够扣，直接扣除

                            $rest = M('Member')->where(['id' => $profit['member_id']])->save(['commission' => ['exp', 'commission-' . $money]]);

                        }else{//佣金钱包不够扣，从会员余额钱包补差

                            $bucha = $money-$commissionMoney;// 待补差金额balance

                            // 获取用户当前余额钱包金额
                            $balance = $memberInfos['money'];
                            if($balance>=$bucha){//钱包余额够扣除补差金额

                                // 减去余额钱包补差金额
                                $rest = M('Member')->where(['id' => $memberInfos['id']])->save(['money' => ['exp', 'money-' . $bucha]]);
                                // 生成补差记录
                                $rest = M('MemberProfit')->add([
                                    'member_id' => $memberInfos['id'],
                                    'money' => $bucha,
                                    'create_time' => time(),
                                    'type' => 4,
                                    'is_ok' => 0,
                                    'remark' => $projectInfo['name'] . "佣金失效补差",
                                ]);
                                if ($rest === false) {
                                    M()->rollback();
                                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                                }

                                $rest = M('Member')->where(['id' => $memberInfos['id']])->save(['commission' => ['exp', 'commission-' . $commissionMoney]]);


                            }else{//钱包余额不够扣除补差金额
                                $rest = M('Member')->where(['id' => $memberInfos['id']])->save(['money' => ['exp', 'money-' . $balance]]);
                                // 生成补差记录
                                $rest = M('MemberProfit')->add([
                                    'member_id' => $memberInfos['id'],
                                    'money' => $balance,
                                    'create_time' => time(),
                                    'type' => 4,
                                    'is_ok' => 0,
                                    'remark' => $projectInfo['name'] . "佣金失效补差",
                                ]);
                                if ($rest === false) {
                                    M()->rollback();
                                    $this->ajaxReturn(['msg' => "返还失败", 'status' => 0]);
                                }

                                $realMoney = $money-$balance;
                                $rest = M('Member')->where(['id' => $memberInfos['id']])->save(['commission' => ['exp', 'commission-' . $realMoney]]);

                            }
                        }
                    }
                    // 修改订单状态
                    $rest = M('MemberSupport')
                        ->where(['id' => $info['id']])
                        ->save([
                            'fixed' => 0,//每天的收益
                           // 'is_fh' => 2,//失效订单
                            'is_fy' => 2,//失效订单
                        ]);
                    if ($rest === false) {
                        M()->rollback();
                        exit;
                    }
                    continue;
                }

                //接受请求参数
                $member_id = $info['member_id'];//当前订单会员id
                // 查询出当前会员信息
                $memberInfo = M('Member')->find($member_id);

                // 查询投资项目
                $projectInfo = M('Project')->where(['id'=>$info['project_id']])->find();


                //进行分佣
                $this->genCommission($info, $projectInfo, $memberInfo['parent_id'], 1);
            }
            // 提交事物
            M()->commit();
            $this->ajaxReturn(['msg' => "分佣成功", 'status' => 1]);

    }

    /**
     * @param $info 当前订单信息
     * @param $projectInfox 当前项目信息
     * @param $parent_id 父级id
     * @param $level 级别
     */
    protected function genCommission($info, $projectInfox, $parent_id, $level)
    {

        if ($parent_id == 0) {
            $rest = M('MemberSupport')
                ->where(['id' => $info['id']])
                ->save([
                    'is_fy' => 1,
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
                    break;
                }
                if ($level == 1) {//第一父
                    // 计算佣金
                    $commission = $info['support_money'] * ($projectInfo['first_rate'] / 100);//一代投资额的5%  一代票房收益的5%
                    // 操作数据库
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                break;

            case "2":
                if ($level > 2) {
                    break;
                }
                if ($level == 1) {//第一父
                    // 计算佣金
                    $commission = $info['support_money'] * ($projectInfo['first_rate'] / 100);// 5%


                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                if ($level == 2) {//第二父
                    $commission = $info['support_money'] * ($projectInfo['two_rate'] / 100);//  3%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }

                break;

            case "3"://制片人
                //计算票房收益
                if ($level > 3) {
                    break;
                }
                if ($level == 1) {
                    // 计算佣金
                    $commission = $info['support_money'] * ($projectInfo['first_rate'] / 100);// 5%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);

                }
                if ($level == 2) {

                    $commission = $info['support_money'] * ($projectInfo['two_rate'] / 100);// 3%

                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                if ($level == 3) {
                    $commission = $info['support_money'] * ($projectInfo['three_rate'] / 100);// 1%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }

                break;

            case "4"://出品人
                if ($level > 3) {
                    break;
                }
                if ($level == 1) {
                    // 计算佣金
                    $commission = $info['support_money'] * ($projectInfo['first_rate'] / 100);// 5%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                if ($level == 2) {
                    $commission = $info['support_money'] * ($projectInfo['two_rate'] / 100);// 3%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                if ($level == 3) {
                    $commission = $info['support_money'] * ($projectInfo['three_rate'] / 100);// 1%
                    $this->insertDb($info, $commission, $parent, $projectInfo['name']);
                }
                break;

        }
        if($level>3){
            return;
        }
        $this->genCommission($info,$projectInfo, $parent['parent_id'], $level + 1);
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
                'is_fy' => 1,
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
            'remark' => $projectName . "影片分佣",
        ]);
        if ($rest === false) {
            M()->rollback();
            $this->ajaxReturn(['msg' => "分佣失败", 'status' => 0]);
        }
    }

    /**
     * 制片人的会员新增业绩分佣
     */
    public function zYj()
    {
        // 查询出等级为 制片人的会员
        $memberInfos = M('Member')->where(['role' => 3])->select();


        foreach ($memberInfos as $info) {
            $parent_id = $info['id'];
            // 根据parent_id 找下级
            $money = $this->sum($parent_id);
            if (!$money) {
                continue;
            }
            // 生成收益详情
            $rest = M('MemberProfit')->add([
                'member_id' => $info['id'],
                'money' => $money*($this->systemInfo['zrate']/100),
                'create_time' => time(),
                'type' => 3,
                'remark' => "制片人新增业绩分佣",
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