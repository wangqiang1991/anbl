<?php
namespace Admin\Controller;
use Think\Upload;
use Think\Controller;

class PageConfigController extends  CommonController{

    public function _initialize(){
        parent::_initialize();
        // 检测用户是否登录，没有登录不能继续执行
        if(!$this->isLogin){
            $this->redirect('admin/login/index');
            exit;
        }
    }

    /**
     * 优秀作品
     */
    public function Select(){

        $paramArr = $_REQUEST;

        $list = M('Works')->order('create_time desc')->select();

        if(isset($paramArr['pgNum']) && !empty($paramArr['pgNum']) && $paramArr['pgNum'] < 1000 && is_numeric($paramArr['pgNum'])){
            $pgNum = $paramArr['pgNum'];
        }else{
            $pgNum = 1;
        }
        if(isset($paramArr['pgSize']) && !empty($paramArr['pgSize']) && $paramArr['pgSize'] < 30 && is_numeric($paramArr['pgSize'])){
            $pgSize = $paramArr['pgSize'];
        }else{
            $pgSize = 20;
        }

        if(!empty($list)){

            $count = ceil(count($list)/20);

            $worksList = $this->pagination($list,$pgNum,$pgSize);


        }

        if(IS_AJAX){
            $this->ajaxReturn([
                'data'=>array_values($worksList),
                'pages'=>$count,
                'status'=>1
            ]);
            exit;
        }

        $this->assign([
            'list'=>$worksList,
            'count'=>$count
        ]);
        $this->display('page/index');

    }

    /**
     * 优秀导演
     */
    public function Director(){

        $paramArr = $_REQUEST;

        $list = M('Director')->order('create_time desc')->select();

        if(isset($paramArr['pgNum']) && !empty($paramArr['pgNum']) && $paramArr['pgNum'] < 1000 && is_numeric($paramArr['pgNum'])){
            $pgNum = $paramArr['pgNum'];
        }else{
            $pgNum = 1;
        }
        if(isset($paramArr['pgSize']) && !empty($paramArr['pgSize']) && $paramArr['pgSize'] < 30 && is_numeric($paramArr['pgSize'])){
            $pgSize = $paramArr['pgSize'];
        }else{
            $pgSize = 20;
        }

        if(!empty($list)){

            $count = ceil(count($list)/20);

            $worksList = $this->pagination($list,$pgNum,$pgSize);


        }
        if(IS_AJAX){
            $this->ajaxReturn([
                'data'=>array_values($worksList),
                'pages'=>$count,
                'status'=>1
            ]);
            exit;
        }

        $this->assign([
            'list'=>$worksList,
            'count'=>$count
        ]);
        $this->display('director/index');

    }

    /**
     * 导演详情
     */
    public function Info(){

        $paramArr = $_REQUEST;

        if(isset($paramArr['id']) && !empty($paramArr['id']) && is_numeric($paramArr['id'])){

            $row = M('Director')->where(['id'=>$paramArr['id']])->find();
            if(!empty($row)){

                $this->assign('director',$row);
            }else{

                return false;
            }
        }
        $this->display('director/detail');
    }

    /**
     * 保存导演
     */
    public function saved(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){

            $data = [
                'name'=>$paramArr['name'],
                'intro'=>$paramArr['intro'],
                'address'=>$paramArr['address'],
                'sort'=>$paramArr['sort'],
                'last_time'=>time(),
                'fans_number'=>$paramArr['fans_number'],
                'vote_number'=>$paramArr['vote_number'],
                'is_active'=>$paramArr['is_active'],
                'image_url'=>$paramArr['image_url']?$paramArr['image_url']:$paramArr['img_url'],
            ];

            M('Director')->where(['id'=>$paramArr['id']])->save($data);
        }
    }

    /**
     * 删除导演
     */
    public function delRector(){

        $paramArr = $_REQUEST;

        if(isset($paramArr['id']) && is_numeric($paramArr['id']) && !empty($paramArr['id'])){

            $res = M('Director')->where(['id'=>$paramArr['id']])->delete();
            if($res){
                $this->ajaxReturn([
                    'status'=>1,
                ]);
            }
        }else{
            $this->ajaxReturn([
                'status'=>0
            ]);
        }
    }
    /**
     * 添加导演
     */
    public function addRector(){

        if(IS_POST){
            $paramArr = $_REQUEST;
            if(!empty($paramArr)){

                $insertData = [
                    'name'=>$paramArr['name'],
                    'address'=>$paramArr['address'],
                    'intro'=>$paramArr['intro'],
                    'fans_number'=>$paramArr['fans_number'],
                    'vote_number'=>$paramArr['vote_number'],
                    'create_time'=>time(),
                    'image_url'=>$paramArr['image_url'],
                    'sort'=>$paramArr['sort'],
                    'is_active'=>$paramArr['is_active'],
                ];
                M('Director')->add($insertData);
            }
        }
        $this->display('director/add');

    }

    /**
     * 添加作品
     */
    public function add(){

        if(IS_POST){
            $paramArr = $_REQUEST;

            if(!empty($paramArr)){

                $insertData = [
                    'name'=>$paramArr['name'],
                    'img_url'=>$paramArr['image_url'],
                    'sort'=>$paramArr['sort'],
                    'vote_number'=>$paramArr['vote_number'],
                    'intro'=>$paramArr['intro'],
                    'is_active'=>$paramArr['is_active'],
                    'create_time'=>time(),
                ];
                $type = '';
                foreach($paramArr['type'] as $key => $value){
                    if($key == count($paramArr['type']) - 1){
                        $type .= $value;
                    }else{
                        $type .= $value.'/';
                    }
                }
                $insertData['type'] = $type;

                $res = M('Works')->add($insertData);
                if($res){
                    $this->ajaxReturn([
                        'status'=>1,
                        'msg'=>'添加成功!'
                    ]);
                }

            }
            exit;
        }

        $this->display('page/add');
    }

    /**
     * 删除作品
     */
    public function delete(){

        $paramArr = $_REQUEST;

        if(isset($paramArr['id']) && is_numeric($paramArr['id']) && !empty($paramArr['id'])){

            $res = M('Works')->where(['id'=>$paramArr['id']])->delete();
            if($res){
                $this->ajaxReturn([
                    'status'=>1,
                ]);
            }
        }else{
            $this->ajaxReturn([
                'status'=>0
            ]);
        }
    }

    /**
     * 作品详情
     */
    public function detail(){

        $paramArr = $_REQUEST;

        if(isset($paramArr['id']) && !empty($paramArr['id']) && is_numeric($paramArr['id'])){

            $row = M('Works')->where(['id'=>$paramArr['id']])->find();
            if(!empty($row)){

                $this->assign('work',$row);

            }
        }
        $this->display('page/detail');

    }

    /**
     * 保存作品
     */
    public function save(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){

            $data = [
                'name'=>$paramArr['name'],
                'type'=>$paramArr['type'],
                'fans_number'=>$paramArr['fans_number'],
                'vote_number'=>$paramArr['vote_number'],
                'is_active'=>$paramArr['is_active'],
                'img_url'=>$paramArr['image_url']?$paramArr['image_url']:$paramArr['img_url'],
                'intro'=>$paramArr['intro'],
            ];
            M('Works')->where(['id'=>$paramArr['id']])->save($data);
        }
    }

    /**
     * 分页工具
     */
    public function pagination($data = [],$pgNum = '',$pgSize = ''){

        if(empty($data)){

            return false;

        }
        $start = ($pgNum - 1) * $pgSize;

        $sliceArr = array_slice($data,$start,$pgSize);

        return $sliceArr;
    }

    /**
     * 图片上传
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
}