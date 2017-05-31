<?php
namespace Admin\Controller;

class RoleController extends CommonController{

    /**
     * 添加电影角色
     */
    public function add(){

        //>> 查询已存在的角色
        $roles = M('ProjectRole')->select();

        $this->assign('roles',$roles);
        $this->display('role/add');
    }

    /**
     * 保存角色
     */
    public function save(){

        $paramArr = $_REQUEST;

        if(!empty($paramArr)){

            //>> 判断是否已经添加过了
            $role = M('ProjectRole')->where(['name'=>$paramArr['role']])->find();
            if(empty($role)){
                $insertData = [
                    'name'=>$paramArr['role'],
                    'create_time'=>time(),
                ];
                $res = M('ProjectRole')->add($insertData);
                if($res){
                    $this->ajaxReturn([
                        'status'=>1,
                        'msg'=>'添加成功'
                    ]);
                }else{
                    $this->ajaxReturn([
                        'status'=>'0',
                        'msg'=>'添加失败'
                    ]);
                }
            }else{
                $this->ajaxReturn([
                    'status'=>0,
                    'msg'=>'你已经添加过该角色'
                ]);
            }
        }else{
            $this->ajaxReturn([
                'status'=>0,
                'msg'=>'添加失败'
            ]);
        }
    }

    /**
     * 删除角色
     */
    public function delete(){
        $paramArr = $_REQUEST;

        if(!empty($paramArr)){

            $res = M('ProjectRole')->where(['id'=>$paramArr['id']])->delete();
            if($res){

                $this->ajaxReturn([
                    'status'=>1,
                    'msg'=>'删除成功'
                ]);
            }else{
                $this->ajaxReturn([
                    'status'=>0,
                    'msg'=>'删除失败'
                ]);
            }
        }else{

            $this->ajaxReturn([
                'status'=>0,
                'msg'=>'删除失败'
            ]);
        }
    }

    /**
     * 招募电影
     */
    public function film(){

        if(IS_POST && IS_AJAX){

            $paramArr = $_REQUEST;

            if(!empty($paramArr)){

                //>> 判断电影是否添加
                $result = M('ProjectRecruit')->where(['name'=>$paramArr['film']])->find();

                if(!empty($result)){
                    $updateData = [
                        'name'=>$paramArr['film'] ? $paramArr['film'] :'',
                        'role_id'=>json_encode(array_unique(array_merge(json_decode($result['role_id']),$paramArr['roles']))),
                        'create_time'=>time(),
                    ];
                    $re = M('ProjectRecruit')->where(['id'=>$result['id']])->save($updateData);

                    //>> 判断是否添加了角色详情
                    $e = M('RoleDescription')->where(['recruit_id'=>$result['id'],'role_id'=>$paramArr['roles'][0]])->find();
                    if(!empty($e)){
                        //>> 如果有这个电影的角色,更新角色描述
                        M('RoleDescription')->where(['role_id'=>$paramArr['roles'][0],'recruit_id'=>$result['id']])->save([
                            'intro'=>$paramArr['intro'] ? $paramArr['intro'] : '',
                            'feature'=>$paramArr['feature'] ? $paramArr['feature'] : '',
                            'figure'=>$paramArr['figure'] ? $paramArr['figure'] : '',
                            'money'=>$paramArr['money'],
                        ]);
                    }else{
                        //>> 如果没有这个电影的角色,添加角色描述
                        $inData = [
                            'intro'=>$paramArr['intro'] ? $paramArr['intro'] : '',
                            'feature'=>$paramArr['feature'] ? $paramArr['feature'] : '',
                            'figure'=>$paramArr['figure'] ? $paramArr['figure'] : '',
                            'role_id'=>$paramArr['roles'][0],
                            'recruit_id'=>$result['id'],
                            'member_id'=>$this->userInfo['id'],
                            'money'=>$paramArr['money'],
                        ];
                        $row = M('RoleDescription')->add($inData);
                    }

                    if($re === false){

                        $this->ajaxReturn([
                            'msg'=>'添加失败',
                            'status'=>0
                        ]);
                    }else{
                        $this->ajaxReturn([
                            'msg'=>'添加成功',
                            'status'=>1
                        ]);
                    }
                }
                $insertData = [
                    'name'=>$paramArr['film'],
                    'image_url'=>$paramArr['image_url'],
                    'role_id'=>json_encode($paramArr['roles']),
                    'create_time'=>time(),
                ];
                $res = M('ProjectRecruit')->add($insertData);
                $lastId = M('ProjectRecruit')->getLastInsID();

                $insData = [
                    'intro'=>$paramArr['intro'] ? $paramArr['intro'] : '',
                    'feature'=>$paramArr['feature'] ? $paramArr['feature'] : '',
                    'figure'=>$paramArr['figure'] ? $paramArr['figure'] : '',
                    'role_id'=>$paramArr['roles'][0],
                    'recruit_id'=>$lastId,
                    'member_id'=>$this->userInfo['id'],
                ];
                //>> 将角色信息单独保存到一张表中
                $row = M('RoleDescription')->add($insData);
                if($res && $row){

                    $this->ajaxReturn([
                        'msg'=>'添加成功',
                        'status'=>1
                    ]);
                }else{

                    $this->ajaxReturn([
                        'msg'=>'添加失败',
                        'status'=>0
                    ]);
                }
            }else{

                $this->ajaxReturn([
                    'status'=>0,
                    'msg'=>'添加失败'
                ]);
            }
        }

        $roles = M('ProjectRole')->select();
        $this->assign('roles',$roles);
        $this->display('role/film');
    }

    /**
     * 招募列表
     */
    public function index(){

        $films = M('ProjectRecruit')->select();
        foreach($films as $key => &$value){
            $value['role_id'] = json_decode($value['role_id']);
            //>> 循环查询角色
            foreach($value['role_id'] as $k => $v){
                $row = M('ProjectRole')->where(['id'=>$v])->find();
                $value['roles'][] = $row['name'];
            }
            $value['roles'] = implode('、',$value['roles']);
        }
        unset($value);

        $this->assign('films',$films);
        $this->display('role/index');
    }

    /**
     * 删除招募
     */
    public function delRecruit(){

        $paramArr = $_REQUEST;
        if(!empty($paramArr)){

            $res = M('ProjectRecruit')->where(['id'=>$paramArr['id']])->delete();
            //>> 删除所有角色
            $re = M('RoleDescription')->where(['recruit_id'=>$paramArr['id']])->delete();
            if($res && $re){

                $this->ajaxReturn([
                    'status'=>1,
                    'msg'=>'删除成功'
                ]);
            }else{

                $this->ajaxReturn([
                    'status'=>0,
                    'msg'=>'删除失败'
                ]);
            }
        }else{

            $this->ajaxReturn([
                'status'=>0,
                'msg'=>'删除失败'
            ]);
        }
    }



}