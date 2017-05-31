<?php
namespace Admin\Controller;

use Think\Controller;
use Think\Upload;

class CommonController extends Controller
{

    //记录用户登录状态
    public $isLogin = false;

    //当前用户信息
    public $userInfo = [];

    // 系统设置
    public $systemInfo;

    // 系统通知列表 格式：
    public $untreated_list = [];
    /**
     * 初始化
     */
    public function _initialize(){
        // 获取系统设置信息
        $this->getSystemInfo();

        // 修改项目状态
        $this->projectStop();

        // 获取所有待处理消息回复
        $this->getAllFeedback();
        // 获取所有待处理充值
        $this->getAllProject();

        // 获取所有待处理提现
        $this->getAllWithdrawals();
        // 消息栏
        $this->assign('untreated_list', $this->untreated_list);

        //>> 拿session
        $session = session(md5('admin'));
        if(!empty($session)){
            //>> 查询用户
            $row = M('User')->where(['session_token'=>$session])->find();
            if(!empty($row)){
                $this->isLogin = 1;
                $this->userInfo = $row;
                $this->assign('userInfo',$row);
            }
        }else{
            //>> 拿cookie
            $cookie = cookie(md5('admin'));

            if(!empty($cookie)){
                $row = M('User')->where(['remember_token'=>$cookie]);
                if(!empty($row)){

                    $this->isLogin = 1;
                    $this->userInfo = $row;
                    $this->assign('userInfo',$row);
                }
            }
        }

    }

    /**
     * Json返回信息
     * @var array
     */
    protected $Msg = [
        'status'     => 0,
        'msg'        => '',
        'error_code' => 0
    ];


    /**
     * ajaxReturn  返回错误信息
     *
     * @param string $msg 错误信息
     * @param int $error_code 错误代码 默认为0 建议为代码的当前行号  __LINE__
     * @param int $status 错误状态 默认为0
     */

    public function ajaxReturnError($msg, $error_code = 0, $status = 0)
    {
        // 错误信息
        $this->Msg['msg'] = $msg;
        // 错误代码 通常为当前行号
        $this->Msg['error_code'] = $error_code;
        // 错误状态
        $this->Msg['status'] = $status;
        // 返回错误信息
        $this->ajaxReturn($this->Msg);
    }

    /**
     * ajaxReturn  返回正确信息
     *
     * @param string $msg 返回信息
     * @param array $data 返回数据 默认为空
     * @param int $error_code 返回错误代码 默认为0 建议为代码的当前行号  __LINE__
     * @param int $status 返回状态 默认为1
     */

    public function ajaxReturnSuccess( $data = [],$msg = 'success', $status = 1, $error_code = 0)
    {
        // 返回信息
        $this->Msg['msg'] = $msg;
        // 返回数据
        $this->Msg['data'] = $data;
        // 返回代码 通常为当前行号
        $this->Msg['error_code'] = $error_code;
        // 返回状态
        $this->Msg['status'] = $status;
        // 返回信息
        $this->ajaxReturn($this->Msg);
    }

    /**
     * 上传文件
     */

    private function upload()
    {
        $config = [
            'exts'     => array('jpg', 'png', 'gif', 'bmp','jpeg'), //允许上传的文件后缀
            'subName'  => array('date', 'Y-m-d'), //子目录创建方式，[0]-函数名，[1]-参数，多个参数使用数组
            'rootPath' => 'Uploads/', //保存根路径
        ];
        $upload = new Upload($config);
        $rst = $upload->upload($_FILES);
        // 判断是否上传成功
        if($rst == false){
            $this->Msg['msg'] = $upload->getError();
            $this->ajaxReturn($this->Msg);
        }
        $url = [
            'status' => 1
        ];
        // 组合出正确的 url 地址
        foreach ($rst as $item) {
            $url[] = [
                'url' => $item['url']
            ];
        }
        $this->ajaxReturn($url);
    }

    /**
     * +----------------------------------------------------------
     * Export Excel | 2013.08.23
     * Author:HongPing <hongping626@qq.com>
     * +----------------------------------------------------------
     *
     * @param $expTitle     string File name
     * +----------------------------------------------------------
     * @param $expCellName  array  Column name
     * +----------------------------------------------------------
     * @param $expTableData array  Table data
     * +----------------------------------------------------------
     */
    public function exportExcel($expTitle, $expCellName, $expTableData)
    {
        $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
        $fileName = $expTitle . date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        vendor("PHPExcel.PHPExcel");
        $objPHPExcel = new \PHPExcel();
        $cellName = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN', 'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU', 'AV', 'AW', 'AX', 'AY', 'AZ');

        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');//合并单元格

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle . '  导出时间:' . date('Y-m-d H:i:s'));

