<?php
namespace app\web\controller;

use app\admin\model\ArticleModel;
use app\admin\model\FanyongModel;
use cmf\controller\HomeBaseController;

class SearchController extends HomeBaseController{

    /*
     * 文章搜索API
     */
    public function search_article(){
        $title = $this->request->param("title");
        $articleModel = new ArticleModel();
        $articleAll = $articleModel->where("title","like","%$title%")->select();
        if($articleAll){
            return json_encode(["code"=>200,"data"=>$articleAll]);
        }else{
            return json_encode(["code"=>404,"data"=>"联系管理员检查"]);
        }

    }


    /**
     * 返佣产品搜索
     */
    public function search_fanyong(){
        $title = $this->request->param("name");
        $fanyongModel = new FanyongModel();
        $fanyongAll = $fanyongModel->where('status',1)->where("name","like","%$title%")->select();
        if($fanyongAll){
            return json_encode(["code"=>200,"data"=>$fanyongAll],JSON_UNESCAPED_UNICODE);
        }else{
            return json_encode(["code"=>404,"data"=>"联系管理员检查"],JSON_UNESCAPED_UNICODE);
        }
    }



}