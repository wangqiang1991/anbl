<?php
namespace Home\Controller;

use Think\Controller;

class LoginController extends CommonController{

    /**
     * 登录界面
     */
    public function index(){

        //>> 判断是否登录过了
        if($this->isLogin == 1){

            $this->redirect('home/index/index');
        }

        $this->display('login/index');
    }

    /**
     * 检测登录
     */
    public function login(){

        $paramArr = $_REQUEST;


        foreach ($paramArr as &$value){

            //>> 对特殊字符加转义
            $value = addslashes($value);

            //>> 对html标签加过滤
            $value = htmlspecialchars($value);
        }
        unset($value);

        if(!empty($paramArr)){

            $userModel = M('Member');
            if(isset($paramArr['username']) && !empty($paramArr['username']) && is_numeric($paramArr['username'])){



                //>> 判断用户名
                $res = $this->checkPhone($paramArr['username']);
                if(!$res){

                    die($this->_printError('1022'));
                }

            }else{

                die($this->_printError('1018'));
            }

            //>>检测密码
//            if(isset($paramArr['password']) && !empty($paramArr['password']) && strlen($paramArr['password']) <= 16){
//
//                $res = $this->checkPassword($paramArr['password']);
//                if(!$res){
//
//                    die($this->_printError('1010'));
//                }
//
//            }else{
//
//                die($this->_printError('1020'));
//            }

            //>> 查询数据库
            $row = $userModel->where(['username'=>$paramArr['username'],'password'=>md5($paramArr['password'])])->find();

            if(!empty($row)){
                //>> 生成token
                $session_token = md5(microtime().'@#$%^&*('.rand(0,9999));
                session(md5('home'),$session_token);
                //>> 将token保存到数据库
                M('Member')->where(['username'=>$paramArr['username'],'password'=>md5($paramArr['password'])])->save([
                    'session_token'=>$session_token
                ]);
                //>> 跳转到首页
                die($this->_printSuccess());
            }else{

                die($this->_printError('1016'));
            }
        }else{

            die($this->_printError('1000'));
        }
    }

    /**
     * 检测用户名
     */
    private function checkPhone($phone){

        if(empty($phone)) return false;

        $reg = '/^0?(13|14|15|17|18)[0-9]{9}$/';

        preg_match_all($reg,$phone,$str);

        if($str){

            return true;

        }else{
            return false;

        }

    }
    /**
     * 检测密码
     */
    private function checkPassword($password){

        return true;

        if(empty($password)){

            return false;

        }else{
            //>> 检测密码
            if(isset($password) && strlen($password) < 16){

                //>> 密码由字母、数字、下划线组成，5-16位
                $reg = '/^(?![0-9]+$)(?![a-zA-Z]+$)[0-9A-Za-z]{8,16}$/';

                preg_match_all($reg,$password,$str);

                if(!empty($str)){

                    return true;

                }else{

                    return false;
                }
            }

        }

    }

    /**
     * 忘记密码
     */
    public function forget(){
        if(IS_POST){
            $paramArr = $_REQUEST;
            if(!empty($paramArr)){
                if(isset($paramArr['phone']) && !empty($paramArr['phone']) && is_numeric($paramArr['phone'])){
                    //>> 检测用户名
                    $res = $this->checkPhone($paramArr['phone']);
                    //>> 检测验证码
                    $captcha = session('verify_code'.$paramArr['phone']);
                    if($captcha != $paramArr['captcha']){
                        die($this->_printError('1008'));
                    }
                    if($res){
                        if(isset($paramArr['password']) && !empty($paramArr['password'])){
                            //>> 检测密码
                            $_res = $this->checkPassword($paramArr['password']);

                            if($_res){
                                //>> 检测两次密码是否一致
                                if(isset($paramArr['repassword']) && !empty($paramArr['repassword'])){
                                   $result = $paramArr['password'] == $paramArr['repassword'] ? true : false;
                                    if($result){
                                        $user = M('Member')->where(['username'=>$paramArr['phone']])->find();

                                        if(!empty($user)){
                                            //>> 查询数据库
                                            $data = [
                                                'password'=>md5($paramArr['password']),
                                            ];
                                            $res = M('Member')->where(['username'=>$paramArr['phone']])->save($data);
                                            if($res === false){
                                                die($this->_printError('1030'));

                                            }else{
                                                die($this->_printSuccess());
                                            }
                                        }else{
                                            die($this->_printError('1032'));
                                        }
                                    }else{
                                        die($this->_printError('1028'));
                                    }
                                }else{
                                    die($this->_printError('1026'));
                                }
                            }else{
                                die($this->_printError('1024'));
                            }
                        }else{
                            die($this->_printError('1020'));
                        }
                    }else{
                        die($this->_printError('1022'));
                    }
                }else{
                    die($this->_printError('1018'));
                }

            }else{
                die($this->_printError('1000'));
            }
        }
        $this->display('login/forget');
    }

    /**
     * 退出登录
     */
    public function logout(){
        session(md5('home'), null);
        cookie(md5('home'), null);
        $this->isLogin = 0;
        $this->redirect('home/login/index');
    }
}