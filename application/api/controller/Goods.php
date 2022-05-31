<?php


namespace app\api\controller;


use app\common\controller\Api;
use think\Db;
/**
 * sg-商品管理接口
 * User: haoyu
 * Date: 2022/3/29
 * Time: 13:54
 */
class Goods extends Api
{

    //如果$noNeedLogin为空表示所有接口都需要登录才能请求
    //如果$noNeedRight为空表示所有接口都需要验证权限才能请求
    //如果接口已经设置无需登录,那也就无需鉴权了
    //
    // 无需登录的接口,*表示全部
    protected $noNeedLogin = ['*'];
    // 无需鉴权的接口,*表示全部
    protected $noNeedRight = ['*'];



    /**
     * 首页-展示商品信息
     * @ApiTitle    (首页-展示商品信息)
     * @ApiSummary  (list)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Goods/list)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="pages", type="int", required=true, description="页码")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "name": 商品名称,
    "title": 商品简要,
    "city": 商品溯源,
    "main_image": 商品图片,
    )})
     */
    public function list()
    {
        $pages = abs(input("pages",1));
        $limit = 10;
        $start = ($pages - 1) * $limit;

        $goodsList = Db::name("goods")
            ->field("name,title,city,main_image")
            ->order("sort","asc")
            ->limit($start,$limit)
            ->select();
        if($goodsList){
            $this->success('返回成功',$goodsList);
        }else{
            $this->error('没有更多了');
        }
    }
}