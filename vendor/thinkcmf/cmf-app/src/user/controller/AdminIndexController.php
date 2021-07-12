<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace app\user\controller;

use app\user\model\UserModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\db\Query;
use think\Model;

/**
 * Class AdminIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'用户管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )
 *
 * @adminMenuRoot(
 *     'name'   =>'用户组',
 *     'action' =>'default1',
 *     'parent' =>'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   =>'',
 *     'remark' =>'用户组'
 * )
 */
class AdminIndexController extends AdminBaseController
{

    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '本站用户',
     *     'parent' => 'default1',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('user_admin_index_view');

        if (!empty($content)) {
            return $content;
        }

        $list = UserModel::where(function (Query $query) {
            $data = $this->request->param();
            if (!empty($data['uid'])) {
                $query->where('id', intval($data['uid']));
            }

            if (!empty($data['keyword'])) {
                $keyword = $data['keyword'];
                $query->where('user_login|user_nickname|user_email|mobile', 'like', "%$keyword%");
            }

        })->order("create_time DESC")
            ->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    /**
     *用户详情
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function details(){
        $id = $this->request->param("id");

        $user = UserModel::find($id);
        if($this->request->isPost()){
            $user->save($this->request->post());
            $this->success("保存成功");
        }
        $this->assign("user",$user);
        return $this->fetch();
    }

    /**
     * 下级列表
     * @return mixed
     * @throws \think\db\exception\DbException
     */
    public function level(){
        $id = $this->request->param("id");
        $userModel = new UserModel();
        $where = array(
          "s_id|indirect" => ["or",3],
        );
        $user = $userModel->where($where)->paginate("10");

        $this->assign("user",$user);
        return $this->fetch();
    }

    public function fanyong(){
        $id = $this->request->param("id");
        $userModel = new UserModel();
        $where = array(
            "s_id|indirect" => ["or",3],
        );
        $user_id = $userModel->where($where)->field("id")->select()->toArray();
        $uid = [];
        foreach($user_id as $vo){
            $uid[] = $vo["id"];
        }
        $fanyong = Db("user_balance_log")->where("user_id","between",$uid)->paginate("10");

        $this->assign("fanyong",$fanyong);
        return $this->fetch();
    }

    public function edit(){
        $id = $this->request->param("id");
        $user = UserModel::find($id);
        if($this->request->isPost()){
            $user->save($this->request->post());
            $this->success("保存成功");
        }
        $this->assign("user",$user);
        return $this->fetch();
    }


    /**
     * 本站用户删除
     * @adminMenu(
     *     'name'   => '本站用户删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户拉黑',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = UserModel::where(["id" => $id, "user_type" => 2])->delete();
            if ($result) {
                $this->success("会员删除成功！", "adminIndex/index");
            } else {
                $this->error('会员删除失败,会员不存在,或者是管理员！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 本站用户拉黑
     * @adminMenu(
     *     'name'   => '本站用户拉黑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户拉黑',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = UserModel::where(["id" => $id, "user_type" => 2])->update(['user_status' => 0]);
            if ($result) {
                $this->success("会员拉黑成功！", "adminIndex/index");
            } else {
                $this->error('会员拉黑失败,会员不存在,或者是管理员！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 本站用户启用
     * @adminMenu(
     *     'name'   => '本站用户启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户启用',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            UserModel::where(["id" => $id, "user_type" => 2])->update(['user_status' => 1]);
            $this->success("会员启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
}
