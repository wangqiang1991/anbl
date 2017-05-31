<?php
namespace Admin\Controller;

class CategoryController extends CommonController
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

        // 查询出所有项目分类
        $ArticleCategorys = M('ArticleCategory')->select();

        $this->assign('ArticleCategorys',$ArticleCategorys);

        $this->display('category/index');
    }

    public function add(){
        //判断请求方式
        if(IS_POST){
            $model = D('ArticleCategory');
            $data = $model->create();
            if(!$data){
                $this->error($model->getError());
                exit;
            }
            $data['create_time']=time();
            //将数据保存到数据库
            $result = $model->add($data);
            if(!$result){
                $this->error('数据保存失败');
                exit;
            }
            $this->redirect('admin/Category/index');
            exit;
        }
        $this->display('category/add');
    }

    public function edit(){
        // 接收传递参数
        $id = I('get.id',0,'intval');
        // 验证id是否存在
        if(!$id){
            $this->error('数据不存在');
            exit;
        }
        // 通过id查询数据
        $Model= D('ArticleCategory');
        $row = $Model->find($id);
        if(!$row){
            $this->error('数据不存在');
            exit;
        }
        // 判断是否是POST请求
        if(IS_POST) {
            // 使用create方法，自动验证、自动完成。
            $data = $Model->create();
            if(!$data){
                $this->error($Model->getError());
                exit;
            }
            // 判断POST过来的ID有没有被用户篡改过
            if(!$data['id'] || $data['id'] != $id){
                $this->error('数据不能乱改！');
                exit;
            }
            // 执行更新
            $res = $Model->save($data);
            if(!$res){
                $this->error('更新失败！');
                exit;
            }
            // 更新成功直接跳转到首页
            $this->redirect('admin/category/index');
            exit;
        }
        // 向模板分配数据
        $this->assign('row',$row);
        $this->display('category/edit');
    }

    public function remove($id){
// 将ID转换成整数类型
        $id = intval($id);
        // 判断是否传了ID
        if(!$id){
            // 没有ID，报错
            $this->error('没有找到数据1');
            exit;
        }
        // 实例化模型类
        $model = D('ArticleCategory');
        // 通过ID主键 查询标签信息
        $info = $model->find($id);
        if(!$info){
            // 没有在数据库中找到数据，报错
            $this->error('没有找到数据2');
            exit;
        }
        // 执行删除
        $res = $model->delete($id);
        if(!$res){
            $this->error('删除失败！');
            exit;
        }
        // 删除成功直接回到首页
        $this->redirect('admin/category/index');
    }
}