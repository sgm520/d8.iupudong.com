<?php
namespace app\admin\controller;


use app\admin\model\ArticleCategoryModel;
use cmf\controller\AdminBaseController;
use think\Model;

class ArticleCategoryController extends AdminBaseController{

    public function index(){
        $categoryArticle = new ArticleCategoryModel();
        $category = $categoryArticle->order("id","desc")->paginate();

        // 获取分页显示
        $page = $category->render();

        $this->assign("page", $page);
        $this->assign("category",$category);
        return $this->fetch();
    }

    public function add(){



        return $this->fetch();
    }
    public function addPost(){
        if ($this->request->isPost()) {
            $data      = $this->request->param();
            $data['status'] = 1;
            $data['create_time'] = time();
            $categoryArticle = new ArticleCategoryModel();
            $result    = $this->validate($data, 'ArticleCategory');
            if ($result !== true) {
                $this->error($result);
            }
            $categoryArticle->save($data);

            $this->success("添加成功！", url("ArticleCategory/index"));
        }
    }


    public function edit(){
        $id        = $this->request->param('id', 0, 'intval');
        $categoryArticle = new ArticleCategoryModel();
        $category      = $categoryArticle->find($id);
        $this->assign('category', $category);
        return $this->fetch();
    }


    public function status(){
        $id = $this->request->param("id");
        $state = Db("article_category")->where("id",$id)->field("status")->find();
        if($state['status']){
            Db("article_category")->where("id",$id)->update(['status'=>0]);
            $this->success("下架成功");
        }else{
            Db("article_category")->where("id",$id)->update(['status'=>1]);
            $this->success("上架成功");
        }
    }

}