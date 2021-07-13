<?php
namespace app\web\controller;


use app\web\model\UserModel;
use cmf\controller\HomeBaseController;
use think\Db;

class RegisterController extends HomeBaseController{


    /**
     * 注册页面API
     */
    public function index(){

        $s_id = $this->request->post("s_id");

        if($this->request->isPost()){
            $data = $this->request->param(["user_pass","user_login"]);
            $user = new UserModel();
            $su = $user->find($s_id);
            
            $ud = [
                "user_login"  => $data['user_login'],
                "tel"  => $data['user_login'],
                "user_pass" => cmf_password($data['user_pass']),
                "user_type" => 2,
                "create_time" => time(),
            ];
            $rq = $user->where("user_login",$ud["user_login"])->find();
            if($rq){
                echo json_encode(["code"=>37,"data"=>"","msg"=>"账号存在"],JSON_UNESCAPED_UNICODE);die;
            }

            $ud['s_id'] = 1;
            $ud['s_path'] = 1;
            if (!empty($s_id) && $s_id = $su['id']){
                $ud['s_id'] = $s_id;
                $arr=[$s_id];
                $ud['s_path'] = self::getParent($s_id,$arr);
                $indirect_id = Db("user")->where("id",$su['s_id'])->find();
                if(!empty($indirect_id['s_id'])){
                    $ud['indirect'] = $su['indirect'];
                }
            }

            $result = $this->validate($ud, 'User');

            if ($result !== true) {
                echo json_encode(["code"=>200,"data"=>$result],JSON_UNESCAPED_UNICODE);die;
            }

            $userId = $user->insertGetId($ud);
         
            if ($this->giving($userId,5,"会员任务奖励")){
                echo json_encode(["code"=>200,"data"=>"注册成功,并赠送5元"],JSON_UNESCAPED_UNICODE);die;
            }else{
                $user->delete($userId);
                echo json_encode(["code"=>4,"data"=>"注册失败，请重新注册"],JSON_UNESCAPED_UNICODE);die;
            }
        }else{
            echo json_encode(["code"=>22,"data"=>"提交方式错误"],JSON_UNESCAPED_UNICODE);die;
        }
    }


    public function getParent($sid,$arr){
          $order= Db('user')->where('id',$sid)->find();
           if($order['id']){
               array_push($arr,$order->s_id);
               self::getParent($order->id,$arr);
           }
            $comma_separated = implode(",", $arr);
           return $comma_separated;
    }

    /**
     * 给用户送钱
     * cmf_user_balance_log
     * @param $userId  用户id
     * @param $change   金额
     * @param $remark   备注
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function giving($userId,$change,$remark){
        $user = UserModel::find($userId);
        $balance = $user["balance"];

        $ia = ["user_id"=>$userId,"create_time"=>time(),"tel"=>$user['tel'],"change"=>$change,"description"=>"注册会员","remark"=>$remark,"balance"=>$balance];
        $result = Db("user_balance_log")->insert($ia);

        if ($result){
            $user->save(['income'=>(int)($user->income + 5)]);
            return true;
        }
        return false;
    }




}