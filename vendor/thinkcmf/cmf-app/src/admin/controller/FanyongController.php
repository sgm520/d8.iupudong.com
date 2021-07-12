<?php
namespace app\admin\controller;


use app\admin\model\ArticleModel;
use app\web\controller\FanyongValidate;
use app\admin\model\FanyongModel;
use cmf\controller\AdminBaseController;
use think\db\Query;
use think\Model;

class FanyongController extends AdminBaseController{
    
    protected $state = ["大额分期","企业贷","信用卡"];



    public function index(){
        $fanyongModel = new FanyongModel();
        $list = $fanyongModel->where(function (Query $query) {
            $data = $this->request->param();
            if (!empty($data['name'])) {
                $name = $data['name'];
                $query->where('name', 'like', "%$name%");
            }

        })->order("id","desc")->paginate();
        // 获取分页显示
        $page = $list->render();

        $this->assign("page", $page);
        $this->assign("formget",array_merge($_GET,$_POST));
        $this->assign("fanyong",$list);
        return $this->fetch();
    }

    public function add(){
        
        $this->assign("state",$this->state);
        return $this->fetch();
    }

    public function addPost(){
        if($this->request->isPost()){
            $data      = $this->request->param();
            $fanyongModel = new FanyongModel();
            $result    = $this->validate($data, 'Fanyong');

            $data["status"] = 1;
            $data["more"] = json_encode($data['more']);
            $data["user_login"] = session("name");
            $data["create_time"] = time();
            $data["conditions_content"] = safe_html(htmlspecialchars_decode($data['conditions_content']));
            if ($result !== true) {
                $this->error($result);
            }
            if ($fanyongModel->save($data)){
                $this->success("添加成功",url("admin/fanyong/index"));
            }

//            htmlspecialchars_decode($article['post_content']));

        }
    }

    public function edit(){
        $id = $this->request->param("id");
        $fanyongModel = new FanyongModel();
        $fanyong = $fanyongModel->find($id);
        $fanyong['more'] = json_decode($fanyong['more']);
//        var_dump($fanyong);die;
        $this->assign("fanyong",$fanyong);
        $this->assign("state",$this->state);
        return $this->fetch();
    }


    public function editPost(){
        if($this->request->isPost()) {
            $data = $this->request->param();
            $result = $this->validate($data, 'Fanyong');
            if ($result !== true) {
                $this->error($result);
            }
            $data["more"] = json_encode($data['more']);
            $data["conditions_content"] = safe_html(htmlspecialchars_decode($data['conditions_content']));
            $fanyongModel = FanyongModel::find($data["id"]);
            if ($fanyongModel->save($data)){
                $this->success("编辑成功",url("admin/fanyong/index"));
            }
        }
    }

    /**
     * 返佣产品排序
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
        $FanyongModel = new  FanyongModel();
        parent::listOrders($FanyongModel);
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
            $FanyongModel = new FanyongModel();

            if (isset($data['id']) && !empty($data["display"])) {
                $ids = $this->request->param('id/a');
                $FanyongModel->where('id', 'in', $ids)->update(['status' => 1]);
                $this->success("更新成功！");
            }

            if (isset($data['id']) && !empty($data["hide"])) {
                $ids = $this->request->param('id/a');
                $FanyongModel->where('id', 'in', $ids)->update(['status' => 0]);
                $this->success("更新成功！");
            }
        }
    }


    public function status(){
        $id = $this->request->param("id");
        $state = Db("fanyong")->where("id",$id)->field("status")->find();
        if($state['status']){
            Db("fanyong")->where("id",$id)->update(['status'=>0]);
            $this->success("下架成功");
        }else{
            Db("fanyong")->where("id",$id)->update(['status'=>1]);
            $this->success("上架成功");
        }
    }


}