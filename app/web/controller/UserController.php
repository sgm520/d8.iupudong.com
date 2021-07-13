<?php

namespace app\web\controller;

use app\web\model\TixianModel;
use app\web\model\Token;
use app\web\model\UserModel;
use cmf\controller\HomeBaseController;
use think\facade\Db;
use think\Model;

class UserController extends HomeBaseController{

    protected $userId;
    public function initialize()
    {
        $this->userId = Token::getUserIdByToken(request()->header('token', ''));
         if (empty($this->userId)) {
             echo json_encode(["code"=>"404","state"=>0,"data"=>"请登录账号！"],JSON_UNESCAPED_UNICODE);
             die;
         }

        parent::initialize();
    }

    /**
     * 个人中心页面
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function user(){
        $user = new UserModel();
        $u_data = $user
            ->where("id",$this->userId)
            ->field("id,tel,last_tel,income,tx,ktx,user_nickname,avatar,vip,is_real,al_pay_name,al_pay_account,is_band")
            ->find();
        echo json_encode(["code"=>200,"data"=>$u_data],JSON_UNESCAPED_UNICODE);die;

    }

    public function tx_list(){
        $txModel = new TixianModel();
        $list = $txModel->where("user_login",session("USER_NAME"))->select();
        foreach($list as $k=>$v){
            $list[$k]['tx_time_text'] = date('Y-m-d H:i:s',$v['tx_time']);
        }
        echo json_encode(["code"=>200,"data"=>$list],JSON_UNESCAPED_UNICODE);die;
    }


    /**
     * 返佣产品单页详情
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function fanyogn_list(){
        $id = $this->request->param("id");
        if(!empty($id)){
            $list = Db("fanyong")->where("id",$id)->find();
            echo json_encode(["code"=>200,"data"=>$list],JSON_UNESCAPED_UNICODE);die;
        }else{
            echo json_encode(["code"=>200,"data"=>"null","msg"=>"未查询到产品"],JSON_UNESCAPED_UNICODE);die;
        }
    }

    /**
     * 提现页面
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function tx123(){
        $param = $this->request->param();
        $user = new UserModel();
        $u_data = $user
            ->where("id",$this->userId)
            ->field("id_card,al_pay_name,al_pay_account")
            ->find();
        if(empty($param['money'])){
            echo json_encode(['code'=>40,"data"=>$u_data,"msg"=>"未填写提交金额"],JSON_UNESCAPED_UNICODE);
            die;
        }


        $money = $this->request->param("money");
        if(!empty($money)){
            $this->tx_validate($money,$u_data,$u_data);
        }else{
            echo json_encode(['code'=>200,"data"=>$u_data],JSON_UNESCAPED_UNICODE);
        }

    }

    public function tx(){

        if($this->request->isPost()){
            $param = $this->request->param();
            if(empty($param['money'])){
                echo json_encode(['code'=>0,"data"=>[],'msg'=>'提现金额不能为空'],JSON_UNESCAPED_UNICODE);die;
            }

            if($param['money']<10){
                echo json_encode(['code'=>0,"data"=>[],'msg'=>'提现金额不能小于10'],JSON_UNESCAPED_UNICODE);die;
            }
            if($param['money']>1000){
                echo json_encode(['code'=>0,"data"=>[],'msg'=>'提现金额不能大于1000'],JSON_UNESCAPED_UNICODE);die;
            }
            $txModel = new TixianModel();
            $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));
            $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
            $count_tx=$txModel->where('user_id',$this->userId)->where('tx_time','between', array($beginToday,$endToday))->count();
            if($count_tx){
                echo json_encode(['code'=>0,"data"=>[],'msg'=>'每日只能提现一次'],JSON_UNESCAPED_UNICODE);die;
            }
            $u_data = UserModel::find($this->userId);
            if($u_data['ktx']<$param['money']){
                echo json_encode(["code"=>0,"data"=>[],"msg"=>"可提现金额不足"],JSON_UNESCAPED_UNICODE);die;
            }
            $rq_us= $this->tx_validate($param['money'],$u_data,$u_data);
            if($rq_us){
                $u_data->ktx=$u_data->ktx-$param['money'];
                $u_data->save();
                echo json_encode(['code'=>200,"data"=>[],'msg'=>'已提交,等待审核'],JSON_UNESCAPED_UNICODE);die;
            }
            echo json_encode(['code'=>0,"data"=>"提交失败"],JSON_UNESCAPED_UNICODE);die;
        }else{
            $user = new UserModel();

            $u_data = $user
                ->where("id",$this->userId)
                ->field("id_card,al_pay_name,al_pay_account")
                ->find();
            echo json_encode(['code'=>200,"data"=>$u_data],JSON_UNESCAPED_UNICODE);die;
        }
    }



    /*
     * 判断是否可提现（配合提现功能使用）
     */
    public function tx_validate($money,$user,$u_data){

        $user = new UserModel();
        $txModel = new TixianModel();
        $uList = $user
            ->where("id",$this->userId)
            ->field("id_card,tx,ktx,income,al_pay_name,al_pay_account")
            ->find();
        $ins = [
            "money"=>$money, //提现金额
            "tx_time" => time(), //提现时间
            "state" => "2", //未处理
            "user_id" => $this->userId,
            "user_login" =>$user->getUser()->user_login,
            "al_pay_name" => $uList["al_pay_name"],
            "al_pay_account" => $uList["al_pay_account"],
        ];
        $txModel->save($ins);
        return $uList->save();
    }


