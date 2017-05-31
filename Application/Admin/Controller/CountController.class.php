<?php
namespace Admin\Controller;

use Think\Page;

class CountController extends CommonController
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
     * 影片统计
     */
    public function projectCount(){
        // 统计上架影片数量
        $projectNum = M('Project')->where(['is_active'])->count();
        $this->assign('projectNum', $projectNum);

        // 统计影片总数
        $num = M('Project')->count();
        $this->assign('num', $num);

        // 查询影片信息
        //查看是否有查询
        $name = I('get.name','','strip_tags');

        // 创建查询条件
        $where =[];
        if($name){
            $where['name'] = ['like',"%$name%"];
        }

        // 查询总记录数
        $count =  M('project')->where($where)->count();

        // 实列化一个分页工具类
        $page = new Page($count,5);

        $rows = M('project')
            ->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->order('id')
            ->select();
        // 生成分页DOM结构
        $pages = $page->show();
        // 向模板分配分页条
        $this->assign('pages',$pages);
        $this->assign('projectInfos',$rows);

        $this->display('count/project');
    }

    /**
     * 销售数据统计
     */
    public function payCount(){

        // 会员累计下载金额
        $dlMoney = M('MemberDownload')->sum('money');
        $this->assign('dlMoney',$dlMoney);

        // 会员累计支持金额
        $spMoneyInfo = M('MemberSupport')->select();
        $spMoney = 0;
        foreach ($spMoneyInfo as $value){
            $projectInfo = M('Project')->where(['id'=>$value['project_id']])->find();
            if($projectInfo['is_active'] == 1 || ($projectInfo['is_active'] == 0  && $projectInfo['is_ok'] == 1)){

                $spMoney += $value['support_money'];
            }

        }
        $this->assign('spMoney',$spMoney);

       //查看是否有查询
        $name = I('get.name','','strip_tags');
        $start_time = strtotime(I('get.start_time'));
        $end_time = strtotime(I('get.end_time'));
        // 创建查询条件
        $where =[];
        if($name){
            $where['b.name'] = ['like',"%$name%"];
        }

        if($start_time){
            $where['a.create_time'] = ['egt',$start_time];
        }
        if($end_time){
            $where['a.create_time'] = ['elt',$end_time];
        }

        // 统计具体项目的累计支持金额
        $support = M('MemberSupport as a')
            ->field('sum(a.support_money) as money,a.id,b.name,b.image_url')
            ->join('left join an_project as b on b.id=a.project_id')
            ->where($where)
            ->group('a.project_id')
            ->select();
        $this->assign('support',$support);

        // 统计具体项目的累计下载金额
        $download = M('MemberDownload as a')
            ->field('sum(a.money) as money,a.id,b.name,b.image_url')
            ->join('left join an_project as b on b.id=a.project_id')
            ->where($where)
            ->group('a.project_id')
            ->select();
        $this->assign('download',$download);

        $this->display('count/pay');
    }


    /**
     * 财务数据统计
     */
    public function financeCount(){
        //查看是否有查询
        $start_time = strtotime(I('get.start_time'));//统计开始时间
        $end_time = strtotime(I('get.end_time'));//统计结束时间
        // 创建查询条件
        $where =[];
        if($start_time){
            $where['create_time'] = ['egt',$start_time];
        }
        if($start_time && $end_time ){
            $where['create_time'] = [
                ['egt',$start_time],
                ['elt',$end_time]
            ];
        }

        //统计会员充值金额
        $amoney = M('MemberRecharge')->where(array_merge($where,['is_pass'=>1]))->sum('money');
        $this->assign('amoney',$amoney);
        //统计会员提现金额
        $bmoney = M('MemberCash')->where(array_merge($where,['is_pass'=>1]))->sum('money');
        $this->assign('bmoney',$bmoney);
        //统计会员分红金额
        $cmoneyInfo = M('MemberProfit')->where(array_merge($where,['type'=>1],['is_ok'=>1]))->select();
        $cmoney = 0;
        if($cmoneyInfo){
            foreach($cmoneyInfo as $info){
                if(!empty($info['support_id'])){
                    $cmoney+=$info['money'];
                }
            }
        }

        $this->assign('cmoney',$cmoney);
        //统计会员分佣金额
        $dmoneyInfo = M('MemberProfit')->where(array_merge($where,['type'=>2],['is_ok'=>1]))->select();
        $dmoney = 0;
        if($dmoneyInfo){
            foreach($dmoneyInfo as $info){
                if(!empty($info['support_id'])){
                    $dmoney+=$info['money'];
                }
            }
        }

        $this->assign('dmoney',$dmoney);

        $this->display('count/finance');
    }

    /**
     * 会员数据统计
     */
    public function memberCount(){

        $paramArr = $_REQUEST;
        $where = [];
        if($paramArr['username']){
            $where['username'] = $paramArr['username'];
        }
        if($paramArr['start_time']){
            $where['create_time'] = ['egt',strtotime($paramArr['start_time'])];
        }
        if($paramArr['end_time']){
            $where['create_time'] = ['elt',strtotime($paramArr['end_time'])];
        }

        if($paramArr['start_time'] && $paramArr['end_time']){
            $where['create_time'] = [['egt',strtotime($paramArr['start_time'])],['elt',strtotime($paramArr['end_time'])]];
        }

        //>> 查询所有会员
        $members = M('Member')->where($where)->select();

        $count = count($members);
        $all = M('Member')->where($where)->sum('money');
        foreach($members as $key => &$value){
            //>> 收藏电影
            $films = M('MemberCollection')->where(['member_id'=>$value['id']])->count();

                $value['project'] = $films ? $films : 0;

            //>> 投资金额
            $money = M('MemberSupport')->where(['member_id'=>$value['id']])->sum('support_money');

                $value['support'] = $money ? $money : 0;

            //>> 发展人数
            $follower = M('Member')->where(['parent_id'=>$value['id']])->count();

                $value['follower'] = $follower ? $follower : 0;

            //>> 下载电影
            $downLoad = M('MemberDownload')->where(['member_id'=>$value['id']])->count();
            $downLoadMoney = M('MemberDownload')->where(['member_id'=>$value['id']])->sum('money');
            $value['downLoad'] = $downLoad ? $downLoad : 0;
            $value['downLoadMoney'] = $downLoadMoney ? $downLoadMoney : 0;
        }
       $this->assign('members',$members);
       $this->assign('count',$count);
       $this->assign('all',$all);
        $this->display('count/member');
    }

}