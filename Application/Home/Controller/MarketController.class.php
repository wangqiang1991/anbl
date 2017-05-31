<?php
namespace Home\Controller;

// 电影超市
class MarketController extends CommonController
{
    public function index(){

        //>> 判断是否登录
        if($this->isLogin == 0){

            $this->redirect('Home/login/index');
            exit;
        }

        $model = M('project');
        //查询所有上线项目
        $where[] = [
            'end_time'   => [['egt', time()], '0', 'or'],// 结束时间 大于等于当前时间 或 为0
            'start_time' => ['elt', time()],// 开始时间 小于等于当前时间
            'is_active'      => 1,//上架状态
        ];
        $projectInfo = $model
            ->where($where)
            ->select();
        $this->assign('projectInfo',$projectInfo);
        $this->display('market/index');
    }

    /**
     * 检查用户权限
     */
    public function test(){
        if(IS_POST && IS_AJAX) {
            // 接收传递参数 文件地址 文件id
            $data = I('post.');

            // 判断用户是否已经登录
            if (!$this->isLogin) {
                $this->ajaxReturn(['msg'=>"对不起，您还没有登录",'status'=>0]);
            }
            $user_id = $this->userInfo['id'];
            $project_id = $data['project_id'];

            //查看电影是否存在
            $projectInfo = M('Project')->find($project_id);
            if (!$projectInfo) {
                $this->ajaxReturn(['msg' => "项目不存在", 'status' => 0]);
            }
            // 验证用户权限

            $rest = M('MemberSupport')
                ->where(['member_id' => $user_id, 'project_id' => $projectInfo['id']])
                ->find();

            if ($rest) {
                $this->ajaxReturn(['msg' => "您已支持该项目可免费下载", 'status' => 1,'info'=>['dl'=>"您已支持该项目可免费下载"],'id'=>$projectInfo['id'],'infot'=>$projectInfo]);
            } else {
                //判断用户余额
                if($projectInfo['dl']>$this->userInfo['money']){
                    $this->ajaxReturn(['msg'=>"对不起，积分不足",'status'=>0]);
                }
                $this->ajaxReturn(['msg' => "付费下载", 'status' => 2,'info'=>$projectInfo]);
            }
        }
    }

    public function download(){
            // 接收传递参数 文件地址 文件id
            $data = I('post.');
            // 根据项目id 查询出视频地址
            $project_id = $data['project_id'];
            //查看电影是否存在
            $projectInfo = M('Project')->find($project_id);
            if (!$projectInfo) {
                $this->ajaxReturn(['msg' => "项目不存在", 'status' => 0]);
            }
            $type_id = $data['type_id'];

            //判断下载类型 2 扣费
            if($type_id == 2){
                if($projectInfo['dl']>$this->userInfo['money']){
                    $this->error("对不起，余额不足");
                    exit;
                }
                $rest = M('Member')->where(['id'=>$this->userInfo['id']])->save(['money'=>$this->userInfo['money']-$projectInfo['dl']]);
                if(!$rest){
                    $this->error("订单保存失败");
                }
                // 生成订单
                $order_number = 'XZ'.date('Ymd') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
                //保存到下载详情表
                $downloadInfo1 = [
                    'member_id'=>$this->userInfo['id'],
                    'project_id'=>$projectInfo['id'],
                    'order_number'=>$order_number,
                    'money'=>$projectInfo['dl'],//下载花费金额
                    'create_time'=>time(),
                ];
                $rest = M('MemberDownload')->add($downloadInfo1);
                if(!$rest){
                    $this->error("订单生成失败");
                    exit;
                }

            }else{
                // 生成订单
                $order_number = 'XZ'.date('Ymd') . str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
                //保存到下载详情表 免费下载
                $downloadInfo2 = [
                    'member_id'=>$this->userInfo['id'],
                    'project_id'=>$projectInfo['id'],
                    'order_number'=>$order_number,
                    'create_time'=>time(),
                ];
                $rest = M('MemberDownload')->add($downloadInfo2);
                if(!$rest){
                    $this->error("订单生成失败");
                    exit;
                }
            }
        // 添加下载信息
        $collect = M('MemberCollection')->where(['member_id'=>$this->userInfo['id'],'project_id'=>$projectInfo['id']])->find();
        if($collect){
            //更新字段
            M('MemberCollection')->where(['member_id'=>$this->userInfo['id'],'project_id'=>$projectInfo['id']])->save(['is_download'=>1]);
        }

      /*  $res_filepath = $projectInfo['url'];//文件地址
        $file_basename = basename($res_filepath);
        $file_filesize = filesize($res_filepath);
        $file = fopen($res_filepath, "r");

        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . $file_filesize);
        Header("Content-Disposition: attachment; filename=" . $file_basename);
        echo fread($file, $file_filesize);
        fclose($file);
        exit;*/
    }


}