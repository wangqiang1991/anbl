<?php
namespace Home\Controller;

use Think\Controller;

class ConsultController extends CommonController{

    /**
     *
     */
    public function add(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){

            //>> 将提交的内容反编译
            $paramArr['title'] = htmlspecialchars($paramArr['title']);
            $paramArr['content'] = htmlspecialchars($paramArr['content']);

            $paramArr['title'] = addslashes($paramArr['title']);
            $paramArr['content'] = addslashes($paramArr['content']);


            //>> 正则过滤
            $paramArr['title'] = preg_replace("/<(.*?)>/","",$paramArr['title']);

            $paramArr['content'] = preg_replace("/<(.*?)>/","",$paramArr['content']);

            $insertData = [
                'title'=>$paramArr['title'],
                'content'=>$paramArr['content'],
                'status'=>0,
                'image_url'=>$paramArr['image_url'],
                'create_time'=>time(),
                'member_id'=>$this->userInfo['id'],
                'reply'=>'',
            ];

            $res = M('MemberConsult')->add($insertData);
            if($res){

                $this->ajaxReturn([
                    'status'=>1
                ]);
            }else{

                $this->ajaxReturn([
                    'status'=>0
                ]);
            }
        }else{

            $this->ajaxReturn([
                'status'=>0
            ]);
        }
    }
}