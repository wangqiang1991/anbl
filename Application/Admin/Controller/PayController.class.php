<?php
namespace Admin\Controller;

class PayController extends CommonController
{
    public function index(){
     // 查询出所有支付方式
        $payInfos = M('Pay')->select();
        $this->assign('payInfos',$payInfos);
        $this->display('pay/index');
    }

    public function add(){
        if(IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');

            // 判断是否上传了二维码
            if(!$_data['image_url']){
                $this->ajaxReturnError('二维码必须上传',__LINE__);
            }
            $payInfo = [
                'name' => $_data['name'],
                'image_url' => $_data['image_url'],
                'account' => $_data['account'],
                'username' => $_data['username'],
                'bank' => $_data['bank'],
                'create_time' => time(),
            ];
            // 添加到数据库
            $ret = M('Pay')->add($payInfo);
            if($ret === false){
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }
            $this->ajaxReturn(['msg'=>'数据保存成功', 'status'=>1,]);
        }
        $this->display('pay/add');
    }

    public function edit($id){
        $id = intval($id);
        $projectModel = D('Pay');

        // 获取项目数据
        $performerInfo = $projectModel->find($id);

        // 判断数据是否存在
        if (!$performerInfo) {
            $this->Msg['msg'] = "支付信息不存在";
            $this->ajaxReturn($this->Msg);
        }
        if (IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');

            // 获取项目数据
            $performerInfo = $projectModel->find($_data['id']);

            // 判断数据是否存在
            if (!$performerInfo) {
                $this->ajaxReturnError('支付方式不存在',__LINE__);
            }

            // 获取项目基本信息
            $performerInfo = [
                'name' => $_data['name'],
                'image_url' => $_data['image_url'],
                'username' => $_data['username'],
                'bank' => $_data['bank'],
                'account' => $_data['account'],
            ];

            // 将项目基本信息保存到数据库 an_project表
            $_id = M('Pay')
                ->where(['id'=>$_data['id']])
                ->save($performerInfo);

            if($_id === false){
                M()->rollback();
                $this->ajaxReturnError('数据更新失败',__LINE__);
            }

            $this->ajaxReturn(['msg'=>'数据更新成功', 'status'=>1,]);

        }
        $this->assign('row',$performerInfo);
        $this->display('pay/edit');
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
        $model = D('Pay');
        // 通过ID主键 查询标签信息
        $info = $model->find($id);
        if(!$info){
            // 没有在数据库中找到数据，报错
            $this->error('没有找到数据');
            exit;
        }
        // 判断定但是否有使用次此支付方式的 有不能删除
        $rest = M('MemberRecharge')->where(['type'=>$id])->find();
        if($rest){
            $this->error('已有会员使用了该充值方式，只能修改，不能删除');
            exit;
        }
        // 执行删除
        $res = $model->delete($id);
        if(!$res){
            $this->error('删除失败！');
            exit;
        }
        // 删除成功直接回到首页
        $this->redirect('admin/pay/index');
    }
}