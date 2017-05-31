<?php
namespace Admin\MODEL;

use Think\Model;

class ProjectCategoryModel extends Model
{
    /**
     * 自动完成，创建时间
     * @var array
     */
    protected $_auto = [
        ['create_time', 'time', 1, 'function'],
    ];

    /**
     * 表单提交自动对数据进行验证
     * @var array
     */
    protected $_validate = [
        ['name', 'require', '分类名称不能为空!', 1],
    ];
}