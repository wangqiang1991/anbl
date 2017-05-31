<?php
namespace Home\Controller;

use Think\Controller;

class CommonController extends Controller{

    //记录用户登录状态
    public $isLogin = 0;

    //当前用户信息
    public $userInfo = [];

    protected $_msgArr = [

        '1000'=>['用户信息不能为空!','用户信息不能为空!'],
        '1002'=>['短信发送失败!','短信发送失败!'],
        '1004'=>['请等待60秒后再发送验证码!','请等待60秒后再发送验证码!'],
        '1006'=>['手机号码格式不正确!','手机号码格式不正确!'],
        '1008'=>['验证码错误!','验证码错误!'],
        '1010'=>['用户名或密码格式不正确!','用户名或密码的格式不正确!'],
        '1012'=>['注册失败!','注册失败！'],
        '1014'=>['当前账号已注册!','当前账号已注册!'],
        '1016'=>['用户名或密码错误!','用户名或密码错误！'],
        '1018'=>['用户名不能为空!','用户名不能为空！'],
        '1020'=>['密码不能为空!','密码不能为空！'],
        '1022'=>['用户名格式不正确!','用户名格式不正确！'],
        '1024'=>['密码格式不正确!','密码格式不正确！'],
        '1026'=>['确认密码不能为空!','确认密码不能为空！'],
        '1028'=>['两次密码不一致!','两次密码不一致！'],
        '1030'=>['密码修改失败!','密码修改失败！'],
        '1032'=>['用户名不存在!','用户名不存在！'],
        '1034'=>['用户名不存在!','用户名不存在！'],
        '1036'=>['发表失败!','发表失败！'],
        '1038'=>['邀请码必填!','邀请码必填！'],
        '1040'=>['邀请码不存在!','邀请码不存在！'],
        '1042'=>['该手机号已经绑定过账号了!','该手机号已经绑定过账号了！'],
        '1044'=>['当前账号已经绑定过手机号了!','当前账号已经绑定过手机号了！'],
        '1046'=>['你没有充值权限!','你没有充值权限！'],
        '1048'=>['充值失败!','充值失败!'],
        '1050'=>['只有每周星期五才能提现!','只有每周星期五才能提现!'],
        '1052'=>['提现金额不能大于余额!','提现金额不能大于余额!'],
        '1054'=>['提现失败!','提现失败!'],
        '1056'=>['只有周五或月末才能提现','只有周五或月末才能提现'],
        '1058'=>['请选择申请的角色','请选择申请的角色'],
        '1060'=>['你还没有选择角色','你还没有选择角色'],
        '1062'=>['请阅读并同意用户协议后再申请','请阅读并同意用户协议后再申请'],
        '1064'=>['您还有未审核的充值订单,待审核通过后才能继续充值','您还有未审核的充值订单,待审核通过后才能继续充值'],
        '1066'=>['余额不足350不能提现','余额不足350不能提现'],
        '1068'=>['邀请码错误','邀请码错误'],
        '1070'=>['提现阿纳豆与手续费大于余额,不能提现','提现阿纳豆与手续费大于余额，不能提现'],

    ];

    // 系统设置
    public $systemInfo;
    /**
     * 初始化
     */
    public function _initialize(){

        // 获取系统设置信息
        $this->getSystemInfo();
        //>> 拿session
        $session = session(md5('home'));
        if(!empty($session)){
            //>> 查询会员角色升级规则
            $condition = M('RoleUp')->select();
            $zcArr = [];
            $jjArr = [];
            $zpArr = [];
            $cpArr = [];
            foreach($condition as $key => $value){
                switch($value['name']){
                    case 'zhichi':
                        $zcArr = $value;
                        break;
                    case 'jingji':
                        $jjArr = $value;
                        break;
                    case 'zhipian':
                        $zpArr = $value;
                        break;
                    case 'chupin':
                        $cpArr = $value;
                        break;
                }
            }
            //>> 查询用户
            $row = M('Member')->where(['session_token'=>$session])->find();
            if(!empty($row)){
                //>> 查询投资
                $support = $row['all_support_money'];


                if($support < $zcArr['support']){

                    M('Member')->where(['id'=>$row['id']])->save(['role'=>0]);
                }
                //>> 判断投资是否满xx,满xx升级为支持者
                if($support >= $zcArr['support']){

                    M('Member')->where(['id'=>$row['id']])->save(['role'=>1]);
                }

                //>> 判断上级已经有多少下线
                $count = $this->groupTrue($row['id']);

                //>> 团队一共多少人
                $all = $this->allMembersTrue($row['id']);


                //>> 多少经纪人
                $jingji = $this->getJingJiRen($row['id']);

                //>> 多少制片人
                $zhipian = $this->getZhiPianRen($row['id']);

                //>> 多少出品人
                $chupin = $this->getChuPinRen($row['id']);


                //>> 直推xxx人，升级为经纪人
                if($count >= $jjArr['follower'] && $support >= $jjArr['support'] ){

                    //>> 升级为经纪人
                    M('Member')->where(['id'=>$row['id']])->save(['role'=>2]);
                }

                //>> 如果投资35000以上,直推10名,团队100人升级为制片人,2名经纪人
                if($support >= $zpArr['support'] && $count >= $zpArr['follower'] && $all >= $zpArr['group'] && (($jingji+$zhipian+$chupin) >= $zpArr['follower_jingji'] )  ){
                    //>> 升级为制品人
                    M('Member')->where(['id'=>$row['id']])->save(['role'=>3]);
                }

                //>> 如果个人投资70000 直推30人 团队500人 2名制片人
                if($support >= $cpArr['support'] && $count >= $cpArr['follower'] && $all >= $cpArr['group'] && ($zhipian+$chupin >= $cpArr['follower_zhipian'])){
                    //>> 升级为经纪人
                    M('Member')->where(['id'=>$row['id']])->save(['role'=>4]);
                }

                //>> 如果是管理员账号，直接升级为出品人
                if($row['is_admin'] == 1){
                    //>> 升级为出品人
                    M('Member')->where(['id'=>$row['id']])->save(['role'=>4]);
                }

                $this->isLogin = 1;
                $this->userInfo = $row;
                $this->assign('userInfo',$row);
            }
        }else{
            //>> 拿cookie
            $cookie = cookie(md5('home'));
            if(!empty($cookie)){
                $row = M('User')->where(['remember_token'=>$cookie]);
                if(!empty($row)){
                    $this->isLogin = 1;
                    $this->userInfo = $row;
                    $this->assign('userInfo',$row);
                }
            }
        }
        $this->assign('isLogin',$this->isLogin);


    }


