<?php
namespace app\admin\validate;

use think\Validate;

class FanyongValidate extends Validate{

    protected $rule = [
        'name' => 'require',  //产品名称
        'conditions_content' => 'require',  //申请条件
    ];

    protected $message = [
        'name.require' => '名称不能为空',
        'conditions_content.require' => '申请条件不能为空',
    ];


}