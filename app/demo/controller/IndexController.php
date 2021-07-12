<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\demo\controller;

use cmf\controller\HomeBaseController;
use app\admin\model\ChaoshiModel;
use think\Model;
use cmf\controller\RestBaseController;
class IndexController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch(':index');
    }

    public function block()
    {
        $ChaoshiModel = new ChaoshiModel();
        $chaoshi     = $ChaoshiModel->select();
//        $this->success('请求成功!', $chaoshi);
var_dump(json_encode($chaoshi));
//        return $this->fetch();
    }

    public function ws()
    {
        return $this->fetch(':ws');
    }
}
