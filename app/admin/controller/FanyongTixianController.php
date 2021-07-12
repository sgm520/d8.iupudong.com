<?php
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class FanyongTixianController extends AdminBaseController{

    public function index(){


        $txdb = Db("tixian")->order("id","desc")->paginate("15");
        // 获取分页显示
        $page = $txdb->render();

        $this->assign("page", $page);
        $this->assign("data",$txdb);
        return $this->fetch();

    }

    public function agree(){
        $id = $this->request->param("id");
        Db("tixian")->where("id",$id)->update(["state"=>1]);
        $this->success("提现通过");
    }
    public function refused(){
        $id = $this->request->param("id");
        Db("tixian")->where("id",$id)->update(["state"=>0]);
        $this->success("提现拒绝");
    }



}