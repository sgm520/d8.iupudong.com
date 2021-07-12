<?php

namespace app\web\controller;

use app\web\model\Token;
use app\web\model\UserModel;
use app\web\model\RoleUserModel;
use cmf\controller\HomeBaseController;

class LoginController extends HomeBaseController{

    public function initialize()
    {
        $adminId = session("USER_ID");
        if (empty($adminId)) {
            session("__LOGIN_BY_CMF_ADMIN_PW__", 1);//设置后台登录加密码
        }

        parent::initialize();
    }

    /**
     *登录页面
     */
    public function Login_authentication(){
        if (!$this->request->isPost()) {
            echo json_encode(['code'=>0,"data"=>'非法登录!'],JSON_UNESCAPED_UNICODE);
            die;
        }

        $name = $this->request->param("tel");
        if (empty($name)) {
            echo json_encode(['code'=>0,"data"=>lang('USERNAME_OR_EMAIL_EMPTY')],JSON_UNESCAPED_UNICODE);
            die;
        }

        $pass = $this->request->param("password");
        if (empty($pass)) {
            echo json_encode(['code'=>0,"data"=>lang('PASSWORD_REQUIRED')],JSON_UNESCAPED_UNICODE);
            die;
        }

        if (strpos($name, "@") > 0) {//邮箱登陆
            $where['user_email'] = $name;
        } else {
            $where['user_login'] = $name;
        }

        $result = UserModel::where($where)->find();

        if (!empty($result) && $result['user_type'] == 2) {
            if (cmf_compare_password($pass, $result['user_pass'])) {
                $groups = RoleUserModel::alias("a")
                    ->join('role b', 'a.role_id =b.id')
                    ->where(["user_id" => $result["id"], "status" => 1])
                    ->value("role_id");
                    
                //session设置
                ini_set("session.gc_maxlifetime",99999999);
                ini_set("session.cookie_lifetime",999999999);        

                //登入成功页面跳转
                session('USER_ID', $result["id"]);
                session('USER_NAME', $result["user_login"]);
                $data                    = [];
                $data['last_login_ip']   = get_client_ip(0, true);
                $data['last_login_time'] = time();
                $token                   = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                $token=Token::setToken($result["id"]);
                UserModel::where('id', $result['id'])->update($data);
                cookie("admin_username", $name, 3600 * 24 * 30);
                session("__LOGIN_BY_CMF_ADMIN_PW__", null);
//                $this->success(lang('LOGIN_SUCCESS'), url("admin/Index/index"));
                echo json_encode(['code'=>200,"data"=>"登陆成功",'data'=>$token],JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['code'=>0,"data"=>lang('PASSWORD_NOT_RIGHT')],JSON_UNESCAPED_UNICODE);

            }
        } else {
            echo json_encode(['code'=>0,"data"=>lang('USERNAME_NOT_EXIST')],JSON_UNESCAPED_UNICODE);
        }


    }

    /**
     *退出登录页面
     */

    public function logout(){
        session("USER_ID",null);
        echo json_encode(["code"=>200,"data"=>"退出成功"],JSON_UNESCAPED_UNICODE);
//        return redirect(url('/', [], false, true));
    }
    
    
    //修改密码
   public function setPassword(){
            $username = $this->request->param('username');
            if(empty($username)){
                echo json_encode(["code"=>0,"data"=>"账号不能为空"],JSON_UNESCAPED_UNICODE);die;
            }
            // $old_password = $this->request->param('old_password');
            // if(empty($old_password)){
            //     echo json_encode(["code"=>0,"data"=>"旧密码不能为空"],JSON_UNESCAPED_UNICODE);die;
            // }
            $new_password = $this->request->param('new_password');
            if(empty($new_password)){
                echo json_encode(["code"=>0,"data"=>"新密码不能为空"],JSON_UNESCAPED_UNICODE);die;
            }
            $confirm_password = $this->request->param('confirm_password');
            if(empty($confirm_password)){
                echo json_encode(["code"=>0,"data"=>"确认密码不能为空"],JSON_UNESCAPED_UNICODE);die;
            }
            if($new_password != $confirm_password){
                echo json_encode(["code"=>0,"data"=>"确认密码与新密码不一臻"],JSON_UNESCAPED_UNICODE);die;
            }
            $userModel = new UserModel();
            $where = [
                'user_login'=>$username,
                // 'user_pass'=>cmf_password($old_password),
            ];
            $userinfo = $userModel->where($where)->find();
            if(empty($userinfo)){
                echo json_encode(["code"=>0,"data"=>"账号不正确"],JSON_UNESCAPED_UNICODE);die;
            }
            
            $result = $userModel->where(['user_login'=>$username])->update(['user_pass'=>cmf_password($new_password)]);
    
            if(!empty($result)){
                echo json_encode(["code"=>200,"data"=>"修改成功"],JSON_UNESCAPED_UNICODE);die;
            }
            
            echo json_encode(["code"=>0,"data"=>"修改失败"],JSON_UNESCAPED_UNICODE);die;
        }

}