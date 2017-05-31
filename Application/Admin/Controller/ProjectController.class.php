<?php
namespace Admin\Controller;

use Think\Page;
use Think\Upload;

class ProjectController extends CommonController
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
        $type_id = intval(I('get.type_id'));
        // 创建查询条件
        $where =[];
        if($name){
            $where['a.name'] = ['like',"%$name%"];
        }
        if($type_id){
            $where['a.type_id'] = $type_id;
        }
        // 查询总记录数
        $count =  M('project as a')
            ->field('a.*, b.story,c.name as type')
            ->join('LEFT JOIN an_project_survey as b ON a.id=b.project_id')
            ->join('LEFT JOIN an_project_category as c ON c.id=a.type_id')
            ->where($where)
            ->count();

        // 实列化一个分页工具类
        $page = new Page($count,10);

        $rows = M('project as a')
            ->field('a.*, b.story,c.name as type')
            ->join('LEFT JOIN an_project_survey as b ON a.id=b.project_id')
            ->join('LEFT JOIN an_project_category as c ON c.id=a.type_id')
            ->where($where)
            ->limit($page->firstRow, $page->listRows)
            ->order('id')
            ->select();
        // 生成分页DOM结构
        $pages = $page->show();
        // 向模板分配分页条
        $this->assign('pages',$pages);

        // 查询出所有分类
        $types = M('ProjectCategory')->select();
        $this->assign('types',$types);
        $this->assign('rows',$rows);
        $this->display('project/index');
    }

    public function add(){
        if(IS_POST && IS_AJAX){
            // 获取数据
            $_data = i('post.');

            // 判断开始时间是否比大结束时间
            if ($_data['start_time'] >= $_data['end_time']) {
                $this->ajaxReturnError('开始时间不能大于或等于结束时间',__LINE__);
            }
            // 判断是否上传封面图
            if(!$_data['image_url']){
                $this->ajaxReturnError('影片封面图必须上传',__LINE__);
            }
            if(!$_data['analysis']){
                $this->ajaxReturnError('剧情简介必须填写',__LINE__);
            }

            // 获取项目基本信息
            $projectInfo = [
                'name' => $_data['name'],
                'target_amount' => $_data['target_amount'],
                'director' => $_data['director'],
                'star' => $_data['star'],
                'company' => $_data['company'],
                'cycle' => $_data['cycle'],
                'country' => $_data['country'],
                'englishname' => $_data['englishname'],
                'showaddress' => $_data['showaddress'],
                'showtime' => strtotime($_data['showtime']),
                'duration' => $_data['duration'],
                'star_num' => $_data['star_num']>5?5:$_data['star_num'],
                'title' => $_data['title'],
                'mode' => $_data['mode'],
                'intro' => $_data['intro'],
                'start_time' => strtotime($_data['start_time']),
                'end_time' => strtotime($_data['end_time']),
                'exp_date' => strtotime($_data['exp_date']),
                'exp_cycle' => $_data['exp_cycle'],
                'sort' => $_data['sort'],
                'is_active' => $_data['is_active'],
                'type_id' => intval($_data['type_id']),
               // 'url' => $_data['url'],
                'image_url' => $_data['image_url'],
                'create_time' => time(),
                //'fixed_rate'=>$_data['fixed_rate'],
               // 'float_rate'=>$_data['float_rate_1'],
            ];

            // 开启事物
            M()->startTrans();
            // 将项目基本信息保存到数据库 an_project表
            $_id = M('project')->add($projectInfo);
            if($_id === false){
                M()->rollback();
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }

            // 商品概况表
            $survey = [
                'project_id' => $_id,
                'expected_return' => $_data['expected_return'],
                'story' => $_data['story'],
                'analysis' => $_data['analysis'],
                'film_critic' => $_data['film_critic'],
                'create_time' => time(),
            ];

            $result = M('project_survey')->add($survey);
            if($result === false){
                M()->rollback();
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }

            // 获取收益预测
            $priceTimes = $_data['priceTimes'];
            $prices = $_data['prices'];
            // 存储所有价格数据的变量
            $price = [];
            // 循环处理数据 判断是否合法
            foreach ($priceTimes as $key => $priceTime) {
                // 判断均价日期是否为空
                if ($priceTime == '') {
                    M()->rollback();
                    $this->Msg['msg'] = '添加失败，请将预测收益填写完整';
                    $this->Msg['error_code'] = __LINE__;
                    $this->ajaxReturn($this->Msg);
                }
                // 判断均价日期是否为空
                if ($prices[$key] == '') {
                    M()->rollback();
                    $this->Msg['msg'] = '添加失败，请将预测收益填写完整';
                    $this->Msg['error_code'] = __LINE__;
                    $this->ajaxReturn($this->Msg);
                }
                // 判断是否有一样的 日期
                if (isset($price[$priceTime]) == true) {
                    M()->rollback();
                    $this->Msg['msg'] = '添加失败，投资额不能一致';
                    $this->Msg['error_code'] = __LINE__;
                    $this->ajaxReturn($this->Msg);
                }
                // 添加到所有价格趋势变量中
                $price[$priceTime] = $prices[$key];
            }

            if (empty($price)) {
                M()->rollback();
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }


            // 获取价格走势值
            $priceData = [
                'project_id'  => $_id,
                'priceTimes'  => json_encode($price),//价格走势图数据 json
                'create_time' => time(),//创建时间
            ];
            $rst = M('ProjectPrice')->add($priceData);
            if (!$rst) {
                M()->rollback();
                $this->ajaxReturnError('数据保存失败',__LINE__);
            }



            // 提交事物
            M()->commit();
            $this->ajaxReturn(['msg'=>'数据保存成功', 'status'=>1,]);

        }
        //查询出所有项目分类
        $ProjectCategorys = M('ProjectCategory')->select();
        $this->assign('ProjectCategorys',$ProjectCategorys);
        $this->display('project/add');
    }

    public function edit($id){
        $id = intval($id);
        $projectModel = D('Project');

        // 获取项目数据
        $projectInfo = $projectModel->find($id);

        // 判断数据是否存在
        if (!$projectInfo) {
            $this->Msg['msg'] = "项目不存在";
            $this->ajaxReturn($this->Msg);
        }
        if (IS_POST && IS_AJAX) {
            // 获取数据
            $_data = i('post.');

            // 判断开始时间是否比大结束时间
            if ($_data['start_time'] >= $_data['end_time']) {
                $this->ajaxReturnError('开始时间不能大于或等于结束时间',__LINE__);
            }

            // 获取项目数据
            $projectInfo = $projectModel->find($_data['id']);

            // 判断数据是否存在
            if (!$projectInfo) {
                $this->ajaxReturnError('项目不存在',__LINE__);
            }

            // 获取项目基本信息
            $projectInfo = [
                'name' => $_data['name'],
                'target_amount' => $_data['target_amount'],
                'director' => $_data['director'],
                'star' => $_data['star'],
                'company' => $_data['company'],
                'cycle' => $_data['cycle'],
                'country' => $_data['country'],
                'englishname' => $_data['englishname'],
                'showaddress' => $_data['showaddress'],
                'showtime' => strtotime($_data['showtime']),
                'duration' => $_data['duration'],
                'speed' => $_data['speed'],
                'star_num' => $_data['star_num']>5?5:$_data['star_num'],
                'title' => $_data['title'],
                'mode' => $_data['mode'],
                'intro' => $_data['intro'],
                'start_time' => strtotime($_data['start_time']),
                'end_time' => strtotime($_data['end_time']),
                'exp_date' => strtotime($_data['exp_date']),
                'exp_cycle' => $_data['exp_cycle'],
                'sort' => $_data['sort'],
                'is_active' => $_data['is_active'],
                'type_id' => intval($_data['type_id']),
               // 'url' => $_data['url'],
                'image_url' => $_data['image_url'],
            ];
            // 开启事物
            M()->startTrans();
            // 将项目基本信息保存到数据库 an_project表
            $_id = M('project')
                ->where(['id'=>$_data['id']])
                ->save($projectInfo);

            if($_id === false){
                M()->rollback();
                $this->ajaxReturnError('数据更新失败',__LINE__);
            }

            // 商品概况表
            $survey = [
                'expected_return' => $_data['expected_return'],
                'story' => $_data['story'],
                'analysis' => $_data['analysis'],
                'film_critic' => $_data['film_critic'],
            ];

            $result = M('project_survey')
                ->where(['project_id'=>$_data['id']])
                ->save($survey);

            if($result === false){
                M()->rollback();
                $this->ajaxReturnError('数据更新失败',__LINE__);
            }

            // 收益预测数据分析
            $priceTimes = $_data['priceTimes'];

            $prices = $_data['prices'];
            // 存储所有价格数据的变量
            $price = [];
            // 循环处理数据 判断是否合法
            foreach ($priceTimes as $key => $priceTime) {
                // 判断均价日期是否为空
                if ($priceTime == '') {
                    M()->rollback();
                    $this->ajaxReturnError('添加失败，请将预测收益填写完整',__LINE__);
                }
                // 判断均价日期是否为空
                if ($prices[$key] == '') {
                    M()->rollback();
                    $this->ajaxReturnError('添加失败，请将预测收益填写完整',__LINE__);
                }
                // 判断是否有一样的 投资
                if (isset($price[$priceTime]) == true) {
                    M()->rollback();
                    $this->ajaxReturnError('添加失败，重复投资额度',__LINE__);
                }
                // 添加到所有价格趋势变量中
                $price[$priceTime] = $prices[$key];
            }

            if (empty($price)) {
                M()->rollback();
                $this->ajaxReturnError('添加失败，请将预测收益填写完整',__LINE__);
            }


            // 获取价格走势值
            $priceData = [
                'project_id' => $_data['id'],
                'priceTimes' => json_encode($price),//价格走势图数据 json
            ];


            $projectPrice = M('ProjectPrice')->where(['project_id' => $_data['id']])->find();

            if ($projectPrice) {
                $rst = M('ProjectPrice')->where(['project_id' => $_data['id']])->save($priceData);

            } else {
                $priceData['create_time'] = time();
                // 添加楼盘价格趋势到数据库中
                $rst = M('ProjectPrice')->add($priceData);
            }
            if ($rst === false) {
                M()->rollback();
                $this->ajaxReturnError('编辑失败，请联系管理员 错误代码',__LINE__);
            }

            // 提交事物
            M()->commit();
            $this->ajaxReturn(['msg'=>'数据更新成功', 'status'=>1,]);

        }

        // 获取项目概况
        $surveys = M('ProjectSurvey')
            ->where(['project_id'=>$projectInfo['id']])
            ->find();

        // 获取项目分类
        $types = M('ProjectCategory')->select();


        // 获取价格走势值
        $projectPrice = M('ProjectPrice')
            ->where([
                'project_id' => $projectInfo['id']
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


        $this->assign('projectPrice', $projectPrice);

        $this->assign('projectInfo',$projectInfo);
        $this->assign('surveys',$surveys);
        $this->assign('types',$types);
        $this->display('project/edit');
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
        $model = D('Project');
        // 通过ID主键 查询标签信息
        $info = $model->find($id);
        if(!$info){
            // 没有在数据库中找到数据，报错
            $this->error('没有找到数据');
            exit;
        }

        // 判断该项目是否存在还没有处理完的订单
        $supportInfo = M('MemberSupport')->where(['is_ok'=>0,'project_id'=>$info['id']])->select();
        if($supportInfo){
            $this->error('该项目还存在未处理完成的订单，不能删除!');
            exit;
        }

        // 执行删除
        $res = $model->delete($id);
        if(!$res){
            $this->error('删除失败！');
            exit;
        }

        // 删除相关订单信息
        $rest = M('MemberSupport')->where(['project_id'=>$info['id']])->delete();
        if(!$rest){
            $this->error('删除失败！');
            exit;
        }

        // 删除收益信息
        foreach($supportInfo as $info){
            $rest = M('MemberProfit')->where(['support_id'=>$info['id']])->delete();
            if(!$rest){
                $this->error('删除失败！');
                exit;
            }
        }

        // 删除关联表数据
        $res = M('ProjectSurvey')
            ->where(['project_id'=>$id])
            ->delete();
        if($res===false){
            $this->error('删除失败！');
            exit;
        }
        // 删除成功直接回到首页
        $this->redirect('admin/project/index');
    }

    /**
     * 图片文件上传
     */
    public function upload(){
        $config = [
            'exts'          =>  array('jpg','png','gif','bmp'), //允许上传的文件后缀
            'subName'       =>  array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath'      =>  'Upload/', //保存根路径
        ];
        $upload = new Upload($config);
        $rst = $upload->uploadOne(array_shift($_FILES));
        // 判断是否上传成功
        if($rst == false){
            $this->Msg['msg'] = $upload->getError();
            $this->ajaxReturn($this->Msg);
        }
        if(!$rst){
            $this->ajaxReturn([
                'status' => 0,
                'msg' => '文件上传失败'
            ]);
        }

        $this->ajaxReturn([
            'status' => 1,
            'url' => $rst['url']
        ]);
    }


    public function upToken(){
        require './ThinkPHP/Library/Vendor/Qiniu/autoload.php';
        // 用于签名的公钥和私钥
        $accessKey = '6j17s5J33oPPMOEtrN3cSQW-W4VEc0Ssu6dbIu5N';
        $secretKey = '9vO9LtdMzZlJLdRVuZ9J4vBj5kyuZhwQ5bP8jCO8';
        // 初始化签权对象
        $auth = new \Qiniu\Auth($accessKey, $secretKey);
        $bucket = 'k1jia';
        // 生成上传Token
        $token = $auth->uploadToken($bucket,null,600);
        $this->ajaxReturn(['uptoken'=>$token]);
    }

    /**
     * 切换是否上架
     * @param $id 项目id
     */
    public function state($id)
    {
        $projectModel = D('Project');

        // 获取项目数据
        $projectInfo = $projectModel->find($id);

        // 判断数据是否存在
        if (!$projectInfo) {
            $this->Msg['msg'] = "项目不存在";
            $this->ajaxReturn($this->Msg);
        }


        // 判断项目是否已上架
        if ($projectInfo['is_active'] == 1) {
            $projectInfo['is_active'] = 0;
        } else {
            $projectInfo['is_active'] = 1;
        }

        // 保存项目数据
        $rst = $projectModel->save($projectInfo);
        if ($rst === false) {
            $this->Msg['msg'] = "修改失败，请重试";
            $this->ajaxReturn($this->Msg);
        } else {
            $this->Msg['msg'] = "修改成功";
            $this->Msg['status'] = 1;
            $this->Msg['value'] = $projectInfo['is_active'];
            $this->ajaxReturn($this->Msg);
        }

    }

    /**
     * 自定义排序
     * @param $id 项目id
     */
    public function saveSort($id)
    {
        $id = intval($id);
        $sort = I('post.sort') ? I('post.sort') : 50;

        $projectModel = D('Project');

        // 获取项目数据
        $projectInfo = $projectModel->find($id);

        // 判断数据是否存在
        if (!$projectInfo) {
            $this->Msg['msg'] = "项目不存在";
            $this->ajaxReturn($this->Msg);
        }
        $projectInfo['sort'] = $sort;
        // 保存项目数据
        $rst = $projectModel->save($projectInfo);
        if ($rst === false) {
            $this->Msg['msg'] = "修改失败，请重试";
            $this->ajaxReturn($this->Msg);
        } else {
            $this->Msg['msg'] = "修改成功";
            $this->Msg['status'] = 1;
            $this->Msg['value'] = $projectInfo['sort'];
            $this->ajaxReturn($this->Msg);
        }
    }

    /**
     * 是否推荐到首页
     * @param $id
     */
    public function recommend($id)
    {
        $projectModel = D('Project');

        // 获取项目数据
        $projectInfo = $projectModel->find($id);

        // 判断数据是否存在
        if (!$projectInfo) {
            $this->Msg['msg'] = "项目不存在";
            $this->ajaxReturn($this->Msg);
        }


        // 判断项目是否已上架
        if ($projectInfo['recommend'] == 1) {
            $projectInfo['recommend'] = 0;
        } else {
            $projectInfo['recommend'] = 1;
        }

        // 保存项目数据
        $rst = $projectModel->save($projectInfo);
        if ($rst === false) {
            $this->Msg['msg'] = "修改失败，请重试";
            $this->ajaxReturn($this->Msg);
        } else {
            $this->Msg['msg'] = "修改成功";
            $this->Msg['status'] = 1;
            $this->Msg['value'] = $projectInfo['recommend'];
            $this->ajaxReturn($this->Msg);
        }

    }

    /**
     * 分红比例
     */
    public function rate(){

        $paramArr = $_REQUEST;

        $row = M('Project')->where(['id'=>$paramArr['id']])->find();
        $this->assign(['row'=>$row,'id'=>$paramArr['id']]);
        $this->display('project/rate');
    }

    public function saveRate(){

        $paramArr = $_REQUEST;

       $res =  M('Project')
           ->where(['id'=>$paramArr['id']])
           ->save([
               'fixed_rate'=>$paramArr['fixed_rate'],
               'float_rate'=>$paramArr['float_rate'],
               'first_rate'=>$paramArr['first_rate'],
               'two_rate'=>$paramArr['two_rate'],
               'three_rate'=>$paramArr['three_rate'],
           ]);

        $this->ajaxReturn([
            'status'=>1
        ]);
    }
}