        for ($i = 0; $i < $cellNum; $i++) {
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
        }
        // Miscellaneous glyphs, UTF-8
        for ($i = 0; $i < $dataNum; $i++) {
            for ($j = 0; $j < $cellNum; $j++) {
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3), $expTableData[$i][$expCellName[$j][0]]);
            }
        }

        header('pragma:public');
        header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
        header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 获取系统配置信息
     */
    public function getSystemInfo()
    {

        // 获取系统设置数据
        $this->systemInfo = M('System')->find(1);

        // 分配到页面中
        $this->assign('systemInfo', $this->systemInfo);
    }


    /**
     * 获取所有未处理事务
     */
    public function getTrans()
    {
        ////////////////////////////////////////////////////////获取动态信息
        $this->untreated_list = [];
            // 获取所有待处理消息回复
            $this->getAllFeedback();
            // 获取所有待处理充值
            $this->getAllProject();

            // 获取所有待处理提现
            $this->getAllWithdrawals();


        // 处理数据
        $string = '';
        foreach ($this->untreated_list as $untreated_info) {
            $string .= '<li class="new"><a href="' . $untreated_info['url'] . '" title="点我立即处理"><span class="label label-danger"><i class="fa fa-bolt"></i></span><span class="name">[' . $untreated_info['type'] . ']' . $untreated_info['content'] . '</span><em class="small"></em></a></li>';
        }
        if ($string == '') {
            $this->Msg['content'] = '<li class="new"><a href="javascript:;"> <span class="name">暂无事务</span></a></li>';
            $this->Msg['num'] = 0;
            $this->ajaxReturn($this->Msg);
        } else {
            $this->Msg['content'] = $string;
            $this->Msg['num'] = count($this->untreated_list) >= 99 ? '100' : count($this->untreated_list);
            $this->Msg['status'] = 1;
            $this->ajaxReturn($this->Msg);
        }

    }

    /**
     * 获取所有待处理提现
     */
    public function getAllWithdrawals()
    {
        $lists = M('MemberCash as a')
            ->field('a.*,b.username as name')
            ->join('left join an_member as b on b.id = a.member_id')
            ->where([
                'a.is_pass' => 0,// 等待处理
            ])
            ->select();


        foreach ($lists as $list) {
            $this->untreated_list[] = [
                'type'    => '提现',
                'url'     => U('admin/order/cash', ['id' => $list['id']]),// 跳转过去的url 地址
                'content' => $list['name'] . ' 等待处理提现',// 显示的内容
            ];
        }
    }

    /**
     * 获取有待充值处理的订单
     */
    public function getAllProject(){
        $lists = M('MemberRecharge as a')
            ->field('a.*,b.username as name')
            ->join('left join an_member as b on b.id = a.member_id')
            ->where([
                'a.is_pass' => 0,// 等待处理
            ])
            ->select();


        foreach ($lists as $list) {
            $this->untreated_list[] = [
                'type'    => '充值',
                'url'     => U('admin/Order/orderRecharge', ['id' => $list['id']]),// 跳转过去的url 地址
                'content' => $list['name'] . ' 等待处理充值',// 显示的内容
            ];
        }
    }

    public function getAllFeedback(){
        $lists = M('MemberConsult as a')
            ->field('a.*,b.username as name')
            ->join('left join an_member as b on b.id = a.member_id')
            ->where([
                'a.status' => 0,// 等待处理
            ])
            ->select();


        foreach ($lists as $list) {
            $this->untreated_list[] = [
                'type'    => '问答',
                'url'     => U('admin/Member/question', ['id' => $list['id']]),// 跳转过去的url 地址
                'content' => $list['name'] . ' 等待处理问答回复',// 显示的内容
            ];
        }
    }


    public function projectStop(){
        $projectInfo = M('Project')->select();
        foreach($projectInfo as $info){
            if(($info['end_time'] < time()) && ($info['is_active'] == 1)){
                // 项目下架 修改项目下架
                // 判断目标金额是否达到
                if($info['target_amount']<=$info['money']){
                    $rest = M('Project')->where(['id'=>$info['id']])->save(['is_active'=>0,'is_ok'=>1]);
                }else{
                    $rest = M('Project')->where(['id'=>$info['id']])->save(['is_active'=>0]);
                }
            }

            // 目标金额达到自动下架
            if($info['target_amount']<=$info['money']){
                $rest = M('Project')->where(['id'=>$info['id']])->save(['is_active'=>0,'is_ok'=>1]);
            }
        }
    }

    public function pagination($data = [],$pgNum,$pgSize){

        if(empty($data))return false;

        $start = ($pgNum - 1) * $pgSize;

        $sliceArr = array_slice($data,$start,$pgSize);

        return $sliceArr;

    }

    /*
 * 空操作
 */
    public function _empty()
    {
        // 显示404 页面
        $this->display('Error:404');

    }

}