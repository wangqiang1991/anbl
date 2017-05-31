<?php
namespace Admin\Controller;
use Admin\Logic\DbMysqlLogic;
use Admin\Service\NestedSets;
class PermissionController extends CommonController{


    /**
     * 列表
     */
    public function index(){

        $permission = M('UserPermission')->where(['status'=>1])->order('lft')->select();

        $this->assign('permission',$permission);
        $this->display('permission/index');
}

    public function add(){

        if(IS_POST){

            $paramArr = $_REQUEST;

            if(empty($paramArr)){

                $this->ajaxReturn(['status'=>0]);
            }

            $db = new DbMysqlLogic();
            $ns = new NestedSets($db, 'an_user_permission', 'lft', 'rght', 'parent_id', 'id', 'level');
            $parent_id = $paramArr['parent_id'];
            $data = [
                'name' => $paramArr['name'],
                'intro' => $paramArr['intro'],
                'status' => $paramArr['status'],
                'url'=>$paramArr['url'],
            ];
            $res = $ns->insert($parent_id, $data, 'bottom');
            if($res){
                $this->ajaxReturn(['status'=>1]);
            }else{
                $this->ajaxReturn(['status'=>0]);
            }
        }
        //>> 查询权限
        $lists = M('UserPermission')->where(['status' => 1])->order('lft')->select();
        $data = ['id' => -1, 'name' => '顶级分类', 't' => '', 'pId' => 0];
        array_unshift($lists, $data);
        foreach ($lists as &$v){
            $v['pId'] = $v['parent_id'];
            $v['t'] = $v['intro'];
            $v['open'] = true;
        }
        $lists = json_encode($lists);
        $this->assign('list', $lists);
        $this->display('permission/add');
    }

    public function edit($id){

        $id = intval($id);
        $info = M('UserPermission as a')
            ->field('a.*, b.name parent_name')
            ->join('left join an_user_permission as b on b.id=a.parent_id')
            ->where(['a.id' => $id])
            ->find();

        if(IS_POST){
            $paramArr = $_REQUEST;

            $parent_id = $paramArr['parent_id'];
            $db = new DbMysqlLogic();
            $ns = new NestedSets($db, 'an_user_permission', 'lft', 'rght', 'parent_id', 'id', 'level');

            if($parent_id != $info['parent_id']){
                $res = $ns->moveUnder($paramArr['id'], $parent_id);
            }
            $data['name'] = $paramArr['name'];
            $data['intro'] = $paramArr['intro'];
            $data['url'] = $paramArr['url'];
            $data['status'] = $paramArr['status'] ? $paramArr['status'] : 1;

            $result = M('UserPermission')->where(['id'=>$paramArr['id']])->save($data);

            if($result === false){
                $this->ajaxReturn(['status'=>0]);
            }
            $this->ajaxReturn(['status'=>1]);
        }

        $lists = M('UserPermission')->order('lft')->select();

        $data = ['id' => -1, 'name' => '顶级分类', 't' => '', 'pId' => 0];
        array_unshift($lists, $data);
        foreach ($lists as &$v){
            $v['pId'] = $v['parent_id'];
            $v['t'] = $v['intro'];
            $v['open'] = true;
            if($v['id'] == $info['parent_id']){
                $v['checked'] = true;
            }
        }
        $this->assign('info', $info);
        $this->assign('lists', $lists);
        $this->display('permission/edit');
    }

    /**
     * 删除
     */
    public function delete($id){

        $id = intval($id);

        $db = new DbMysqlLogic();
        $ns = new NestedSets($db, 'an_user_permission', 'lft', 'rght', 'parent_id', 'id', 'level');
        $res = $ns->delete($id);
        if($res){

            $this->redirect('admin/Permission/index');
        }else{
            $this->error('删除失败');
        }

    }
}