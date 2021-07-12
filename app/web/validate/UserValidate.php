<?php

namespace app\web\validate;

use think\Validate;

class UserValidate extends Validate{


    protected $rule = [
        'user_login' => 'require|unique:user,user_login',
        'user_pass'  => 'require',
        'tel'  => 'require',
    ];
    protected $message = [
        'user_login.require' => '用户不能为空',
        'user_login.unique'  => '用户名已存在',
        'user_pass.require'  => '密码不能为空',
    ];

    protected $scene = [
        'add'  => ['user_login', 'user_pass', 'user_email'],
        'edit' => ['user_login', 'user_email'],
    ];

}