    /**
     * 查询直推人数
     */
    private function group($id){

        $res = M('Member')->where(['parent_id'=>$id])->select();

        if(!empty($res)){

            return count($res);
        }
    }

    /**
     * 查询直推人数
     */
    private function groupTrue($id){

        $res = M('Member')->where(['parent_id'=>$id,'role'=>['egt',1]])->select();

        if(!empty($res)){

            return count($res);
        }
    }

    /**
     * 团队(1.直推一部分，然后下线发展一部分。2.直推一人，再下线发展)
     */
    protected function allMembersTrue($id){

        static $sum = 0;
        $model = M('Member');
        $rows = $model ->where(['parent_id'=>$id,'role'=>['egt',1]])->select();

        $count = count($rows);
        $sum += $count;

        if(!empty($rows)){
            foreach($rows as $k => $v){

                $this->allMembersTrue($v['id']);
            }
        }
        return $sum;
    }

    /**
     * 团队(1.直推一部分，然后下线发展一部分。2.直推一人，再下线发展)
     */
    protected function allMembers($id){

        static $sum = 0;
        $model = M('Member');
        $rows = $model ->where(['parent_id'=>$id])->select();

        if(!empty($rows)){

            $count = count($rows);
            $sum += $count;

            foreach($rows as $k => $v){
                $this->allMembers($v['id']);
            }
        }
        return $sum;
    }


    /**
     * 团队(1.直推一部分，然后下线发展一部分。2.直推一人，再下线发展)
     */
    protected function notTrue($id){

        static $sum = 0;

        $model = M('Member');

        $rows = $model ->where(['parent_id'=>$id,'role'=>['egt',1]])->select();


        if(!empty($rows)){

            $count = count($rows);
            $sum += $count;

            foreach($rows as $k => $v){
                $this->notTrue($v['id']);
            }
        }
        return $sum;
    }


    /**
     * 查询下线经纪人
     */
    public function getJingJiRen($id){

        $rows = M('Member')->where(['parent_id'=>$id,'role'=>2])->count();

        return $rows;
    }

    /**
     * 查询下线制片人人
     */
    public function getZhiPianRen($id)
    {

        $rows = M('Member')->where(['parent_id' => $id, 'role' => 3])->count();

        return $rows;
    }

    /**
     * 查询下线出品人
     */
    public function getChuPinRen($id){

        $rows = M('Member')->where(['parent_id'=>$id,'role'=>4])->count();

        return $rows;
    }

    /*
     * 空操作
     */
    public function _empty()
    {
        // 显示404 页面
        $this->display('Error:404');

    }



    /**
     *获取错误
     */
    public function getError($code,$isShow = 1){

        if(empty($code)) return false;

        $errMsg = $this->_msgArr[$code][0];

        if($isShow){

            $msg = ['status'=>0,'msg'=>$errMsg,'errCode'=>$code];

        }else{

            $msg = ['status'=>0,'errCode'=>$code];

        }

        return $msg;
    }

    /**
     * 发送错误
     */
    public function _printError($code){

        if(empty($code)) return false;

        $out = $this->getError($code);

        return json_encode($out,JSON_UNESCAPED_UNICODE);

    }

    /**
     * 请求成功
     */
    public function _printSuccess($value = [],$isobject = 0)
    {
        $out = array("status" => 1,"data" => $value);

        if($isobject){

            $out = array("status" => 1,"data" => (object)$value );
        }

        return json_encode($out);
    }

    /**
     * 获取系统配置信息
     */
    public function getSystemInfo()
    {
        // 获取系统设置数据
        $this->systemInfo = M('System')->find(1);
        // 分配到页面中
        $this->assign('systemInfo', $this->systemInfo);
    }
}