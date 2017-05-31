<?php
    namespace Admin\Controller;


    /**
     * 空控制器
     * 
     * Class EmptyController
     * @package Wap\Controller
     */
    class EmptyController extends CommonController
    {
        public function index(){
            $this->display('Error:404');
        }

        public function _empty(){
            $this->display('Error:404');
        }

    }