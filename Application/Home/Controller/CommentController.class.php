<?php
namespace Home\Controller;

use Think\Controller;

class CommentController extends CommonController{

    /**
     * 添加评论
     */
    public function add(){
        
        $paramArr = $_REQUEST;

        if(!empty($paramArr)){
            if(isset($paramArr['content']) && !empty($paramArr['content'])){
                $insertData = [
                    'content'=>$paramArr['content'],
                    'username'=>$this->userInfo['username'],
                    'user_id'=>$this->userInfo['id'],
                    'movie_id'=>$paramArr['movie_id'],
                    'is_pass'=>0,
                    'create_time'=>time(),
                    'type'=>$paramArr['type'] ? $paramArr['type'] : 1,
                ];
                $res = M('Comment')->add($insertData);
                if($res){
                    die($this->_printSuccess(['list'=>$insertData['content'],'username'=>telephoneNumber($this->userInfo['username'])]));
                }else{
                    die($this->_printError('1036'));
                }
            }
        }else{
            die($this->_printError(''));
        }
    }

    /**
     * 获取评论
     */
    public function get(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){

            if(isset($paramArr['movie_id']) && !empty($paramArr['movie_id']) && is_numeric($paramArr['movie_id'])){

                if(isset($paramArr['type']) && !empty($paramArr['type']) && is_numeric($paramArr['type'])){
                    //>> 查询所有评论
                    $rows = M('Comment')->where(['movie_id'=>$paramArr['movie_id'],'type'=>$paramArr['type'],'is_pass'=>1])->select();

                    if(!empty($rows)){

                        $pgNum = $paramArr['pgNum'] ? $paramArr['pgNum'] : 1;

                        $pgSize = $paramArr['pgSize'] ? $paramArr['pgSize'] : 12;

                        $list = $this->pagination($rows,$pgNum,$pgSize);

                        die($this->_printSuccess(['data'=>array_values($list)]));

                    }else{

                        die($this->_printError(''));
                    }
                }else{

                    die($this->_printError(''));
                }
            }else{

                die($this->_printError(''));
            }
        }else{

            die($this->_printError(''));
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
}