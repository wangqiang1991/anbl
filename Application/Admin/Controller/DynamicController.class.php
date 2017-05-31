<?php
namespace Admin\Controller;

class DynamicController extends CommonController
{
    public function index(){
        $name = I('get.name','','strip_tags');
        // 创建查询条件
        $where =[];
        if($name){
            $where['b.name'] = ['like',"%$name%"];
        }
        // 查询出所有动态信息
        $dynamicInfos = M('ProjectDynamic as a')
            ->field("a.*,b.name")
            ->join('left join an_project as b on b.id=a.project_id')
            ->order('create_time desc')
            ->where($where)
            ->select();
        $this->assign('dynamicInfos',$dynamicInfos);
        $this->display('dynamic/index');
    }

    public function add(){
        if(IS_POST && IS_AJAX){
            $data = I('post.');

            $projectInfo = [
                'project_id' => intval($data['id']),
                'content' => $data['content'],
                'create_time' => time(),
            ];
            // 保存到数据库
            $result = M('ProjectDynamic')->add($projectInfo);
            if($result === false){
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }
            $this->ajaxReturn(['msg'=>'数据保存成功', 'status'=>1,]);
        }else{
            // 接受请求参数

            $id = intval(I('get.id'));

            // 判断项目是否存在
            $info = M('Project')->find($id);
            if(!$info){
                $this->error('项目不存在');
                exit;
            }
            $this->assign('info',$info);
            $this->display('dynamic/add');
        }
    }

    public function edit($id){
        $id = intval($id);
        $ArticleModel = D('ProjectDynamic');

        // 获取项目数据
        $ArticleInfo = $ArticleModel->find($id);

        // 判断数据是否存在
        if (!$ArticleInfo) {
            $this->Msg['msg'] = "信息不存在";
            $this->ajaxReturn($this->Msg);
        }

        // 获取项目名称
        $project = M('Project')->find($ArticleInfo['project_id']);
        $projectName = $project['name'];
        if (IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');

            // 获取项目数据
            $performerInfo = $ArticleModel->find($_data['id']);

            // 判断数据是否存在
            if (!$performerInfo) {
                $this->ajaxReturnError('项目不存在',__LINE__);
            }

            // 获取项目基本信息
            $performerInfo = [
                'content' => $_data['content'],
            ];

            // 将项目基本信息保存到数据库 an_project表
            $_id = M('ProjectDynamic')
                ->where(['id'=>$_data['id']])
                ->save($performerInfo);

            if($_id === false){
                M()->rollback();
                $this->ajaxReturnError('数据更新失败',__LINE__);
            }

            $this->ajaxReturn(['msg'=>'数据更新成功', 'status'=>1,]);

        }

        $this->assign('row',$ArticleInfo);
        $this->assign('projectName',$projectName);
        $this->display('dynamic/edit');
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
        $model = D('ProjectDynamic');
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
        $this->redirect('admin/dynamic/index');
    }
}