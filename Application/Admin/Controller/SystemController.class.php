<?php
namespace Admin\Controller;

class SystemController extends CommonController
{
    public function _initialize(){
        parent::_initialize();
        // 检测用户是否登录，没有登录不能继续执行
        if(!$this->isLogin){
            $this->redirect('admin/login/index');
            exit;
        }
    }
    //系统设置
    public function index(){
        if (IS_POST) {
            // 获取数据
            $data = M('System')->create();

            // 判断数据是否合法
            if (!$data) {
                $this->Msg['msg'] = M('System')->getError();
                $this->ajaxReturn($this->Msg);
            }
            // 判断 是否需要更改邮件发送密码
            if (empty($data['send_mail_password'])) {
                unset($data['send_mail_password']);
            }

            // 将数据保存到数据库中
            $rst = M('System')->save($data);
            if ($rst === false) {
                $this->Msg['msg'] = '保存失败';
                $this->ajaxReturn($this->Msg);
            } else {
                $this->Msg['status'] = 1;
                $this->Msg['msg'] = '保存成功';
                $this->ajaxReturn($this->Msg);
            }
            exit;

        }
        // 查询出系统设置信息
        $systemInfo = M('System')->find();
        $this->assign('info',$systemInfo);
        $this->display('system/index');
    }

    /**
     * 合作机构
     */
    public function cooperation(){
        $cooperInfos = M('Cooper')->select();
        $this->assign('cooperInfos',$cooperInfos);
        $this->display('system/cooper');
    }

    public function add(){
        if(IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');
            $cooperInfo = [
                'name' => $_data['name'],
                'image_url' => $_data['image_url'],
                'url' => $_data['url'],
                'create_time' => time(),
            ];
            // 添加到数据库
            $ret = M('Cooper')->add($cooperInfo);
            if($ret === false){
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }
            $this->ajaxReturn(['msg'=>'数据保存成功', 'status'=>1,]);
        }
        $this->display('system/add');
    }

    public function edit($id){
        $id = intval($id);
        $projectModel = D('Cooper');

        // 获取项目数据
        $cooperInfo = $projectModel->find($id);


        // 判断数据是否存在
        if (!$cooperInfo) {
            $this->Msg['msg'] = "机构信息不存在";
            $this->ajaxReturn($this->Msg);
        }
        if (IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');

            // 获取项目数据
            $cooperInfo = $projectModel->find($_data['id']);

            // 判断数据是否存在
            if (!$cooperInfo) {
                $this->ajaxReturnError('机构信息不存在',__LINE__);
            }

            // 获取项目基本信息
            $performerInfo = [
                'name' => $_data['name'],
                'image_url' => $_data['image_url'],
                'url' => $_data['url'],
            ];

            // 将项目基本信息保存到数据库 an_project表
            $_id = M('Cooper')
                ->where(['id'=>$_data['id']])
                ->save($performerInfo);

            if($_id === false){
                M()->rollback();
                $this->ajaxReturnError('数据更新失败',__LINE__);
            }

            $this->ajaxReturn(['msg'=>'数据更新成功', 'status'=>1,]);

        }
        $this->assign('row',$cooperInfo);
        $this->display('system/edit');
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
        $model = D('Cooper');
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
        $this->redirect('admin/System/cooper');
    }
}