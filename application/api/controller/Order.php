<?php


namespace app\api\controller;

use app\common\controller\Api;

/**
 * sg-商品订单接口
 * User: haoyu
 * Date: 2022/4/6
 * Time: 9:18
 */
class Order extends  Api
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
     * 订单管理接口
     * @ApiTitle    (订单管理接口（显示团购信息）)
     * @ApiSummary  (list)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Order/list)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="小程序用户")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    data':array(
    "name": 商品名称,
    "usetime": 使用时间,
    "shop_name": 店铺名称,
    "image": 商品图片,
    )})
     */
    public function list()
    {
        $member_id = $this->request->request("member_id");
        if($member_id > 0)
        {
          $data = db("groupc gc")->where("gc.member_id",$member_id)
                ->where("gc.status","已发货")
                ->field("gs.name,gs.main_image as image,FROM_UNIXTIME(gc.usetime,'%Y-%c-%d %h:%i:%s') as usetime,mi.shop_name")
                ->join("group gp","gc.group_id = gp.group_id","left")
                ->join("goods gs","gs.id = gp.goods_id","left")
                ->join("app_member_info mi","gc.shop_id = mi.crm_shop_id","left")
                ->select();
          foreach ($data as &$value)
          {
              $value['type'] = "拼团";
          }
          $this->success("返回成功",$data);
        }else{
            $this->error("参数有误");
        }
    }
}