<?php
namespace app\admin\controller;

use app\admin\model\UserModel;
use cmf\controller\AdminBaseController;
use think\facade\Db;

class FanyongOrderController extends AdminBaseController{

    public function index(){
        $order = Db::name("fanyong_order")->order("id","desc")->paginate("10");
        // 获取分页显示
        $page = $order->render();

        $this->assign("page", $page);
        $this->assign("order",$order);
        return $this->fetch();
    }

    public function edit(){
        $ary = [
            "s"=> ["lines"=>"授信额度"],
            "x"=> ["xlines"=>"下款金额","fmoney"=>"返佣金额"],
            "t"=> ["xlines"=>"下款金额","fmoney"=>"返佣金额"],
        ];
        $data = $this->request->param();

        $this->assign("tx",$ary[$data["d"]]);
        $this->assign("id",$data["id"]);
        return $this->fetch();
    }

    public function editPost(){
        if ($this->request->post()){
            $data = $this->request->param();
            if (!empty($data["fmoney"])){
                $data["status"] = 1;
            }
            Db("fanyong_order")->where("id",$data["id"])->update($data);
            $this->fanyong($data['id']);
            $this->success("修改成功");
        }
    }


    public function fanyong($id){
        $userModel = new UserModel();
        $order = Db("fanyong_order")->where("id",$id)->field("pid,name,p_id,tel,xlines,p_title,fmoney")->find();
        $user = $userModel->where("id",$order['pid'])->field("id,income,indirect,s_id")->find();

        $remark = $order['p_title'];

        /**
         * 申请用户
         */
        if($user['id']){
            $balance = $order['fmoney'];
            $description = "直推客户奖励";
            $this->balance($user['id'],$order['tel'],$order['name'],$order['xlines'],$balance,$description,$remark);
        }
        /**
         * 直推反百分之五
         */
        if($user['s_id']){
            $balance = $order['fmoney']*0.05;
            $description = "下级直推奖励";
            $this->balance($user['s_id'],$order['tel'],$order['name'],$order['xlines'],$balance,$description,$remark);
        }
        if($user['indirect']){
            $balance = $order['fmoney']*0.02;
            $description = "下下级直推奖励";
            $this->balance($user['indirect'],$order['tel'],$order['name'],$order['xlines'],$balance,$description,$remark);
        }

    }

    public function balance($user_id,$tel,$name,$change,$balance,$description,$remark){
        $user = UserModel::find($user_id);
        $userModel = new UserModel();
        $user_result = $userModel->where("id",$user_id)->save(["income"=>$user['income']+$balance,"ktx"=>$user["ktx"]+$balance]);
        $user_balance_log = [
            "user_id" =>   $user_id,
            "create_time" =>   time(),
            "tel" =>   $user["tel"],
            "name" =>   $name,
            "k_tel" =>   $tel,
            "change" =>   $change,
            "balance" =>   $balance,
            "description" =>   $description,
            "remark" =>   $remark,
        ];
        Db("user_balance_log")->insert($user_balance_log);
    }

    /**
     * 拒绝申请
     * @throws \think\db\exception\DbException
     */
    public function refused(){
        $id = $this->request->param("id");
        Db("fanyong_order")->where("id",$id)->update(["status"=>0]);
        $this->success("已拒绝");
    }

}
