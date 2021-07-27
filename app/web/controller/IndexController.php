<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-present http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Released under the MIT License.
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------

namespace app\web\controller;

use app\admin\controller\SlideItemController;
use app\admin\model\ChaoshiCategoryModel;
use app\admin\model\ArticleCategoryModel;
use app\admin\model\ArticleModel;
use app\admin\model\FanyongModel;
use app\admin\model\SlideItemModel;
use app\user\model\AssetModel;
use app\web\model\UserModel;
use cmf\controller\HomeBaseController;
use app\admin\model\ChaoshiModel;
use Petstore30\Category;
use think\Db;
use think\Model;
use cmf\controller\RestBaseController;

class IndexController extends HomeBaseController
{


    public function block()
    {
        // foreach($data_ChaoshiCategory as $k=>$v){
        //     $data_ChaoshiCategory[$v['id']] = $v;
        // }

        $data_ChaoshiCategory = ChaoshiCategoryModel::where('state', 1)->order("list_order", "asc")->select()->toArray();

        foreach ($data_ChaoshiCategory as $k => $v) {
            $data_ChaoshiCategory[$k]['children'] = ChaoshiModel::where('status', 1)->where('category', $v['id'])->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->order("list_order", "asc")->select()->toArray();
            // $data_ChaoshiCategory[$k]['children']['update_time_text'] = date('Y-m-d H:i:s',$v['children']['update_time']);
        }

        echo json_encode(["code" => 200, "data" => $data_ChaoshiCategory], JSON_UNESCAPED_UNICODE);
        die;


        $name = $this->request->param("name");
        // $data_ChaoshiCategory=ChaoshiCategoryModel::where([])->field('id,name,logo')->select()->toArray();

        if (!empty($name)) {
            $data_Chaoshi = ChaoshiModel::where('status', 1)->where('name', 'like', "{$name}%")->order("list_order", "asc")->select()->toArray();

            foreach ($data_Chaoshi as $k => $v) {
                $catgory                              = ChaoshiCategoryModel::where(['id' => $v['category']])->field('id cate,name cate_name,logo cate_logo')->find()->toArray();
                $data_Chaoshi[$k]['cate']             = $catgory['cate'];
                $data_Chaoshi[$k]['cate_name']        = $catgory['cate_name'];
                $data_Chaoshi[$k]['cate_logo']        = $catgory['cate_logo'];
                $data_Chaoshi[$k]['update_time_text'] = date('Y-m-d H:i:s', $v['update_time']);
            }

            echo json_encode(["code" => 200, "data" => $data_Chaoshi], JSON_UNESCAPED_UNICODE);
            die;

        } else {
            $data_Chaoshi = ChaoshiModel::where('status', 1)->select()->toArray();

            foreach ($data_Chaoshi as $k => $v) {
                $catgory                              = ChaoshiCategoryModel::where(['id' => $v['category']])->field('id cate,name cate_name,logo cate_logo')->find()->toArray();
                $data_Chaoshi[$k]['cate']             = $catgory['cate'];
                $data_Chaoshi[$k]['cate_name']        = $catgory['cate_name'];
                $data_Chaoshi[$k]['cate_logo']        = $catgory['cate_logo'];
                $data_Chaoshi[$k]['update_time_text'] = date('Y-m-d H:i:s', $v['update_time']);
            }

            echo json_encode(["code" => 200, "data" => $data_Chaoshi], JSON_UNESCAPED_UNICODE);
            die;
        }


        if (!empty($name)) {
            $chaoshi = ChaoshiModel::field('ch.*,ca.name as category_name,ca.category as cate,ca.logo as cate_logo')
                ->alias('ch')->join('chaoshi_category ca', 'ca.id = ch.category')
                ->where("ch.status", 1)
                ->where('ch.name', 'like', "{$name}%")
                ->select();

        } else {
            $chaoshi = ChaoshiModel::field('ch.*,ca.name as category_name,ca.category as cate,ca.logo as cate_logo')
                ->alias('ch')->join('chaoshi_category ca', 'ca.id = ch.category')
                ->where("ch.status", 1)
                ->select();
        }


        foreach ($chaoshi as $k => $v) {
            $chaoshi[$k]['update_time_text'] = date('Y-m-d H:i:s', $v['update_time']);
        }


        echo json_encode(["code" => 200, "data" => $chaoshi], JSON_UNESCAPED_UNICODE);
        die;
    }

