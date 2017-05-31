<?php
namespace Admin\Controller;

use Think\Controller;

class LoginController extends CommonController{


    /**
     * 登录界面
     */
    public function index(){


        if($this->isLogin){
            $this->redirect('admin/index/index');
            exit;
        }
        $this->display('index/login');

    }

    /**
     * 登录方法
     */
    public function login(){


        $paramArr = $_REQUEST;

       if(empty($paramArr['captcha'])){
          $this->ajaxReturn(['status'=>0,'msg'=>'验证码错误']);
       }

        //>> 检测验证码
        $code = session('captcha');
        if($code != strtolower($paramArr['captcha']) ){

            $this->ajaxReturn(['status'=>0,'msg'=>'验证码错误']);
        }

        if(empty($paramArr['username']) || empty($paramArr['password'])){

            $this->ajaxReturn(['status'=>0,'msg'=>'用户名或密码不能为空']);
        }

        if(!isset($paramArr['username']) && strlen($paramArr['username']) > 16 && !isset($paramArr['password'])){

            $this->ajaxReturn(['status'=>0,'msg'=>'用户名或密码格式不正确']);
        }

        $paramArr['password'] = addslashes($paramArr['password']);
        $paramArr['username'] = addslashes($paramArr['username']);

        //>> 根据用户名取盐
        $where = [
            'username'=>$paramArr['username'],
        ];

        //>> 对比用户名的密码和盐密码
        $res = M('User')->where($where)->find();

        //>> 取出盐和盐密码
        $salt = $res['salt'];

        $saltPassword = md5($paramArr['password'].$salt);

        if($saltPassword == $res['password']){

            $token = md5(microtime().'!@#$$%^'.rand(0,1000));
            session(md5('admin'),$token);
            M('User')->where(['id'=>$res['id']])->save(['session_token'=>$token,'create_time'=>time(),'last_ip'=>get_client_ip()]);
            //>> 判断是否记住个人信息
            if(isset($paramArr['remember']) && $paramArr['remember'] == 1){

                $rememberToken = md5(microtime().rand(0,1000));

                cookie(md5('admin'),$rememberToken,time()+7*3600*24);

                //>> 将token保存到数据库
                M('User')->where(['id'=>$res['id']])->save(['remember_token'=>$rememberToken]);
            }

            $this->ajaxReturn(['status'=>1]);

        }else{

            $this->ajaxReturn(['status'=>0,'msg'=>'用户名或密码错误']);
        }
    }

    /**
     * 退出方法
     */
    public function logout(){
        session(md5('admin'), null);
        cookie(md5('admin'), null);
        $this->isLogin = 0;
        $this->redirect('admin/login/index');
    }

    /**
     * 验证码
     */
    public function captcha(){

       require_once "./ThinkPHP/Library/Vendor/Captcha/Captcha.php";

       $config = [
           'width'=>100,
           'height'=>30,
           'num'=>5
       ];

        $captcha = new \Captcha($config);

        //>> 获取产生的验证码
        $code = $captcha->getCheckCode();

        if($code){
            //>> 保存到session中
            session('captcha',strtolower($code));
            //>> 调用方法输出
            $captcha->showImage();
        }
    }

}