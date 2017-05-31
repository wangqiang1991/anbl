<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends CommonController{

    /**
     * 首页界面
     */
    public function index(){


        //>> 判断是否登录
        if($this->isLogin == 0){

            $this->redirect('Home/login/index');
            exit;
        }

        // 查询出首页轮播 项目
        $model = M('project');
        $lunbos = $model
            ->where([
                'recommend' => 1,
                'end_time'   => [['egt', time()], '0', 'or'],// 结束时间 大于等于当前时间 或 为0
                'start_time' => ['elt', time()],// 开始时间 小于等于当前时间
                'is_active'      => 1,
                ])
            ->limit(0,3)
            ->order('sort')
            ->select();

        // 查询出星级项目
        $where[] = [
            'end_time'   => [['egt', time()], '0', 'or'],// 结束时间 大于等于当前时间 或 为0
            'start_time' => ['elt', time()],// 开始时间 小于等于当前时间
            'is_active'      => 1,//上架状态
        ];
        $projectInfo = $model
            ->where($where)
            ->order('star_num desc')
            ->select();

        // 查询出所有新闻
        $news = M('Article')->select();
        $this->assign('news',$news);
        $this->assign('lunbos',$lunbos);
        $this->assign('projectInfo',$projectInfo);
        $this->display('index/index');
    }


    /**
     * 项目详情
     */
    public function detail($id){
        $id = intval($id);
        $commentModel = M('Comment');
        //>> 默认查询导演
        $rows = $commentModel->where(['type'=>1,'movie_id'=>$id,'is_pass'=>1])->order('create_time desc')->select();
        foreach($rows as &$row){
            $row['username']= telephoneNumber($row['username']);
        }
        unset($row);
        $directorArr = [];
        $count = 0;

        if(!empty($rows)){
            $count = ceil(count($rows) / 12);
            $directorArr = $this->pagination($rows,1,12);
        }
        // 查看项目是否存在
        $info = M('project as a')
            ->field('a.*,b.story,b.analysis,b.film_critic,b.expected_return')
            ->join('left join an_project_survey as b on b.project_id = a.id')
            ->where(['a.id'=>$id])
            ->find();
        if(!$info){
            $this->error('项目不存在');
            exit;
        }

        // 查询出项目最新动态
        $dynamic = M('ProjectDynamic')->where(['project_id'=>$id])->order('create_time desc')->select();

        $this->assign([
            'info'=>$info,
            'comment'=>$directorArr,
            'count'=>$count,
            'dynamic'=>$dynamic,
        ]);

        // 获取收益预测
        $projectPrice = M('ProjectPrice')
            ->where([
                'project_id' => $id
            ])
            ->order('create_time desc')
            ->find();
        // 价格走势图数据分析
        $priceTimes = json_decode($projectPrice['pricetimes'], true);
        // 排序
        ksort($priceTimes);
        foreach ($priceTimes as $key => &$price) {
            $price = [
                'time'  => $key,
                'price' => $price
            ];
        }
        unset($price);
        // 分配所有价格列表
        $this->assign('priceTimes', $priceTimes);
        $this->assign('cycle', $info['exp_cycle']);
        $this->assign('end_time', $info['exp_date']);

        $this->assign('projectPrice', $projectPrice);

        $this->display('index/detail');
    }

    /**
     * 分页查询
     */
    public function pageSelect(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){
            if(isset($paramArr['pgNum']) && !empty($paramArr['pgNum']) && is_numeric($paramArr['pgNum'])){
                $pgNum = $paramArr['pgNum'];
            }else{
                $pgNum = 1;
            }
            if(isset($paramArr['pgSize']) &&!empty($paramArr['pgSize']) && is_numeric($paramArr['pgSize'])){
                $pgSize = $paramArr['pgSize'];
            }else{
                $pgSize = 12;
            }
            $rows = M('Comment')->where(['movie_id'=>$paramArr['movie_id'] ,'type'=>$paramArr['type'] ? $paramArr['type'] : 1])->order('create_time desc')->select();
            foreach($rows as &$row){
                $row['username']= telephoneNumber($row['username']);
            }

            unset($row);
            if(!empty($rows)){
                $directorArr = $this->pagination($rows,$pgNum,$pgSize);
                $this->ajaxReturn([
                    'directorArr'=>$directorArr,
                    'pages'=>ceil(count($rows)/12) ? ceil(count($rows)/12):0,
                    'crrpage'=>$paramArr['pgNum'],
                    'status'=>1
                ]);
            }else{
                die($this->_printError('1000'));
            }
        }else{
            die($this->_printError('1002'));
        }
    }

    /**
     * 分页工具
     */
    public function pagination($data = [],$pgNum,$pgSize){

        if(empty($data)) return false;

        $start = ($pgNum - 1) * $pgSize;
        $sliceArr = array_slice($data,$start,$pgSize);
        return $sliceArr;
    }

    public function support(){
        if(IS_POST && IS_AJAX){
            //判断会员是否已经登录
            if(!$this->isLogin){
                $this->ajaxReturn(['msg'=>"对不起，您还没有登录",'status'=>0]);
            }
            $data = i('post.');
            // 支持金额
            $support_money = $data['money'];

            // 判断用户余额够不够
            if($support_money>$this->userInfo['money']){
                $this->ajaxReturn(['msg'=>"对不起，阿纳豆不足",'status'=>0]);
            }

            // 判断通一会员的支持额 小于70000
            $allMoney = M('MemberSupport')->where(['member_id'=>$this->userInfo['id'],'project_id'=>$data['project_id']])->sum('support_money');

            if(($support_money+$allMoney)>70000){
                $this->ajaxReturn(['msg'=>"对不起，你的投资额已满",'status'=>0]);
            }
            // 会员id
            $member_id = $this->userInfo['id'];
            // 项目id
            $project_id = intval($data['project_id']);


            $projectInfo = M('Project')->find($project_id);
            if(!$projectInfo){
                $this->ajaxReturn(['msg'=>"项目不存在",'status'=>0]);
            }

            // 判断目标金额是否达到
            if($projectInfo['target_amount'] <= $projectInfo['money']){
                $this->ajaxReturn(['msg'=>"阿纳豆已达到，不能进行支持",'status'=>0]);
            }

            if(($support_money+$projectInfo['money'])>$projectInfo['target_amount']){
                $x = $projectInfo['target_amount']-$projectInfo['money'];
                $this->ajaxReturn(['msg'=>"该影片还可以提供.$x.阿纳豆支持",'status'=>0]);
            }

            // 分红类型  1固定 2浮动

            $type = intval($data['type']);

            // 生成订单
            $order_number = 'ZC'.date('Ymd') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
            //封装数据
            $supportInfo = [
                'member_id' => $member_id,
                'project_id' => $project_id,
                'support_money' => $support_money,
                'type' => $type,
                'order_number' => $order_number,
                'is_fh' => 0,//是否分红
                'is_true' => 0,//是否返还用户余额
                'create_time' => time(),
            ];

            // 开启事物
            M()->startTrans();

            //保存支持信息
            $rest = M('MemberSupport')->add($supportInfo);
            if(!$rest){
                M()->rollback();
                $this->ajaxReturn(['msg'=>"订单保存失败",'status'=>0]);
            }

            // 更新用户余额
            $rest = M('Member')->where(['id' => $member_id])->save(['money' => ['exp', 'money-' . $support_money]]);
            if(!$rest){
                M()->rollback();
                $this->ajaxReturn(['msg'=>"订单保存失败",'status'=>0]);
            }

            $rest = M('Member')->where(['id' => $member_id])->save(['all_support_money' => ['exp', 'all_support_money+' . $support_money]]);

            if(!$rest){
                M()->rollback();
                $this->ajaxReturn(['msg'=>"订单保存失败",'status'=>0]);
            }

            // 更新支持人数 //支持金额
            $rest = M('Project')
                ->where(['id'=>$projectInfo['id']])
                ->save([
                    'support_number' => $projectInfo['support_number']+1,
                    'money' => $projectInfo['money']+$support_money,
                ]);
            if(!$rest){
                M()->rollback();
                $this->ajaxReturn(['msg'=>"订单保存失败",'status'=>0]);
            }

            //提交事物
            M()->commit();
            $this->ajaxReturn(['msg'=>"支持成功",'status'=>1]);
        }
    }
