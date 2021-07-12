<?php

namespace app\admin\controller;

use app\admin\model\ArticleModel;
use cmf\controller\AdminBaseController;
use think\Db;
use think\Model;
use tree\Tree;

class ArticleController extends AdminBaseController{

    public function index(){
        $articleModel = new ArticleModel();
        $article = $articleModel->order("id","desc")->paginate();
        // 获取分页显示
        $page = $article->render();

        $this->assign("page", $page);
        $this->assign("article",$article);
        return $this->fetch();
    }


    public function add(){

        $category = Db("article_category")->select();
        $this->assign("category",$category);
        return $this->fetch();
    }

    public function addPost(){
        if($this->request->isPost()){
            $data      = $this->request->param();
            $articleModel = new ArticleModel();
            $result    = $this->validate($data, 'Article');

            $data["status"] = 1;
            $data["user_login"] = session("name");
            $data["content"] = htmlspecialchars_decode($data['content']);
            if ($result !== true) {
                $this->error($result);
            }
            if ($articleModel->save($data)){
                $this->success("添加成功",url("admin/article/index"));
            }


//            htmlspecialchars_decode($article['post_content']));


        }
    }

    public function edit(){
        $id = $this->request->param("id");
        $articleModel = new ArticleModel();
        $article = $articleModel->where("id",$id)->find();
        $category = Db("article_category")->select();
        $this->assign("category",$category);
        $this->assign("article",$article);
        return $this->fetch();
    }

    public function editPost(){
        if($this->request->isPost()) {
            $data = $this->request->param();
            $result = $this->validate($data, 'Article');
            if ($result !== true) {
                $this->error($result);
            }
            $data["update_time"] = date("Y/m/d H:i",time());
            $data["content"] = htmlspecialchars_decode($data['content']);
            $articleModel = ArticleModel::find($data["id"]);
            if ($articleModel->save($data)){
                $this->success("编辑成功",url("admin/article/index"));
            }
        }
    }

    public function status(){
        $id = $this->request->param("id");
        $state = Db("article")->where("id",$id)->field("status")->find();
        if($state['status']){
            Db("article")->where("id",$id)->update(['status'=>0]);
            $this->success("下架成功");
        }else{
            Db("article")->where("id",$id)->update(['status'=>1]);
            $this->success("上架成功");
        }
    }

    public function delete(){
        if(isset($_GET['id'])){
            $articleModel = new ArticleModel();

            if ($articleModel->where(['id'=>$_GET['id']])->delete()) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }



}
