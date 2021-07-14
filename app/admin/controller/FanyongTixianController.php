<?php
namespace app\admin\controller;

use app\web\model\UserModel;
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
        $order=Db("tixian")->where("id",$id)->find();
        if(empty($order)){
            $this->error("订单没有找到");
        }
        Db("tixian")->where("id",$id)->update(["state"=>1]);
        git = UserModel::find($order['user_id']);
        $u_data->tx=$u_data->tx+$order['money'];
        $this->success("提现通过");
    }
    public function refused(){
        $id = $this->request->param("id");

        $order=Db("tixian")->where("id",$id)->find();
        if(empty($order)){
            $this->error("订单没有找到");
        }
        if($order['state'] !=2){
            $this->success("改状态不能提现");
        }else{
            $u_data = UserModel::find($order['user_id']);
            if(empty($u_data)){
                $this->success("未找到该用户");
            }else{
                $u_data->ktx=$u_data->ktx+$order['money'];

                $u_data->save();
                Db("tixian")->where("id",$id)->update(["state"=>0]);
            }

        }



        $this->success("提现拒绝");
    }



}