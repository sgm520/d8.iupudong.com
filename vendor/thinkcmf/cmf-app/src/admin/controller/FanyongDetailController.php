<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class FanyongDetailController extends AdminBaseController{

    //cmf_user_balance_log

    public function default(){
        $detail = Db("user_balance_log")->order("id","desc")->paginate();
        // 获取分页显示
        $page = $detail->render();

        $this->assign("page", $page);
        $this->assign("detail",$detail);
        return $this->fetch();

    }


}
