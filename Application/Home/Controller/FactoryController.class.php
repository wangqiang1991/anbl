<?php
namespace Home\Controller;

use Think\Page;

class FactoryController extends CommonController
{

    public function index(){


        //>> 判断是否登录
        if($this->isLogin == 0){

            $this->redirect('Home/login/index');
            exit;
        }

        $name = I('get.name');
        $where = [];
        if($name){
            $where['a.name']=['like',"%$name%"];
        }
        // 查询总记录数
        $count =  M('MemberStar as a')
            ->field('a.*,b.name as project_name,c.name as role_name')
            ->join('left join an_project_recruit as b on b.id=a.project_id')
            ->join('left join an_project_role as c on c.id=a.role_id')
            ->where($where)
            ->count();

        // 分页处理
        $page = new Page($count,10);
        // 查询出所有我要当明星
        $starInfo = M('MemberStar as a')
            ->field('a.*,b.name as project_name,c.name as role_name')
            ->join('left join an_project_recruit as b on b.id=a.project_id')
            ->join('left join an_project_role as c on c.id=a.role_id')
            ->where($where)
            ->order('vote_number desc')
            ->limit($page->firstRow, $page->listRows)
            ->select();
        // 生成分页DOM结构
        $pages = $page->wapShow();
        // 向模板分配分页条
        $this->assign('pages',$pages);
        $this->assign('starInfo',$starInfo);


        // 查询出所有优秀演员
        $performerInfos = M('Performer')
            ->order('create_time desc')
            ->limit(0,10)
            ->select();
        $this->assign('performerInfos',$performerInfos);
        // 查询出所有优秀导演
        $directorInfos = M('Director')
            ->order('create_time desc')
            ->limit(0,10)
            ->select();
        $this->assign('directorInfos',$directorInfos);
        // 查询出所有优秀作品
        $worksInfos = M('Works')
            ->order('create_time desc')
            ->limit(0,10)
            ->select();
        $this->assign('worksInfos',$worksInfos);
        $this->display('factory/index');
    }

    /**
     * 我要当演员
     */
    public function actor(){
        if(IS_POST && IS_AJAX){
            //判断有没有搜索
            $where = [];
            $name = I('post.name');
            if($name){
                $where['name'] =$name;
                $rows = M('')->where($where)->find();
                if($rows){
                    $this->ajaxReturn([
                        'data' =>$rows,
                        'status' =>1,
                    ]);
                    exit;
                }else{
                    $this->ajaxReturn([
                        'msg' =>"没有您想要的数据",
                        'status' =>0,
                    ]);
                    exit;
                }
            }
            // 查询出我要当演员的所有申请
            $rows = M('')->where($where)->select();
            if($rows){
                $this->ajaxReturn([
                    'data' =>$rows,
                    'status' =>1,
                ]);
                exit;
            }else{
                $this->ajaxReturn([
                    'msg' =>"没有您想要的数据",
                    'status' =>0,
                ]);
                exit;
            }
        }
    }

    /**
     * 投票
     */
    public function vote(){
        if(IS_POST && IS_AJAX){
            //判断会员是否已经登录
            if(!$this->isLogin){
                $this->ajaxReturn(['msg'=>"对不起，您还没有登录",'status'=>0]);
            }
            $data = I('post.');
            $type_id = intval($data['type']);
            $id = intval($data['id']);
            // 判断会员余额够不够
            if($this->userInfo['money']<1){
                $this->ajaxReturn(['msg'=>"阿纳豆不足",'status'=>0]);
            }

            //判断投票类别
            if($type_id == 1){//优秀演员
                $model = M('performer');
               $this->setMoney($model,$id);
            }elseif($type_id == 2){
                $model = M('Director');
                $this->setMoney($model,$id);
            }elseif($type_id == 3){
                $model = M('Works');
                $this->setMoney($model,$id);
            }elseif($type_id == 4){
                $model = M('MemberStar');
                $this->setMoney($model,$id);
            }
        }
    }

    protected function setMoney($mode,$id){
        $model = $mode;
        //查看是否存在记录
        $info = $model->find($id);
        if(!$info){
            $this->ajaxReturn(['msg'=>"演员信息不存在",'status'=>0]);
        }
        $result = $model->where(['id'=>$id])->save(['vote_number'=>$info['vote_number']+1,'fans_number'=>$info['fans_number']+1]);
        if($result === false){
            $this->ajaxReturn(['msg'=>"投票失败",'status'=>0]);
        }


        // 更新用户余额
        $rest = M('Member')->where(['id' => $this->userInfo['id']])->save(['money' => ['exp', 'money-' . 1]]);
        if($rest === false){
            $this->ajaxReturn(['msg'=>"投票失败",'status'=>0]);
        }
        // 生成用户消费明细
        $data = [
            'member_id'=>$this->userInfo['id'],
            'type'=>"投票",
            'create_time'=>time(),
            'money'=>1,
        ];
        $rest = M('MemberConsume')->add($data);
        if($rest === false){
            $this->ajaxReturn(['msg'=>"投票失败",'status'=>0]);
        }
        $this->ajaxReturn(['msg'=>"投票成功",'status'=>1]);

    }

}