    public function block123()
    {
        $name = $this->request->param("name");
        if (!empty($name)) {
            $chaoshi = ChaoshiModel::field('ch.*,ca.name as category_name,ca.category as cate,ca.logo as cate_logo')
                ->alias('ch')->join('chaoshi_category ca', 'ca.id = ch.category')
                ->where("ch.status", 1)
                ->where('ch.name', 'like', "{$name}%")
                ->select();
        } else {
            $chaoshi = ChaoshiModel::field('ch.*,ca.name as category_name,ca.category as cate,ca.logo as cate_logo')
                ->alias('ch')->join('chaoshi_category ca', 'ca.id = ch.category')
                ->where("ch.status", 1)
                ->select();
        }

        foreach ($chaoshi as $k => $v) {
            $chaoshi[$k]['update_time_text'] = date('Y-m-d H:i:s', $v['update_time']);
        }

        echo json_encode(["code" => 200, "data" => $chaoshi], JSON_UNESCAPED_UNICODE);
        die;
    }

    /**
     * 文章分类API
     */
    public function article_category()
    {
        $category_article = new ArticleCategoryModel();
        $category         = $category_article->field("id,name")->where('status', 1)->select();
        echo json_encode(["code" => 200, "data" => $category], JSON_UNESCAPED_UNICODE);
    }



    /**
     * 文章api
     * @return false|string
     * @throws \think\db\exception\DbException
     */
    public function article()
    {
        $categoryId = $this->request->param("category");
        if (!empty($categoryId)) {
            $articleModel = new ArticleModel();
            $where        = ["category" => $categoryId, "status" => 1];
            $article      = $articleModel->where($where)->order('id desc')->paginate();
            return json_encode(["code" => 200, "data" => $article]);
        } else {
            return json_encode(["code" => 404, "data" => "未提供分类ID"], JSON_UNESCAPED_UNICODE);
        }
    }

