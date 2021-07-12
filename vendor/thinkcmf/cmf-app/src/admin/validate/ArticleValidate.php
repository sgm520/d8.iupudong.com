<?php
namespace app\admin\validate;

use think\Validate;

class ArticleValidate extends Validate{

    protected $rule = [
        'title' => 'require',
        'category'  => 'require',
        'content'  => 'require',
    ];

    protected $message = [
        'title.require' => '标题不能为空',
        'category.require'  => '分类必须选择',
        'content.require'  => '内容不能为空',
    ];


}