<?php
namespace Admin\Controller;

use Think\Controller;

class IndexController extends CommonController
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
     * 后台首页
     */
    public function index()
    {
        // 获取项目数量
        $projectNum = M('Project')->count();
        $this->assign('projectNum', $projectNum);

        // 获取本月会员支持总额
        $projectNewNum = M('MemberSupport')
            ->where(['create_time' => ['egt',date('Y-m-1 00:00:00')]])
            ->sum('support_money');
        $this->assign('projectNewNum', $projectNewNum);

        // 获取会员数量
        $userNum = M('Member')->count();
        $this->assign('userNum', $userNum);
        $this->display('index/index');
    }
}