    public function add_view(){
        $article_id = $this->request->param("article_id");

        if(empty($article_id)){
            return json_encode(["code" => 404, "data" => "未提供分类ID"], JSON_UNESCAPED_UNICODE);
        }
        $articleModel = new ArticleModel();
        $article= $articleModel->where('id',$article_id)->find();
        if(empty($article)){
            echo json_encode(["code" => 200, "data" => [],'msg'=>'没找到该文章'], JSON_UNESCAPED_UNICODE);
        }else{
            $articleModel->save([
                'view'  => $article['view']+1,
            ],['id' => $article_id]);
        }
    }
    /**
     * 返佣产品列表  get需要获取产品类型  state
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function fanyong()
    {
        $state = $this->request->param("state");
        if (!empty($state)) {
            $where        = ["state" => $state, "status" => 1];
            $fanyongModel = new FanyongModel();
            $list         = $fanyongModel->where($where)->order('list_order','desc')->select();
            return json_encode(["code" => 200, "data" => $list]);
        } else {
            return json_encode(["code" => 404, "data" => "未提供产品类型"], JSON_UNESCAPED_UNICODE);
        }

    }

    // 返佣产品详情
    public function fanyong_detail()
    {
        $id = $this->request->param("id");
        if (!empty($id)) {
            $where        = ["id" => $id, "status" => 1];
            $fanyongModel = new FanyongModel();
            $row          = $fanyongModel->where($where)->find();
            return json_encode(["code" => 200, "data" => $row]);
        } else {
            return json_encode(["code" => 0, "data" => "非法数据"], JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     *轮播图
     */
    public function banner()
    {
        $slide_id    = $this->request->param("slide_id");
        $bannerModel = new SlideItemModel();
        $banner      = $bannerModel->where("slide_id", $slide_id)->select();
        return json_encode(["code" => 200, "data" => $banner], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 超市分类
     * @return false|string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function chaoshi_category()
    {
        $categoryId = $this->request->param("category");
        $category   = Db("chaoshi_category")->where("id", $categoryId)->select();
        return json_encode(["code" => 200, "data" => $category], JSON_UNESCAPED_UNICODE);
    }


    public function update()
    {
        $param = $this->request->param();
        if (empty($param['name'])) {
            return json_encode(["code" => 0, "data" => [], "msg" => "名称不能为空"], JSON_UNESCAPED_UNICODE);
        }
        if (empty($param['version'])) {
            return json_encode(["code" => 0, "data" => [], "msg" => "版本号不能空"], JSON_UNESCAPED_UNICODE);
        }

        // $row = db('app')->where(['name'=>$param['name'],'version'=>$param['version']])->order('id desc')->find();
        //  $row = db('app')->where(['name'=>$param['name'],'version'=>['gt',$param['version']]])->order('id desc')->find();

        $row            = db('app')->where(['name' => $param['name'], 'update' => 1])->where("version > {$param['version']}")->order('id desc')->find();
        $row['version'] = rtrim(chunk_split($row['version'], 1, '.'), '.');

        if (!empty($row)) {
            return json_encode(["code" => 200, "data" => $row, 'msg' => '成功'], JSON_UNESCAPED_UNICODE);
        }
        return json_encode(["code" => 0, "msg" => '数据不存在'], JSON_UNESCAPED_UNICODE);
    }


    /**
     * 2021年7月8日16:2:35
     */
    public function tags()
    {
        $name = $this->request->param("name");
        if (empty($name)) {
            echo json_encode(["code" => 0, "msg" => "name不能为空"], JSON_UNESCAPED_UNICODE);
            die;
        }
        if ($name == 'today_hot') {
            $hot_list  = ChaoshiModel::where('status', 1)->where('hot_list', 1)->order("list_order", "asc")->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->select()->toArray();//热门排行
            $today_hot = ChaoshiModel::where('status', 1)->where('today_hot', 1)->order("list_order", "asc")->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->select()->toArray(); //今日热门
            foreach ($hot_list as $k => $v) {
                $catgory                   = ChaoshiCategoryModel::where(['id' => $v['category']])->field('id cate,name cate_name,logo cate_logo')->find()->toArray();
                $hot_list[$k]['cate']      = $catgory['cate'];
                $hot_list[$k]['cate_name'] = $catgory['cate_name'];
                $hot_list[$k]['cate_logo'] = $catgory['cate_logo'];
            }
            foreach ($today_hot as $k => $v) {
                $catgory                    = ChaoshiCategoryModel::where(['id' => $v['category']])->field('id cate,name cate_name,logo cate_logo')->find()->toArray();
                $today_hot[$k]['cate']      = $catgory['cate'];
                $today_hot[$k]['cate_name'] = $catgory['cate_name'];
                $today_hot[$k]['cate_logo'] = $catgory['cate_logo'];
            }
            echo json_encode(["code" => 200, "data" => ['hot' => $hot_list, 'today' => $today_hot]], JSON_UNESCAPED_UNICODE);
            die;
        } elseif ($name == 'today_update') {
            $data = ChaoshiModel::where('status', 1)->where('today_update', 1)->order("list_order", "asc")->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->select()->toArray();//今日更新
            foreach ($data as $k => $v) {
                $catgory               = ChaoshiCategoryModel::where(['id' => $v['category']])->field('id cate,name cate_name,logo cate_logo')->find()->toArray();
                $data[$k]['cate']      = $catgory['cate'];
                $data[$k]['cate_name'] = $catgory['cate_name'];
                $data[$k]['cate_logo'] = $catgory['cate_logo'];
            }
            echo json_encode(["code" => 200, "data" => $data], JSON_UNESCAPED_UNICODE);
            die;
        } elseif ($name == "new_loan") {
            $data = ChaoshiModel::where('status', 1)->where('new_loan', 1)->order("list_order", "asc")->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->select()->toArray();//今日更新
            foreach ($data as $k => $v) {
                $catgory               = ChaoshiCategoryModel::where(['id' => $v['category']])->field('id cate,name cate_name,logo cate_logo')->find()->toArray();
                $data[$k]['cate']      = $catgory['cate'];
                $data[$k]['cate_name'] = $catgory['cate_name'];
                $data[$k]['cate_logo'] = $catgory['cate_logo'];
            }
            echo json_encode(["code" => 200, "data" => $data], JSON_UNESCAPED_UNICODE);
            die;
        } elseif ($name == 'series') {
            $data_ChaoshiCategory = ChaoshiCategoryModel::where('state', 1)->where('category', 1)->order("list_order", "asc")->select()->toArray();
            foreach ($data_ChaoshiCategory as $k => $v) {
                $data_Chaoshi = ChaoshiModel::where('status', 1)->where('category', $v['id'])->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->order("list_order", "asc")->select()->toArray();
                foreach ($data_Chaoshi as $k1 => $v2) {
                    $data_Chaoshi[$k1]['cate']      = $v['id'];
                    $data_Chaoshi[$k1]['cate_name'] = $v['name'];
                    $data_Chaoshi[$k1]['cate_logo'] = $v['logo'];
                }
                $data_ChaoshiCategory[$k]['children'] = $data_Chaoshi;
            }
            echo json_encode(["code" => 200, "data" => $data_ChaoshiCategory], JSON_UNESCAPED_UNICODE);
            die;
        } elseif ($name == "lending") {
            $data_ChaoshiCategory = ChaoshiCategoryModel::where('state', 1)->where('category', 2)->order("list_order", "asc")->select()->toArray();
            foreach ($data_ChaoshiCategory as $k => $v) {
                $data_ChaoshiCategory[$k]['children'] = ChaoshiModel::where('status', 1)->where('category', $v['id'])->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->order("list_order", "asc")->select()->toArray();
            }
            echo json_encode(["code" => 200, "data" => $data_ChaoshiCategory], JSON_UNESCAPED_UNICODE);
            die;
        }

        echo json_encode(["code" => 0, "msg" => "非法数据"], JSON_UNESCAPED_UNICODE);
        die;
    }

    public function search()
    {
        $name = $this->request->param("name");
        if (empty($name)) {
            echo json_encode(["code" => 0, "msg" => "name不能为空"], JSON_UNESCAPED_UNICODE);
            die;
        }
        $data = ChaoshiModel::where('status', 1)->where('name', 'like', "%{$name}%")->order("list_order", "asc")->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->select()->toArray();//热门排行
        foreach ($data as $k => $v) {
            $category              = ChaoshiCategoryModel::where(['id' => $v['category']])->field('id cate,name cate_name,logo cate_logo')->find()->toArray();
            $data[$k]['cate']      = $category['cate'];
            $data[$k]['cate_name'] = $category['cate_name'];
            $data[$k]['cate_logo'] = $category['cate_logo'];
        }
        echo json_encode(["code" => 200, "data" => $data], JSON_UNESCAPED_UNICODE);
        die;
    }

    /**
     * 返佣产品单页详情
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function fanyong_list()
    {
        $id = $this->request->param("id");
        if (!empty($id)) {
            $list = Db("fanyong")->where("id", $id)->find();
            if($list['status'] ==1){
                echo json_encode(["code" => 200, "data" => $list], JSON_UNESCAPED_UNICODE);
            }else{
                echo json_encode(["code" => 200, "data" => [],'msg'=>'产品已下架，请联系客服。'], JSON_UNESCAPED_UNICODE);
            }
            die;
        } else {
            echo json_encode(["code" => 200, "data" => "null", "msg" => "未查询到产品"], JSON_UNESCAPED_UNICODE);
            die;
        }
    }

    /**
     * 申请订单  申请产品
     */
    public function to_product(){
        if($this->request->post()){
            $UserModel = new UserModel();
            $data = $this->request->param(["pid","name","tel","p_id","p_title"]);

            $fanyong=db('fanyong')->where('id',$data['p_id'])->find();

            if(empty($fanyong['status'])){
                echo json_encode(["code"=>23,"data"=>"产品已下架，请联系客服。"],JSON_UNESCAPED_UNICODE);die;
            }
            $state=db('fanyong')->where('id',$data['p_id'])->value('state');

            $get_data = [
                "status" => 2,
                "ment" => $UserModel->GetOs(),
                "time" => time(),
                "state" => $state
            ];
            $in_data = array_merge($data,$get_data);
            $request = db("fanyong_order")->insert($in_data);
            if($request){
                echo json_encode(["code"=>200,"data"=>"申请成功"],JSON_UNESCAPED_UNICODE);die;
            }else{
                echo json_encode(["code"=>23,"data"=>"抱歉，未能提交成功，请检查填写。"],JSON_UNESCAPED_UNICODE);die;
            }
        }else{
            echo json_encode(["code"=>24,"data"=>"抱歉，提交方式有误"],JSON_UNESCAPED_UNICODE);die;
        }

    }


    public function category_product()
    {
        $cid = $this->request->param("cid");
        if (empty($cid)) {
            echo json_encode(["code" => 0, "msg" => "参数不能为空"], JSON_UNESCAPED_UNICODE);
            die;
        }
        $data = ChaoshiModel::where('status', 1)->where('category', $cid)->order("list_order", "asc")->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->select()->toArray();//热门排行
        foreach ($data as $k => $v) {
            $category              = ChaoshiCategoryModel::where(['id' => $v['category']])->field('id cate,name cate_name,logo cate_logo')->find()->toArray();
            $data[$k]['cate']      = $category['cate'];
            $data[$k]['cate_name'] = $category['cate_name'];
            $data[$k]['cate_logo'] = $category['cate_logo'];
        }
        echo json_encode(["code" => 200, "data" => $data], JSON_UNESCAPED_UNICODE);
        die;

        // $cid = $this->request->param("cid");
        // $data_ChaoshiCategory = ChaoshiCategoryModel::where('state', 1)->where('id',$cid)->order("list_order", "asc")->select()->toArray();
        // foreach ($data_ChaoshiCategory as $k => $v) {
        //     $data_ChaoshiCategory[$k]['children'] = ChaoshiModel::where('status', 1)->where('category', $v['id'])->field('*,FROM_UNIXTIME(update_time,"%Y-%m-%d %H:%i:%s") update_time_text')->order("list_order", "asc")->select()->toArray();
        // }
        // echo json_encode(["code" => 200, "data" => $data_ChaoshiCategory], JSON_UNESCAPED_UNICODE);
        // die;
    }


}
