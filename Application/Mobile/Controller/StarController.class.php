<?php
namespace Mobile\Controller;

class StarController extends CommonController{

    public function index(){

        if($this->isLogin == 0){
            $this->redirect('login/index');
            exit;
        }

        //>> 查询招募电影
        $recruit = M('ProjectRecruit')->select();
        $this->assign('recruit',$recruit);
        $this->display('star/index');
    }

    /**
     * 展示详情
     */
    public function starInfo(){

        //>> 根据id查询电影详情和角色详情
        $paramArr = $_REQUEST;
        if(!empty($paramArr)){

            $movie = M('ProjectRecruit')->where(['id'=>$paramArr['id']])->find();
            //>> 取出角色id
            $roleId = json_decode($movie['role_id']);
            //>> 查询角色
           $roles = [];
            foreach($roleId as $key => $value){
                session(md5('roleId'),$roleId[0]);
                $row = M('ProjectRole')->where(['id'=>$value])->find();
                $roles[$key] = $row;
            }
            //>> 根据电影id和角色id查询第一个角色的描述详情
            $roleInfo = M('RoleDescription')->where(['recruit_id'=>$paramArr['id'],'role_id'=>$roleId[0]])->find();

            $this->assign('movie',$movie);
            $this->assign('userInfo',$this->userInfo);
            $this->assign('roles',$roles);
            $this->assign('roleInfo',$roleInfo);
            $this->assign('movieId',$paramArr['id']);
        }
        $this->display('star/info');
    }

    /**
     * 角色选择
     */
    public function roleChange(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){

            //>> 查询角色信息
            $roleInfo = M('RoleDescription')->where(['role_id'=>$paramArr['roleId'],'recruit_id'=>$paramArr['movieId']])->find();

            if(!empty($roleInfo)){
                session(md5('roleId'),$roleInfo['id']);
                $this->ajaxReturn(['msg'=>'请求成功','status'=>1,'data'=>$roleInfo]);
            }else{

                $this->ajaxReturn(['msg'=>'数据为空','status'=>0]);
            }
        }else{

            $this->ajaxReturn(['msg'=>'请求失败','status'=>0]);
        }
    }

    /**
     * 角色申请
     */
    public function application(){

        $roleId = session(md5('roleId'));
        $movieId = $_REQUEST['id'];
        $this->assign(['roleId'=>$roleId,'movieId'=>$movieId]);
        $this->display('star/application');
    }

    /**
     * 保存申请
     */
    public function starAdd(){

        $paramArr = $_REQUEST;

        //>> 查询余额
        $row  = M('Member')->where(['id'=>$this->userInfo['id']])->find();

        //>> 根据电影id和角色id查询需要消耗的阿纳豆
        $needMoney = M('RoleDescription')->where(['recruit_id'=>$paramArr['movieId'],'role_id'=>$paramArr['roleId']])->find();
        $money = $row['money'];
        if($money < $needMoney['money']){

            die($this->_printError('1064'));
        }

        if(!empty($paramArr)){
            M()->startTrans();
            $insertData = [
                'name'=>$paramArr['realname'],
                'sex'=>$paramArr['sex'],
                'volk'=>$paramArr['volk'],
                'birthday'=>$paramArr['birthday'],
                'height'=>$paramArr['height'],
                'id_card'=>$paramArr['id_card'] ? $paramArr['id_card'] : 0,
                'phone'=>$paramArr['phone'],
                'email'=>$paramArr['email'],
                'address'=>$paramArr['address'],
                'skill'=>$paramArr['skill'],
                'expirence'=>$paramArr['expirence'],
                'image_url'=>$paramArr['image_url'] ? $paramArr['image_url'] : '',
                'member_id'=>$this->userInfo['id'],
                'project_id'=>$paramArr['movieId'],
                'role_id'=>$paramArr['roleId'],
                'is_pass'=>2,
                'create_time'=>time(),
            ];
            $res = M('MemberStar')->add($insertData);
            $re = M('Member')->where(['id'=>$this->userInfo['id']])->save(['money' => ['exp', 'money-' .$needMoney['money']]]);
            if($res && $re){
                M()->commit();
                die($this->_printSuccess());
            }else{

                M()->rollback();
                die($this->_printError('1000'));
            }
        }
    }
}