<?php
namespace Admin\Controller;

use Think\Page;

class OrderController extends CommonController
{
    public function _initialize(){
        parent::_initialize();
        // 检测用户是否登录，没有登录不能继续执行
        if(!$this->isLogin){
            $this->redirect('admin/login/index');
            exit;
        }
    }
    /**
     * 下载订单
     */

    public function downloadOrder(){

        $where = [];
        $order_number = I('get.order_number','','strip_tags');
        $username = I('get.username','','strip_tags');
        $name = I('get.project_name','','strip_tags');
        $start_time = strtotime(I('get.start_time'));
        $end_time = strtotime(I('get.end_time'));
        if($order_number){
            $where['a.order_number'] = ['like',"%$order_number%"];
        }
        if($username){
            $where['c.username'] = ['like',"%$username%"];
        }
        if($name){
            $where['b.name'] = ['like',"%$name%"];
        }
        if($start_time){
            $where['a.create_time'] = ['egt',$start_time];
        }
        if($start_time && $end_time ){
            $where['a.create_time'] = [
                ['egt',$start_time],
                ['elt',$end_time]
            ];
        }
        // 查询总记录数
        $count =   M('MemberDownload as a')
            ->field('a.*,b.name as project_name,c.username')
            ->join('left join an_project as b on b.id=a.project_id')
            ->join('left join an_member as c on c.id=a.member_id')
            ->where($where)
            ->count();
        // 实列化一个分页工具类
        $page = new Page($count,15);

        //查询出所有下载订单
        $rows = M('MemberDownload as a')
            ->field('a.*,b.name as project_name,c.username')
            ->join('left join an_project as b on b.id=a.project_id')
            ->join('left join an_member as c on c.id=a.member_id')
            ->where($where)
            ->order('a.create_time desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();

        // 生成分页DOM结构
        $pages = $page->show();
        // 向模板分配分页条
        $this->assign('pages',$pages);
        $this->assign('count',$count);
        $this->assign('rows',$rows);
        $this->display('order/download');
    }

    /**
     * 导出下载订单列表
     */
    public function exportData(){

        $where = [];
        $order_number = I('get.order_number','','strip_tags');
        $username = I('get.username','','strip_tags');
        $name = I('get.project_name','','strip_tags');
        $start_time = strtotime(I('get.start_time'));
        $end_time = strtotime(I('get.end_time'));
        if($order_number){
            $where['a.order_number'] = ['like',"%$order_number%"];
        }
        if($username){
            $where['c.username'] = ['like',"%$username%"];
        }
        if($name){
            $where['b.name'] = ['like',"%$name%"];
        }
        if($start_time){
            $where['a.create_time'] = ['egt',$start_time];
        }
        if($start_time && $end_time ){
            $where['a.create_time'] = [
                ['egt',$start_time],
                ['elt',$end_time]
            ];
        }

        //查询出所有下载订单
        $rows = M('MemberDownload as a')
            ->field('a.*,b.name as project_name,c.username')
            ->join('left join an_project as b on b.id=a.project_id')
            ->join('left join an_member as c on c.id=a.member_id')
            ->where($where)
            ->select();
        foreach ($rows as &$info){
            if($info['money'] == 0){
                $info['money'] = "免费下载";
            }
            $info['create_time'] = date('Y-m-d',$info['create_time']);
        }
        unset($info);

        $xlsCell  = array(
            array('id','编号'),
            array('order_number','订单号'),
            array('username','下载用户'),
            array('project_name','下载项目'),
            array('money','下载金额'),
            array('create_time','下载时间'),
        );

        $this->exportExcel(date('Y-m-d').'_下载订单',$xlsCell,$rows);

    }

    /**
     * 支持订单
     */
    public function supportOrder(){
        $where = [];
        $order_number = I('get.order_number','','strip_tags');
        $username = I('get.username','','strip_tags');
        $name = I('get.project_name','','strip_tags');
        $start_time = strtotime(I('get.start_time'));
        $end_time = strtotime(I('get.end_time'));
        $type = intval(I('get.type'));

        if($order_number){
            $where['a.order_number'] = ['like',"%$order_number%"];
        }
        if($username){
            $where['c.username'] = ['like',"%$username%"];
        }
        if($name){
            $where['b.name'] = ['like',"%$name%"];
        }
        if($type){
            $where['a.type'] = $type;
        }
        if($start_time){
            $where['a.create_time'] = ['egt',$start_time];
        }
        if($start_time && $end_time ){
            $where['a.create_time'] = [
                ['egt',$start_time],
                ['elt',$end_time]
            ];
        }
        // 查询总记录数
        $count =   M('MemberSupport as a')
            ->field('a.*,b.name as project_name,c.username')
            ->join('left join an_project as b on b.id=a.project_id')
            ->join('left join an_member as c on c.id=a.member_id')
            ->where($where)
            ->count();
        // 实列化一个分页工具类
        $page = new Page($count,15);

        //查询出所有下载订单
        $rows = M('MemberSupport as a')
            ->field('a.*,b.name as project_name,c.username')
            ->join('left join an_project as b on b.id=a.project_id')
            ->join('left join an_member as c on c.id=a.member_id')
            ->where($where)
            ->order('a.create_time desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();

        // 生成分页DOM结构
        $pages = $page->show();
        // 向模板分配分页条
        $this->assign('pages',$pages);
        $this->assign('count',$count);
        $this->assign('rows',$rows);
        $this->display('order/support');
    }

    /**
     * 导出支持订单列表
     */
    public function exportDataSupport()
    {

        $where = [];
        $order_number = I('get.order_number', '', 'strip_tags');
        $username = I('get.username', '', 'strip_tags');
        $name = I('get.project_name', '', 'strip_tags');
        $start_time = strtotime(I('get.start_time'));
        $end_time = strtotime(I('get.end_time'));
        $type = intval(I('get.type'));
        if ($order_number) {
            $where['a.order_number'] = ['like', "%$order_number%"];
        }
        if ($username) {
            $where['c.username'] = ['like', "%$username%"];
        }
        if ($name) {
            $where['b.name'] = ['like', "%$name%"];
        }
        if($type){
            $where['a.type'] = $type;
        }
        if($start_time){
            $where['a.create_time'] = ['egt',$start_time];
        }
        if($start_time && $end_time ){
            $where['a.create_time'] = [
                ['egt',$start_time],
                ['elt',$end_time]
            ];
        }

        //查询出所有下载订单
        $rows = M('MemberSupport as a')
            ->field('a.*,b.name as project_name,c.username')
            ->join('left join an_project as b on b.id=a.project_id')
            ->join('left join an_member as c on c.id=a.member_id')
            ->where($where)
            ->select();
        foreach ($rows as &$info) {
            $info['create_time'] = date('Y-m-d H:i:s', $info['create_time']);
            if($info['type'] == 1){
                $info['type'] = "月酬";
            }else{
                $info['type'] = "票房";
            }

        }
        unset($info);

        $xlsCell = array(
            array('id', '编号'),
            array('order_number', '订单号'),
            array('username', '支持用户'),
            array('type', '投资类型'),
            array('project_name', '支持项目'),
            array('support_money', '支持金额'),
            array('create_time', '支持时间'),
        );

        $this->exportExcel(date('Y-m-d') . '_支持订单', $xlsCell, $rows);
    }

    public function remove($id){
        $id = intval($id);

        // 判断是否传了ID
        if(!$id){
            // 没有ID，报错
            $this->error('没有找到数据');
            exit;
        }
        // 实例化模型类
        $model = D('MemberSupport');
        // 通过ID主键 查询标签信息
        $info = $model->find($id);


        if(!$info){
            // 没有在数据库中找到数据，报错
            $this->error('没有找到数据');
            exit;
        }
        // 查询用户
        $user = M('Member')->where(['id'=>$info['member_id']])->find();
        // 当前订单所有收益失效
        // 当前订单用户的收益全部失效
        $profits = M('MemberProfit')->where(['support_id' => $info['id']])->select();

        if(!empty($profits)){

            foreach ($profits as $profit) {
                // 扣除用户余额
                $money = $profit['money'];
                $rest = M('Member')->where(['id' => $profit['member_id']])->save(['money' => ['exp', 'money-' . $money]]);
                // 删除记录
                $rest = M('MemberProfit')->where(['id' => $profit['id']])->delete();
            }
        }

        // 返还用户投资额
        $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money+' . $info['support_money']]]);


        // 改变项目投资状态
        $rest = M('Project')->where(['id' => $info['project_id']])->save(['money' => ['exp', 'money-' . $info['support_money']]]);

        $rest = M('Project')->where(['id' => $info['project_id']])->save(['support_number' => ['exp', 'support_number-' . $rest]]);

        $insertData = [
            'admin'=>$this->userInfo['username'],
            'user'=>$user['username'],
            'type'=>'后台管理员操作',
            'event'=>'删除支持订单',
            'create_time'=>time(),
            'money'=>$info['support_money'],
            'remark'=>'无备注',
            'left_money'=>$user['money'],
        ];

        //>> 将记录写入数据库
        M('AdminLogs')->add($insertData);

        // 执行删除
        $res = $model->delete($id);



        if(!$res){
            $this->error('删除失败！');
            exit;
        }else{

            // 删除成功直接回到首页
            $this->redirect('admin/Order/supportOrder');
            exit;
        }
    }


    /**
     * 充值订单
     */
    public function recharge(){

        $paramArr = $_REQUEST;

        $where = [
            'is_pass'=>0
        ];
        if(!empty($paramArr['order_number'])){
            $where = [
                'order_number'=>$paramArr['order_number']
            ];
        }
        if(!empty($paramArr['type'])){
            $where = [
                'is_pass'=>$paramArr['type']
            ];
        }

        //>> 查询充值订单
        $orderLst = M('Member_recharge as a')->field('a.*,b.username,c.name as payname')
                ->join('left join an_member as b on a.member_id = b.id')
                ->join('left join an_pay as c on c.id=a.type')
                ->order('a.create_time desc')
                ->where($where)->select();

        if(isset($paramArr['pgNum']) && !empty($paramArr['pgNum']) && is_numeric($paramArr['pgNum'])){
            $pgNum = $paramArr['pgNum'];
        }else{
            $pgNum = 1;
        }
        if(isset($paramArr['pgSize']) && !empty($paramArr['pgSize']) && is_numeric($paramArr['pgSize'])){
            $pgSize = $paramArr['pgSize'];
        }else{
            $pgSize = 15;
        }
        $count = ceil(count($orderLst)/$pgSize);
        $orderList = $this->pagination($orderLst,$pgNum,$pgSize);

        if(IS_AJAX){
            $this->ajaxReturn([
                'data'=>array_values($orderList),
                'status'=>1,
                'count'=>$count
            ]);
        }

        $this->assign('order',$orderList);
        $this->assign('count',$count);
        $this->display('order/recharge');

    }

    /**
     * 分页
     */
    public function pagination($data = [],$phNum,$pgSize){

        if(empty($data))return false;

        $start = ($phNum - 1) * $pgSize;

        $sliceArr = array_slice($data,$start,$pgSize);

        return  $sliceArr;
    }

    /**
     * 提现订单
     */
    public function cash(){

        $where = [];
        $order_number = I('get.order_number','','strip_tags');
        $username = I('get.username','','strip_tags');
        $money = I('get.money','','strip_tags');
        $start_time = strtotime(I('get.start_time'));
        $end_time = strtotime(I('get.end_time'));
        if($order_number){
            $where['a.order_number'] = ['like',"%$order_number%"];
        }
        // 提现id
        if (I('get.id')) {
            $where[] = ['a.id' => I('get.id')];
        }
        if($username){
            $user = M('Member')->where(['username'=>$username])->find();
            $id = $user['id'];
            $where['a.member_id'] = ['like',"%$id%"];
        }
        if($money){
            $where['a.money'] = ['like',"%$money%"];
        }
        if($start_time){
            $where['a.create_time'] = ['egt',$start_time];
        }
        if($start_time && $end_time ){
            $where['a.create_time'] = [
                ['egt',$start_time],
                ['elt',$end_time]
            ];
        }

        // 查询总记录数
        $count =   M('MemberCash as a')
            ->field('a.*,b.username,b.bank_card_name,b.bank_card,b.address')
            ->join('left join an_member as b on a.member_id = b.id')
            ->where($where)
            ->count();

        // 实列化一个分页工具类
        $page = new Page($count,15);

        //查询出所有下载订单
        $rows = M('MemberCash as a')
            ->field('a.*,b.username,b.bank_card_name,b.bank_card,b.address')
            ->join('left join an_member as b on a.member_id = b.id')
            ->where($where)
            ->order('a.create_time desc')
            ->limit($page->firstRow,$page->listRows)
            ->select();

        // 生成分页DOM结构
        $pages = $page->show();
        // 向模板分配分页条
        $this->assign('pages',$pages);
        $this->assign('count',$count);
        $this->assign('order',$rows);
        $this->display('order/cash');
    }


    /**
     * 导出提现订单
     */
    public function exportDataCash(){

        $where = [];
        $order_number = I('get.order_number','','strip_tags');
        $username = I('get.username','','strip_tags');
        $money = I('get.money','','strip_tags');
        $start_time = strtotime(I('get.start_time'));
        $end_time = strtotime(I('get.end_time'));
        if($order_number){
            $where['a.order_number'] = ['like',"%$order_number%"];
        }
        if($username){
            $user = M('Member')->where(['username'=>$username])->find();
            $id = $user['id'];
            $where['a.member_id'] = ['like',"%$id%"];
        }
        if($money){
            $where['a.money'] = ['like',"%$money%"];
        }
        if($start_time){
            $where['a.create_time'] = ['egt',$start_time];
        }
        if($start_time && $end_time ){
            $where['a.create_time'] = [
                ['egt',$start_time],
                ['elt',$end_time]
            ];
        }


        //查询出所有下载订单
        $rows = M('MemberCash as a')
            ->field('a.*,b.username,b.bank_card_name,b.bank_card,b.address')
            ->join('left join an_member as b on a.member_id = b.id')
            ->where($where)
            ->select();
        foreach ($rows as &$info) {
            $info['create_time'] = date('Y-m-d', $info['create_time']);
            $info['bank_card'] = strval($info['bank_card']);
            $info['username'] = strval($info['username']);
           switch ($info['is_pass']){
               case 0:
                   $info['is_pass'] = '拒绝';
                   break;
               case 1:
               $info['is_pass'] = '通过';
               break;
               case 2:
                   $info['is_pass'] = '未审核';
                   break;

           }
        }
        unset($info);

        $xlsCell = array(
            array('id', '编号'),
            array('order_number', '订单号'),
            array('username', '提现用户'),
            array('money', '提现金额'),
            array('create_time', '提现时间'),
            array('is_pass', '状态'),
            array('bank_card', '银行卡'),
            array('bank_card_name', '开户名'),
            array('address', '开户支行'),
        );
        $this->exportExcel(date('Y-m-d') . '_提现订单', $xlsCell, $rows);
    }

    /**
     * 充值详情
     */
    public function detail(){

        $paramArr =$_REQUEST;

        $res = [];
        if(isset($paramArr['id']) && !empty($paramArr['id']) && is_numeric($paramArr['id'])){
            //>> 查询数据库
            $res = M('MemberRecharge')->where(['id'=>$paramArr['id']])->find();
            $row = M('Member')->where(['id'=>$res['member_id']])->find();
            $res['username'] = $row['username'];

            //>> 判断图片地址是否完整
            $image_url = $res['image_url'];
            $subUrl = substr($image_url,0,strpos($image_url,'%')).'%';

            if($subUrl == 'http://oomv52gxr.bkt.clouddn.com/image%'){

                //>> 截取地址
                $url = substr($image_url,0,strpos($image_url,'%')).'%25'.substr($image_url,strpos($image_url,'%')+1);
                $res['image_url'] = $url;

            }

        }

        $this->assign('detail',$res);
        $this->display('order/detail');
    }

    /**
     * 删除
     */
    public function delete(){
        $paramArr = $_REQUEST;
        if(!empty($paramArr)){
            $id = $paramArr['id'];
            $info = M('MemberCash as a')->field('a.username as aname,a.money as amoney,b.*')->join('left join an_member as b on a.member_id = b.id')->where(['id'=>$id])->find();
            $res = M('MemberCash')->where(['id'=>$id])->delete();
            if($res){
                adminLogs($info['amoney'],'后台管理员操作','删除提现订单',time(),$info['amoney'],'无备注',$info['amoney'],$this->userInfo['username']);

                $this->ajaxReturn(['status'=>1,'msg'=>'删除成功!']);
            }else{
                $this->ajaxReturn(['status'=>1,'msg'=>'删除失败!']);
            }
        }
    }

    /**
     * 通过审核
     */
    public function pass(){

        $paramArr = $_REQUEST;
        if(!empty($paramArr)){

            if(isset($paramArr['id']) && !empty($paramArr['id']) && is_numeric($paramArr['id'])){

                $row = M('MemberRecharge')->where(['id'=>$paramArr['id']])->find();

                $user = M('Member')->where(['id'=>$row['member_id']])->find();

                //>> 查询积分规则表
                $ins = M('IntegralInstitution')->select();
                $newLevel = $user['level'];
                foreach($ins as $key => $value){
                    //>> 取出当前等级下一级所对应的积分
                    if($paramArr['money'] + $user['money'] >= $value['integral'] && $user['integral'] + $paramArr['money'] >= $value['integral'] ){
                        $newLevel = $value['level'];
                    }
                }
                M('Member')->where(['id'=>$row['member_id']])->save([
                    'money'=>$user['money'] + $paramArr['money'],
                    'integral'=>$user['integral'] + $paramArr['money'],
                    'level'=>$newLevel,
                ]);

                //>> 查询数据库,审核通过，余额和积分
                $res = M('MemberRecharge')->where(['id'=>$paramArr['id']])->save([
                    'is_pass'=>1,
                ]);

                if($res){
                    adminLogs($user['username'],'后台管理员操作','用户充值通过',time(),$paramArr['money'],'无备注',$user['money'] + $paramArr['money'],$this->userInfo['username']);
                    sendSMSTemp($user['username'],'', $this->systemInfo,1775846);
                    $this->ajaxReturn(['status'=>1]);

                }else{

                    $this->ajaxReturn(['status'=>0]);
                }

            }else{

                $this->ajaxReturn(['status'=>0]);
            }
        }
    }

    /**
     * 充值订单(用tp自带分页)
     */

    public function orderRecharge(){

        $paramArr = $_REQUEST;


        $start_time = strtotime(I('get.start_time'));
        $end_time = strtotime(I('get.end_time'))+86400;
        $where = [];
       if(!empty($paramArr)){

           //>> 查询记录
           if(!empty($paramArr['order_number'])){
               $where['a.order_number'] = $paramArr['order_number'];
           }
           if(!empty($paramArr['username'])){
               $user = M('Member')->where(['username'=>$paramArr['username']])->find();
               $where['a.member_id'] = $user['id'];
           }
           if(!empty($paramArr['money'])){

               $where['a.money'] = $paramArr['money'];
           }


           if($start_time){
               $where['a.create_time'] = ['egt',$start_time];
           }
           if($start_time && $end_time ){
               $where['a.create_time'] = [
                   ['egt',$start_time],
                   ['elt',$end_time]
               ];
           }


           if(!empty($paramArr['id'])){
               $where['a.id'] = $paramArr['id'];
           }

           if (I('get.is_pass', '') || I('get.is_pass', '')==='0' ) {
           $where['a.is_pass'] = I('get.is_pass', '');
       }

       }


        $count = M('MemberRecharge as a ')
            ->field('a.*,b.username,c.name as payname')
            ->join('left join an_member as b on a.member_id = b.id')
            ->join('left join an_pay as c on c.id=a.type')
            ->where($where)
            ->count();

        // 实列化一个分页工具类
        $page = new Page($count,15);

        $rows = M('MemberRecharge as a ')->field('a.*,b.username,c.name as payname')
            ->join('left join an_member as b on a.member_id = b.id')
            ->join('left join an_pay as c on c.id=a.type')
            ->order('create_time desc')
            ->where($where)
            ->select();

        $allPassMoney = 0;
        $allRefuseMoney = 0;
        $allNotPassMoney = 0;
        $toDayRecharge = 0;
        $toDayRefuse = 0;
        $toDayPass = 0;

        //>> 对审核通过的和未通过的金额求和,2是拒绝，1是通过，0是未审核
        foreach ($rows as $key => $value){
            switch ($value['is_pass']){

                case 1:

                    $allPassMoney += $value['money'];

                    break;

                case 0:

                     $allNotPassMoney += $value['money'];

                    break;
                case 2:

                    $allRefuseMoney += $value['money'];

                    break;
            }
            if(date('Y-m-d',$value['create_time']) == date('Y-m-d',time()) && $value['is_pass'] == 1){

                $toDayPass += $value['money'];
            }
            if(date('Y-m-d',$value['create_time']) == date('Y-m-d',time()) && $value['is_pass'] == 0){

                $toDayRefuse += $value['money'];
            }
            if((date('Y-m-d',$value['create_time']) == date('Y-m-d',time())) && ($value['is_pass'] == 2)){

                $toDayRecharge += $value['money'];
            }

        }


        $rowes = M('MemberRecharge as a ')->field('a.*,b.username,c.name as payname')
            ->join('left join an_member as b on a.member_id = b.id')
            ->join('left join an_pay as c on c.id=a.type')
            ->limit($page->firstRow, $page->listRows)
            ->where($where)
            ->order('create_time desc')
            ->select();

        // 生成分页DOM结构
        $pages = $page->show();
        // 向模板分配分页条
        $this->assign([
            'allRefuseMoney'=>$allRefuseMoney,
            'allPassMoney'=>$allPassMoney,
            'allNotPassMoney'=>$allNotPassMoney,
            'toDayPass'=>$toDayPass,
            'toDayRefuse'=>$toDayRefuse,
            'toDayRecharge'=>$toDayRecharge
        ]);
        $this->assign('pages',$pages);
        $this->assign('order',$rowes);
        $this->assign('count',$count);
        $this->display('order/recharge');
    }

    /**
     * 导出支持订单列表
     */
    public function exportDataRecharge()
    {
        $paramArr = $_REQUEST;
        $where = ['1=1'];
        $order_number = I('get.order_number', '', 'strip_tags');
        $username = I('get.username', '', 'strip_tags');
        $money = I('get.money', '', 'strip_tags');
        $start_time = strtotime(I('get.start_time'));
        $end_time = strtotime(I('get.end_time'))+86400;
        if ($order_number) {
            $where['a.order_number'] = ['like', "%$order_number%"];
        }
        if ($username) {
            $where['a.username'] = ['like', "%$username%"];
        }
        if ($money) {
            $where['a.money'] = ['like', "%$money%"];
        }
        if($start_time){
            $where['a.create_time'] = ['egt',$start_time];
        }
        if($start_time && $end_time ){
            $where['a.create_time'] = [
                ['egt',$start_time],
                ['elt',$end_time]
            ];
        }
        if(strlen($paramArr['is_pass'])){
            if($where['a.is_pass'] == 0){
                $where['a.is_pass'] =$paramArr['is_pass'];
            }
        }


        //查询出所有下载订单
        $rows = M('MemberRecharge as a')
            ->field('a.*,b.username')
            ->join('left join an_member as b on a.member_id = b.id')
            ->where($where)
            ->select();
        foreach ($rows as &$info) {
            $info['create_time'] = date('Y-m-d H:i:s', $info['create_time']);
            if($info['is_pass'] == 0){
                $info['is_pass'] = "未审核";
            }
            if($info['is_pass'] == 1){
                $info['is_pass'] = "已通过";
            }
            if($info['is_pass'] == 2){
                $info['is_pass'] = "已拒绝";
            }
        }
        unset($info);

        $xlsCell = array(
            array('id', '编号'),
            array('order_number', '订单号'),
            array('username', '充值用户'),
            array('money', '充值金额'),
            array('create_time', '支持时间'),
            array('is_pass', '状态'),
        );
        $this->exportExcel(date('Y-m-d') . '_充值订单', $xlsCell, $rows);
    }
    /**
     * 提现通过
     */
    public function cashPass(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){

            $res = M('MemberCash')->where(['id'=>$paramArr['id']])->save(['is_pass'=>1]);

            //>> 查询当前用户
            $user = M('Member')->where(['id'=>$res['member_id']])->find();
            adminLogs($user['username'],'后台管理员操作','允许用户提现',time(),$res['money'],'无备注',$user['money'],$this->userInfo['username']);

          if($res != false){

              $this->ajaxReturn([
                  'status'=>1
              ]);
          }else{
                $this->ajaxReturn([
                    'status'=>0
                ]);
            }
        }else{

            $this->ajaxReturn([
                'status'=>0
            ]);
        }
    }

    /**
     * 拒绝提现
     */
    public function cashRefuse(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){
            M('MemberCash')->startTrans();
            $res = M('MemberCash')->where(['id'=>$paramArr['id']])->save(['is_pass'=>2]);

            //>> 查询用户id
            $casher = M('MemberCash')->where(['id'=>$paramArr['id']])->find();
            $userId = $casher['member_id'];
            $type = $casher['type'];

            $user = M('Member')->where(['id'=>$userId])->find();

            switch($type){
                case '余额提现':
                    //>>将用户的余额重新恢复
                    $updateData = [
                        'money'=>$paramArr['money'] + $user['money']
                    ];
                    $re = M('Member')->where(['id'=>$userId])->save($updateData);
                    if($res && $re){
                        M('MemberCash')->commit();
                        $this->ajaxReturn([
                            'status'=>1
                        ]);
                    }else{
                        $this->ajaxReturn([
                            'status'=>0
                        ]);
                    }
                    break;
                case '收益提现':
                    //>>将用户的余额重新恢复
                    $updateData = [
                        'profit'=>$paramArr['money'] + $user['profit']
                    ];
                    $re = M('Member')->where(['id'=>$userId])->save($updateData);
                    if($res && $re){
                        M('MemberCash')->commit();
                        $this->ajaxReturn([
                            'status'=>1
                        ]);
                    }else{
                        $this->ajaxReturn([
                            'status'=>0
                        ]);
                    }
                    break;
                case '佣金提现':
                    //>>将用户的余额重新恢复
                    $updateData = [
                        'commission'=>$paramArr['money'] + $user['commission']
                    ];
                    $re = M('Member')->where(['id'=>$userId])->save($updateData);
                    if($res && $re){
                        M('MemberCash')->commit();
                        $this->ajaxReturn([
                            'status'=>1
                        ]);
                    }else{
                        $this->ajaxReturn([
                            'status'=>0
                        ]);
                    }
                    break;
            }
            adminLogs($user['username'],'后台管理员操作','拒绝用户提现',time(),$paramArr['money'],'无备注',$user['money'],$this->userInfo['username']);
            $this->ajaxReturn([
                'status'=>1
            ]);
        }else{
            $this->ajaxReturn([
                'status'=>0
            ]);
        }
    }

