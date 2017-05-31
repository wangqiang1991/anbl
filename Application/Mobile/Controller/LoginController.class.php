<?php
namespace Mobile\Controller;

class LoginController extends CommonController{

    /*
     * 展示登录
     */
    public function index(){

        //>> 判断是否是由链接传过来的invite_key
        $invite_key = I('get.invite_key');

        //>> 判断是否登录过了
        if($this->isLogin == 1){

            $this->redirect('Index/index');
            exit;
        }

        if(!empty($invite_key)){

            //>> 查询id作为parent_id
            $parent_id = M('Member')->where(['invite_key'=>$invite_key])->find();

            $this->assign('parent_id',$parent_id);
        }

        $this->display('login/index');
    }

    /**
     * 检测登录
     */
    public function checkLogin(){

        $paramArr = $_REQUEST;

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
            if(isset($paramArr['password']) && !empty($paramArr['password']) && strlen($paramArr['password']) <= 16){

                $res = $this->checkPassword($paramArr['password']);
                if(!$res){

                    die($this->_printError('1010'));
                }

            }else{

                die($this->_printError('1020'));
            }

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
     * 退出登录
     */
    public function logout(){
        session(md5('home'), null);
        cookie(md5('home'), null);
        $this->isLogin = 0;
        $this->redirect('login/index');
    }
}