<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\ChaoshiCategoryModel;
use app\admin\model\LinkModel;
use cmf\controller\AdminBaseController;
use app\admin\model\ChaoshiModel;
use think\Db;
use think\db\Query;
use think\Model;

class ChaoshiController extends AdminBaseController
{
    protected $targets = ["无","放水","热门"];
    protected $state = [1=>"是",0=>"否"];

    /**
     * 超市产品管理
     * @adminMenu(
     *     'name'   => '超市产品',
     *     'parent' => 'admin/chaoshi/index',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 50,
     *     'icon'   => '',
     *     'remark' => '超市产品管理',
     *     'param'  => ''
     * )
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $ChaoshiModel = new ChaoshiModel();
        $chaoshi     = $ChaoshiModel->where(function (Query $query) {
            $data = $this->request->param();
            if (!empty($data['name'])) {
                $name = $data['name'];
                $query->where('name', 'like', "%$name%");
            }

        })->order("id","desc")->paginate(10);
        // 获取分页显示
        $page = $chaoshi->render();

        $this->assign("page", $page);
        $this->assign("formget",array_merge($_GET,$_POST));
        $this->assign('chaoshi', $chaoshi);
        return $this->fetch();
    }

    public function add(){

        $category = Db("chaoshi_category")->select();
        $this->assign("category",$category);
        $this->assign("state",$this->state);
        $this->assign('targets', $this->targets);
        return $this->fetch();
    }

    public function addPost(){
        $data = $this->request->param();
        $chaoshiModel = new ChaoshiModel();
        $result = $this->validate($data,"Chaoshi");
        if ($result !== true) {
            $this->error($result);
        }
        $date['status'] = 1;
        $date['list_order'] = 1000;
        $data["remarks"] = json_encode($data["remarks"], JSON_UNESCAPED_UNICODE);

        $data["update_time"] = $data['create_time'] = time();
        $chaoshiModel->save($data);

        $this->success("添加成功！", url("chaoshi/index"));
    }


    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $chaoshiModel = new ChaoshiModel();
        $chaoshi      = $chaoshiModel->find($id);
        $chaoshi['remarks'] = json_decode($chaoshi['remarks'],true);

        $category = Db("chaoshi_category")->select();
        $this->assign("category",$category);
        $this->assign('targets', $this->targets);
        $this->assign('state', $this->state);
        $this->assign('chaoshi', $chaoshi);
        return $this->fetch();
    }

    public function editPost(){
        $data = $this->request->param();
        $result = $this->validate($data,"Chaoshi");
        if ($result !== true) {
            $this->error($result);
        }
        $data["remarks"] = json_encode($data['remarks'],JSON_UNESCAPED_UNICODE);
        $data["update_time"] = time();
        $ChaoshiModel = ChaoshiModel::find($data['id']);
        $ChaoshiModel->save($data);

        $this->success("更新成功！", url("chaoshi/index"));
    }

    /**
     * 系列分类 管理部分
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     *
     */
    public function category(){

        $ChaoshiCategoryModel = new ChaoshiCategoryModel();
        $category = $ChaoshiCategoryModel->where(function (Query $query) {
            $data = $this->request->param();
            if (!empty($data['name'])) {
                $name = $data['name'];
                $query->where('name', 'like', "%$name%");
            }

        })->order("id","desc")->paginate("15");

        // 获取分页显示
        $page = $category->render();

        $this->assign("page", $page);

        $this->assign("formget",array_merge($_GET,$_POST));
        $this->assign("category",$category);
        return $this->fetch();
    }

    public function category_add(){

        return $this->fetch();
    }

    public function category_addPost(){
        if ($this->request->isPost()) {
            $data      = $this->request->param();
            $data['state'] = 1;
            $data['list_order'] = 1000;
            $data['time'] = time();
            $ins = Db("chaoshi_category")->insert($data);

            if($ins){
                $this->success("添加成功！", url("chaoshi/category"));
            }

        }
    }

    public function category_edit(){
        $id        = $this->request->param('id', 0, 'intval');
        $category = Db("chaoshi_category")->where("id",$id)->find();
        $this->assign('category', $category);
        return $this->fetch();
    }

    public function category_editPost(){
        if ($this->request->isPost()) {
            $id   = $this->request->param("id");
            $data   = $this->request->param();
            $data['update_time'] = time();
            $result = $this->validate($data, 'Chaoshi');
            if ($result !== true) {
                $this->error($result);
            }
            unset($data['id']);
            $category = Db("chaoshi_category")->where("id",$id)->update($data);

            $this->success("保存成功！", url("Chaoshi/category"));
        }
    }


    /**
     * 超市产品排序
     * @adminMenu(
     *     'name'   => '超市产品排序',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '超市产品排序',
     *     'param'  => ''
     * )
     */
    public function listOrder()
    {
        $ChaoshiModel = new  ChaoshiModel();
        parent::listOrders($ChaoshiModel);
        $this->success("排序更新成功！");
    }

    /**
     * 超市产品显示隐藏
     * @adminMenu(
     *     'name'   => '超市产品显示隐藏',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '超市产品显示隐藏',
     *     'param'  => ''
     * )
     */
    public function toggle()
    {
        if ($this->request->isPost()) {
            $data      = $this->request->param();
            $ChaoshiModel = new ChaoshiModel();

            if (isset($data['id']) && !empty($data["display"])) {
                $ids = $this->request->param('id/a');
                $ChaoshiModel->where('id', 'in', $ids)->update(['status' => 1]);
                $this->success("更新成功！");
            }

            if (isset($data['id']) && !empty($data["hide"])) {
                $ids = $this->request->param('id/a');
                $ChaoshiModel->where('id', 'in', $ids)->update(['status' => 0]);
                $this->success("更新成功！");
            }
        }
    }

    public function category_listOrder()
    {

        $ChaoshiModel = new ChaoshiCategoryModel();
        parent::listOrders($ChaoshiModel);
        $this->success("排序更新成功！");
    }

    public function delete()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');
            ChaoshiModel::destroy($id);
            $this->success("删除成功！", url("Chaoshi/index"));
        }
    }

    public function status(){
        $id = $this->request->param("id");
        $state = Db("chaoshi")->where("id",$id)->field("status")->find();
        if($state['status']){
            Db("chaoshi")->where("id",$id)->update(['status'=>0]);
            $this->success("下架成功");
        }else{
            Db("chaoshi")->where("id",$id)->update(['status'=>1]);
            $this->success("上架成功");
        }
    }
    /**
     * 超市分类的上架 下架
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function state(){
        $id = $this->request->param("id");
        $state = Db("chaoshi_category")->where("id",$id)->field("state")->find();
        if($state['state']){
            Db("chaoshi_category")->where("id",$id)->update(['state'=>0]);
            $this->success("下架成功");
        }else{
            Db("chaoshi_category")->where("id",$id)->update(['state'=>1]);
            $this->success("上架成功");
        }
    }


}
