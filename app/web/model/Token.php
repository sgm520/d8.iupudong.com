<?php


namespace app\web\model;


use app\web\model\UserModel;
use think\Model;

class Token extends Model
{

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'user_token';
    /**获取token值
     *
     * @return string
     */
    public static function getToken()
    {
        return request()->header('token', '');
    }
    /**设置token
     *
     * @param $user_id
     * @param $timeout_days
     *
     * @return string
     */
    public static function setToken($user_id, $timeout_days = 1)
    {
        $token = md5($user_id . time() . rand(0, 99999));
        self::create(['user_id' => $user_id,  'token' => $token]);
        return $token;
    }

    /**根据token获取user_id
     *
     * @param $token
     *
     * @return int
     */
    public static function getUserIdByToken($token)
    {
        if (empty($token)) {
            return 0;
        }
        $token = self::where('token', $token)->find();
        if (empty($token)) {
            return 0;
        }
        return $token->user_id;
    }
}