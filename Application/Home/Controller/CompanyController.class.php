<?php
namespace Home\Controller;

class CompanyController extends CommonController
{
    public function index(){

        //>> 判断是否登录
        if($this->isLogin == 0){

            $this->redirect('Home/login/index');
            exit;
        }

        // 查询出合作机构
        $cooper = M('Cooper')->select();
        // 分配合作机构数据
        $this->assign('coopers',$cooper);
        $this->display('company/index');
    }
    public function about(){
        //>> 判断是否登录
        if($this->isLogin == 0){

            $this->redirect('Home/login/index');
            exit;
        }

        $this->display('company/about');
    }
}