//收藏
    public function collect(){
        if(IS_POST && IS_AJAX){
            //判断会员是否已经登录
            if(!$this->isLogin){
                $this->ajaxReturn(['msg'=>"对不起，您还没有登录",'status'=>0]);
            }
            $project_id = intval(I('post.project_id'));
            $projectInfo = M('Project')->find($project_id);

            if(!$projectInfo){
                $this->ajaxReturn(['msg'=>"非法项目",'status'=>2]);
            }

            $model = M('MemberCollection');

            //判断用户是否已经收藏该项目
            $info = $model->where(['member_id'=>$this->userInfo['id'],
                'project_id'=>$projectInfo['id']])->find();
            if($info){
                $this->ajaxReturn(['msg'=>"您已收藏该项目",'status'=>2]);
            }
            //收藏
            $data = [
                'member_id'=>$this->userInfo['id'],
                'project_id'=>$projectInfo['id'],
                'create_time'=>time(),
            ];

            $id = $model->add($data);
            if($id===false){
                $this->ajaxReturn(['msg'=>"收藏失败",'status'=>2]);
            }

            //更新项目收藏人数
            $rest = M('Project')->where(['id'=>$projectInfo['id']])->save(['collection_number'=>$projectInfo['collection_number']+1]);
            if($rest === false){
                $this->ajaxReturn(['msg'=>"收藏失败",'status'=>2]);
            }

            $this->ajaxReturn(['msg'=>"收藏成功",'status'=>1,'info'=>$projectInfo['collection_number']+1]);

        }
    }

    public function search(){
        $info = I('get.text','','strip_tags');
        $where = [];
        if($info){
            $where['name'] = ['like',"%$info%"];;
        }

        $model = M('project');
        $searchInfos = $model
            ->where([
                'name' => ['like',"%$info%"],
                'recommend' => 1,
                'end_time'   => [['egt', time()], '0', 'or'],// 结束时间 大于等于当前时间 或 为0
                'start_time' => ['elt', time()],// 开始时间 小于等于当前时间
                'is_active'      => 1,
            ])
            ->select();
        $this->assign('searchInfos',$searchInfos);
        $this->assign('searchno',$info);
        $this->display('index/search');
    }

    public function news($id){
        $id = intval($id);
        $new = M('Article')->find($id);
        $this->assign('new',$new);
        $this->display('index/news');
    }

    public function agreement(){
        $this->display('index/agreement');
    }

}
