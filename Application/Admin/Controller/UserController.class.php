<?php
namespace Admin\Controller;

class UserController extends CommonController
{
    public function _initialize(){
        parent::_initialize();
        // 检测用户是否登录，没有登录不能继续执行
        if(!$this->isLogin){
            $this->redirect('admin/login/index');
            exit;
        }
    }
    public function index(){
        $name = I('get.name');
        $where = [];
        if($name){
            $where['username'] = ['like',"%$name%"];
        }
        // 查询出所有管理员
        $userInfos = M('User')->where($where)->select();
        $this->assign('userInfos',$userInfos);
        $this->display('user/index');
    }

    public function add(){
        if(IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');
            $salt = substr(uniqid(rand()),-6);
            $userInfo = [
                'username' => $_data['name'],
                'password' => md5($_data['password'].$salt),
                'phone' => $_data['phone'],
                'image_url' => $_data['image_url'],
                'create_time' => time(),
                'salt'=>$salt,
            ];
            // 添加到数据库
            $ret = M('User')->add($userInfo);
            if($ret === false){
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }
            $this->ajaxReturn(['msg'=>'数据保存成功', 'status'=>1,]);
        }
        $this->display('user/add');
    }

    public function edit($id){
        $id = intval($id);
        $projectModel = D('User');

        // 获取项目数据
        $userInfo = $projectModel->find($id);

        // 判断数据是否存在
        if (!$userInfo) {
            $this->Msg['msg'] = "用户信息不存在";
            $this->ajaxReturn($this->Msg);
        }
        if (IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');
            $salt = substr(uniqid(rand()),-6);

            // 获取项目数据
            $performerInfo = $projectModel->find($_data['id']);

            // 判断数据是否存在
            if (!$performerInfo) {
                $this->ajaxReturnError('用户不存在',__LINE__);

            }
            if(!empty($_data['password'])){
                $password = md5($_data['password'].$salt);
            }
            // 获取项目基本信息
            $performerInfo = [
                'username' => $_data['name'],
                'password' => $password,
                'phone' => $_data['phone'],
                'image_url' => $_data['image_url'],
            ];

            // 将项目基本信息保存到数据库 an_project表
            $_id = M('User')
                ->where(['id'=>$_data['id']])
                ->save($performerInfo);

            if($_id === false){
                M()->rollback();
                $this->ajaxReturnError('数据更新失败',__LINE__);
            }

            $this->ajaxReturn(['msg'=>'数据更新成功', 'status'=>1,]);

        }
        $this->assign('row',$userInfo);
        $this->display('user/edit');
    }

    public function remove($id){
// 将ID转换成整数类型
        $id = intval($id);
        // 判断是否传了ID
        if(!$id){
            // 没有ID，报错
            $this->error('没有找到数据');
            exit;
        }
        // 实例化模型类
        $model = D('User');
        // 通过ID主键 查询标签信息
        $info = $model->find($id);
        if(!$info){
            // 没有在数据库中找到数据，报错
            $this->error('没有找到数据');
            exit;
        }
        // 执行删除
        $res = $model->delete($id);
        if(!$res){
            $this->error('删除失败！');
            exit;
        }
        // 删除成功直接回到首页
        $this->redirect('admin/user/index');
    }

}