    /**
     * 申请订单  申请产品
     */
    public function to_product(){
        if($this->request->post()){
            $UserModel = new UserModel();
            $data = $this->request->param(["name","tel","p_id","p_title"]);
            $get_data = [
                "pid" => session("USER_ID"),
                "status" => 2,
                "ment" => $UserModel->GetOs(),
                "time" => time(),
            ];
            $in_data = array_merge($data,$get_data);
            $request = Db::name("fanyong_order")->insert($in_data);
            if($request){
                echo json_encode(["code"=>200,"data"=>"申请成功"],JSON_UNESCAPED_UNICODE);die;
            }else{
                echo json_encode(["code"=>23,"data"=>"抱歉，未能提交成功，请检查填写。"],JSON_UNESCAPED_UNICODE);die;
            }
        }else{
            echo json_encode(["code"=>24,"data"=>"抱歉，提交方式有误"],JSON_UNESCAPED_UNICODE);die;
        }

    }



    /**
     * 收入明细
     */
    public function income_details(){
        $userId = session("USER_ID");
        $detail = Db::name("user_balance_log")->where("user_id",$userId)->select()->toArray();
        foreach($detail as $k=>$v){
            $detail[$k]['create_time_text'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        if($detail){
            echo json_encode(["code"=>200,"data"=>$detail],JSON_UNESCAPED_UNICODE);die;
        }else{
            echo json_encode(["code"=>200,"data"=>"暂时没有数据"],JSON_UNESCAPED_UNICODE);die;
        }

    }

    /**
     * 客户列表-订单记录
     */
    public function order(){
        $data = $this->request->param(["p_id","status","pid"]);
        $data['state'] = $data["p_id"];
        unset($data["p_id"]);
        $order = Db::name("fanyong_order")->where($data)->field('*,FROM_UNIXTIME(time,"%Y-%m-%d %H:%i:%s") time_text')->order('time desc')->select()->toArray();

        foreach($order as $k=>$v){
            $order[$k]['tel'] = substr_replace($v['tel'], '****', 3, 4);
        }
        echo json_encode(["code"=>200,"data"=>$order],JSON_UNESCAPED_UNICODE);die;
    }

    /**
     * 我的推广
     * @param $state
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function promote($state){
        $userModel = new UserModel();
        $userId = session("USER_ID");
        // true 直推 false 间推
        if ($state){
            $where = ["s_id"=>$userId];
        }else{
            $where = ["indirect"=>$userId];
        }
        $user = $userModel->where($where)->select();
        foreach($user as $k=>$v){
            $user[$k]['create_time_text'] = date('Y-m-d H:i:s',$v['create_time']);
        }
        echo json_encode(["code"=>200,"data"=>$user],JSON_UNESCAPED_UNICODE);die;
    }

    //实名认证
    public function real_name(){
        $param = $this->request->param(["real_name","id_card"]);
        if(empty($param['real_name'])){
            echo json_encode(['code'=>0,"data"=>"真实姓名不能为空",'msg'=>'真实姓名不能为空'],JSON_UNESCAPED_UNICODE);die;
        }
        if(empty($param['id_card'])){
            echo json_encode(['code'=>0,"data"=>"身份证号码不能为空",'msg'=>'身份证号码不能为空'],JSON_UNESCAPED_UNICODE);die;
        }
        $user = new UserModel();

        $u_data = $user
            ->where("id",$this->userId)
            ->find();

        if($u_data['is_real'] ==1){
            echo json_encode(["code"=>0,"data"=>'','msg'=>'你已实名 无需重复实名认证'],JSON_UNESCAPED_UNICODE);die;
        }

        $u_data->real_name=$param['real_name'];
        $u_data->id_card=$param['id_card'];
        $u_data->is_real=1;
        $u_data->save();
        echo json_encode(["code"=>200,"data"=>'','msg'=>'实名认证通过'],JSON_UNESCAPED_UNICODE);die;
    }


    public function band(){
        $param = $this->request->param(["al_pay_name","al_pay_account"]);
        if(empty($param['al_pay_name'])){
            echo json_encode(['code'=>0,"data"=>"",'msg'=>'支付宝名称'],JSON_UNESCAPED_UNICODE);die;
        }
        if(empty($param['al_pay_account'])){
            echo json_encode(['code'=>0,"data"=>[],'msg'=>'支付宝账号'],JSON_UNESCAPED_UNICODE);die;
        }
        $user = new UserModel();
        $u_data = $user
            ->where("id",$this->userId)
            ->find();

        if($u_data['is_band'] ==1){
            echo json_encode(["code"=>0,"data"=>[],'msg'=>'你已绑定提现方式 无需重复'],JSON_UNESCAPED_UNICODE);die;
        }

        $u_data->al_pay_name=$param['al_pay_name'];
        $u_data->al_pay_account=$param['al_pay_account'];
        $u_data->is_band=1;
        $u_data->save();
        echo json_encode(["code"=>200,"data"=>[],'msg'=>'实名认证通过'],JSON_UNESCAPED_UNICODE);die;
    }


}