    /**
     * 拒绝充值
     */
    public function sorryy(){

        $paramArr = $_REQUEST;


        $member_id = $paramArr['id'];
        $user = M('Member')->where(['id'=>$member_id])->find();

        $id = $paramArr['oId'];

        $res = M('MemberRecharge')->where(['member_id'=>$member_id,'id'=>$id])->save(['is_pass'=>2]);
        $info = M('MemberRecharge')->where(['member_id'=>$member_id,'id'=>$id])->find();
        if($res === false){

            $this->ajaxReturn(['status'=>0]);
        }else{
            //>> 发短信,将拒绝的理由保存到数据库
            M('MemberRecharge')->where(['member_id'=>$member_id,'id'=>$id,'is_pass'=>2])->save(['remark'=>$paramArr['text']]);

            adminLogs($user['username'],'后台管理员操作','拒绝用户充值',time(),$info['money'],$paramArr['text'],$user['money'],$this->userInfo['username']);

            sendSMSTemp($user['username'], ('#phone#') . "=" . urlencode($paramArr['text']), $this->systemInfo,1775922);
            $this->ajaxReturn(['status'=>1]);
        }
    }

    public function removereg($id){
        $id = intval($id);
        // 判断是否传了ID
        if(!$id){
            // 没有ID，报错
            $this->error('没有找到数据');
            exit;
        }
        // 实例化模型类
        $model = D('MemberRecharge');
        // 通过ID主键 查询标签信息
        $info = $model->find($id);

        if(!$info){
            // 没有在数据库中找到数据，报错
            $this->error('没有找到数据');
            exit;
        }
        // 扣除充值
        $rest = M('Member')->where(['id' => $info['member_id']])->save(['money' => ['exp', 'money-' . $info['money']]]);
        $user = M('Member')->where(['id' => $info['member_id']])->find();
        if(!$rest){
            $this->error('删除失败！');
            exit;
        }

        // 执行删除
        $res = $model->delete($id);
        if(!$res){
            $this->error('删除失败！');
            exit;
        }
        adminLogs($user['username'],'后台管理员操作','删除充值订单',time(),$info['money'],'无备注',$user['money'],$this->userInfo['username']);
        // 删除成功直接回到首页
        $this->redirect('admin/order/orderRecharge');
        exit;
    }

}
