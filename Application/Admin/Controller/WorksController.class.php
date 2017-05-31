<?php
namespace Admin\Controller;

use Think\Cache\Driver\Redis;

class WorksController extends  CommonController
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

        //查看是否有查询
        $name = I('get.name','','strip_tags');
        // 创建查询条件
        $where =[];
        if($name){
            $where['name'] = ['like',"%$name%"];
        }
        // 查询出所有演员
        $performerInfos = M('Performer')->where($where)->select();
        $this->assign('performerInfos',$performerInfos);
        $this->display('works/index');
    }

    public function add(){
        if(IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');
            $performerInfo = [
                'name' => $_data['name'],
                'type' => $_data['type'],
                'works' => $_data['works'],
                'fans_number' => $_data['fans_number'],
                'image_url' => $_data['image_url'],
                'create_time' => time(),
            ];
            // 添加到数据库
            $ret = M('performer')->add($performerInfo);
            if($ret === false){
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }
            $this->ajaxReturn(['msg'=>'数据保存成功', 'status'=>1,]);
        }
        $this->display('works/add');
    }

    public function edit($id){
        $id = intval($id);
        $projectModel = D('Performer');

        // 获取项目数据
        $performerInfo = $projectModel->find($id);

        // 判断数据是否存在
        if (!$performerInfo) {
            $this->Msg['msg'] = "演员信息不存在";
            $this->ajaxReturn($this->Msg);
        }
        if (IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');

            // 获取项目数据
            $performerInfo = $projectModel->find($_data['id']);

            // 判断数据是否存在
            if (!$performerInfo) {
                $this->ajaxReturnError('项目不存在',__LINE__);
            }

            // 获取项目基本信息
            $performerInfo = [
                'name' => $_data['name'],
                'type' => $_data['type'],
                'works' => $_data['works'],
                'fans_number' => $_data['fans_number'],
                'image_url' => $_data['image_url'],
            ];

            // 将项目基本信息保存到数据库 an_project表
            $_id = M('Performer')
                ->where(['id'=>$_data['id']])
                ->save($performerInfo);

            if($_id === false){
                M()->rollback();
                $this->ajaxReturnError('数据更新失败',__LINE__);
            }

            $this->ajaxReturn(['msg'=>'数据更新成功', 'status'=>1,]);

        }
        $this->assign('row',$performerInfo);
        $this->display('works/edit');
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
        $model = D('Performer');
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
        $this->redirect('admin/works/index');
    }
}