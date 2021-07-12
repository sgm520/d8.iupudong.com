<?php
namespace app\web\controller;

use cmf\controller\HomeBaseController;
use cmf\model\OptionModel;


class SettingController extends HomeBaseController {

    /**
     * 微信群和推广海报
     */
    public function app(){
        //header('Content-Type:application/json');
        $appSettings  = OptionModel::where('option_name', 'app')->value('option_value');
        echo $appSettings;

    }

}