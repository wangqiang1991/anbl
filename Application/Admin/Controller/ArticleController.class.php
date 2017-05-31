<?php
namespace Admin\Controller;

class ArticleController extends CommonController
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
        //查询出所有消息
        $articleInfos = M('Article as a')
            ->field("a.*,b.name")
            ->join('left join an_article_category as b on b.id=a.type')
            ->select();
        $this->assign('articleInfos',$articleInfos);
        $this->display('article/index');

    }

    public function add(){
        if(IS_POST){
            $data = i('post.');
            $articleInfo = [
                'title' => $data['title'],
                'content' => $data['content'],
                'type' => $data['type'],
                'create_time' => time(),
            ];

            //保存到数据库
            $rest = M('Article')->add($articleInfo);
            if($rest == false){
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }
            $this->ajaxReturn(['msg'=>'数据保存成功', 'status'=>1,]);
        }
        // 查询所有分类
        $types = M('ArticleCategory')->select();
        $this->assign('articleCategory',$types);
        $this->display('article/add');
    }

    public function edit($id){
        $id = intval($id);
        $ArticleModel = D('Article');

        // 获取项目数据
        $ArticleInfo = $ArticleModel->find($id);

        // 判断数据是否存在
        if (!$ArticleInfo) {
            $this->Msg['msg'] = "信息不存在";
            $this->ajaxReturn($this->Msg);
        }
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
                'title' => $_data['title'],
                'type' => $_data['type'],
                'content' => $_data['content'],
            ];

            // 将项目基本信息保存到数据库 an_project表
            $_id = M('Article')
                ->where(['id'=>$_data['id']])
                ->save($performerInfo);

            if($_id === false){
                M()->rollback();
                $this->ajaxReturnError('数据更新失败',__LINE__);
            }

            $this->ajaxReturn(['msg'=>'数据更新成功', 'status'=>1,]);

        }
        // 获取信息分类
        $types = M('ArticleCategory')->select();

        $this->assign('types',$types);
        $this->assign('row',$ArticleInfo);
        $this->display('article/edit');
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
        $model = D('Article');
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
        $this->redirect('admin/article/index');
    }
}