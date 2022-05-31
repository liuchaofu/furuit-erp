<?php

namespace app\api\controller;

use app\common\controller\Api;
use think\Db;

/**
 * sg-商户核销接口
 * User: haoyu
 * Date: 2022/3/31
 * Time: 15:29
 */
class Shop extends Api
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
     * 商户核销列表
     * @ApiTitle    (商户核销列表（优惠券）)
     * @ApiSummary  (list)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Shop/list)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="shop_id", type="int", required=true, description="商户id")
     * @ApiParams (name="status", type="string", required=true, description="状态：已使用、已结算")
     * @ApiParams (name="pages", type="int", required=true, description="页数")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    )})
     */
    public function list()
    {
        $shop_id = $this->request->request("shop_id");
        $status= $this->request->request("status","已使用");
        $limit = $this->request->request("limit",20);
        if(!is_numeric($limit)){
            $this->error("页码参数错误");
        }
        $pages = abs(input("pages", 1));
        if (isset($shop_id) && $shop_id > 0) {
            $start = ($pages - 1) * $limit;
            //分页查询优惠券信息
            $sql = db("coupond")->alias("d")
                ->join("coupon cn", "d.coupon_id = cn.coupon_id")
                ->where("d.shop_id", $shop_id)->where("d.status", $status)
                ->field("d.status,d.code,FROM_UNIXTIME(d.usetime,'%Y-%m-%d %H:%i:%s') as usetime,FROM_UNIXTIME(d.settlementtime,'%Y-%m-%d %H:%i:%s') as settlementtime,cn.price");
            $coupon = $sql->limit($start, $limit)
                ->order("d.usetime", "DESC")->select();
            $couponC = 0;

            //查询优惠券总数
            $count_data = db("coupond")->alias("d")
                ->join("coupon cn", "d.coupon_id = cn.coupon_id")
                ->where("d.shop_id", $shop_id)->where("d.status", $status)
                ->field("d.status,cn.price")->select();

            //统计总条数
            $total_num = count($count_data);


            //统计优惠券金额总数
            foreach ($count_data as &$value) {
                    $couponC += $value['price'];
            }
            //统计优惠券金额总数
            foreach ($coupon as &$value) {
                if($value['status'] == "已结算"){
                    $value['usetime'] = $value['settlementtime'];
                }
                unset($value['settlementtime']);
                $value['type'] = "优惠券";
            }
            $array['data'] = $coupon;
            $array['coupon'] = $couponC;
            $array['group'] = 0;
            $array['total_num'] =  $total_num;
            $array['pages'] = $pages;
            $array['total'] = $couponC;
            if($coupon){
                $this->success("返回成功", $array);
            }else{
                $this->error("未查询出数据",$array);
            }
        } else {
            $this->error("参数有误");
        }
    }

    /**
     * 商户核销列表团购
     * @ApiTitle    (商户核销列表（团购）)
     * @ApiSummary  (listGroup)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Shop/listGroup)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="shop_id", type="int", required=true, description="商户id")
     * @ApiParams (name="status", type="string", required=true, description="状态：已发货")
     * @ApiParams (name="pages", type="int", required=true, description="页数")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    )})
     */
    public function listGroup()
    {
        $shop_id = $this->request->request("shop_id");
        $status= $this->request->request("status","已发货");
        $limit = $this->request->request("limit",20);
        if(!is_numeric($limit)){
            $this->error("页码参数错误");
        }
        $pages = abs(input("pages", 1));
        $start = ($pages - 1) * $limit;
        $groupC = 0;
            //查询团购信息
        $sql = Db::name("groupc")
                ->where("shop_id", $shop_id)
                ->where("status", $status)
                ->field("status,code,FROM_UNIXTIME(usetime,'%Y-%m-%d %H:%i:%s') as usetime,purchase_quantity as num");
        $group = $sql->limit($start, $limit)
                ->order("usetime", "DESC")
                ->select();

        foreach ($group as &$value) {
            $value['type'] = "拼团";
        }
        //团购总数
        $count_data = Db::name("groupc")
            ->where("shop_id", $shop_id)
            ->where("status", $status)
            ->field("purchase_quantity as num")->select();
        //统计总条数
        $total_num = count($count_data);

        //统计优惠券金额总数
        foreach ($count_data as &$value) {
            $groupC += $value['num'];
        }

        $array['data'] = $group;
        $array['total_num'] =  $total_num;
        $array['pages'] = $pages;
        $array['total'] = $groupC;
        if($group){
            $this->success("返回成功", $array);
        }else{
            $this->error("未查询出数据",$array);
        }
    }
    /**
     *
     * 商户核销
     * @ApiTitle    (商户核销（优惠券和团购）)
     * @ApiSummary  (writeOff)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Shop/writeOff)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="shop_id", type="int", required=true, description="页码")
     * @ApiParams (name="code", type="string", required=true, description="")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功和失败'
    )})
     */
    public function writeOff()
    {
//        $data = input('post.');
//        $shop_id = $data['shop_id'];
//        $code = $data['code'];
        //参数
        $shop_id =$this->request->request("shop_id");
        $code =$this->request->request("code");
        //拆分参数，多个已逗号隔开
        $shop_a = explode(",", $shop_id);
        if ($shop_a && isset($shop_a[0])) {
            $shop_id = $shop_a[0];

            //执行查询 是否存在商户
            $data = db('app_member_info')
                ->where('crm_shop_id', $shop_id)
                ->find();
            if (empty($data)) {
                //调用crm商铺接口返回数据
                $url = "http://crm.ixinangou.com/api/shop/shopInfo";
                $param = ['shop_id' => $shop_id];

                $receive = $this->http($url, $param, 'POST');
                $info = json_decode($receive, true);
                if (isset($info['data']['shopInfo'])&&!empty($info['data']['shopInfo'])) {
                    //存数据库
                    $memberDate['shop_name'] = $info['data']['shopInfo']['shop_name'];
                    $memberDate['shop_image'] = $info['data']['shopInfo']['image'];
                    $memberDate['name'] = $info['data']['shopInfo']['name'];
                    $memberDate['phone'] = $info['data']['shopInfo']['phone'];
                    $memberDate['id_card'] = $info['data']['shopInfo']['idcard'];
                    $memberDate['city'] = $info['data']['shopInfo']['address'];
                    $memberDate['house_num'] = $info['data']['shopInfo']['door'];
                    $memberDate['createtime'] = time();
                    $memberDate['updatetime'] = time();
                    $memberDate['crm_member_id'] = $info['data']['shopInfo']['member_id'];
                    $memberDate['crm_shop_id'] = $info['data']['shopInfo']['shop_id'];
                    $memberDate['state'] = 1;
                    $memberDate['checkdata'] = 'merchant';
                    //添加商户信息
                    $info_id = db('app_member_info')
                        ->insertGetId($memberDate);
                    if ($info_id) {
                        //调用公共类
                        $this->manage($shop_id,$code);
                    } else {
                        $this->error('添加失败');
                    }
                } else {
                    $this->error('没有该店铺');
                }
            }else{
                //调用公共类
                $this->manage($shop_id,$code);
            }
        } else {
            $this->error("参数有误");
        }
    }

    /**
     * 公共核券方法
     * @param string $shop_id
     * @param string $code
     */
    function manage($shop_id,$code)
    {
        if (isset($shop_id) && $shop_id > 0 && isset($code) && $code) {
            $prefix = substr($code, 0, 2);//从左边第一位字符起截取2位字符
            switch ($prefix) {
                case "SG":
                    //核销优惠券
                    $coupon = Db::name("coupond cd")
                        ->where("cd.code", $code)
                        ->join("coupon cn","cd.coupon_id = cn.coupon_id","left")
                        ->field("cd.coupond_id,cd.status,cn.usestime,cn.useetime")->find();
                    $time = time();
                    //判断优惠券开始使用时间
                    if ($time > $coupon['usestime']){
                        //判断优惠券结束使用时间
                        if ($time < $coupon['useetime']){
                            if ($coupon && isset($coupon['coupond_id']) && isset($coupon['status'])) {
                                if ($coupon['status'] == "已领取") {
                                    $array = array(
                                        'shop_id' => $shop_id,
                                        'usetime' => time(),
                                        'status' => "已使用",
                                    );
                                    $res = Db::name("coupond")
                                        ->where("coupond_id", $coupon['coupond_id'])
                                        ->update($array);
                                    if ($res) {
                                        $this->success("优惠券核销成功", "");
                                    } else {
                                        $this->error("优惠券核销失败,请联系管理");
                                    }
                                } else {
                                    $this->error("优惠券状态为：" . $coupon['status']);
                                }
                            } else {
                                $this->error("优惠券码有误");
                            }
                        }else{
                            $this->error("优惠券已结束使用");
                        }
                    }else{
                        $this->error("优惠券未开始使用");
                    }
                    break;
                case "GC":
                    $group = Db::name("groupc")->where("code", $code)->field("groupc_id,status")->find();

                    if ($group && isset($group['groupc_id']) && isset($group['status'])) {
                        if ($group['status'] == "成功") {
                            $array = array(
                                'shop_id' => $shop_id,
                                'usetime' => time(),
                                'status' => "已发货",
                            );
                            $res = Db::name("groupc")->where("groupc_id", $group['groupc_id'])->update($array);
                            if ($res) {
                                $this->success("团购核销成功", "");
                            } else {
                                $this->error("团购核销失败");
                            }
                        } else {
                            $this->error("团购状态为：" . $group['status']);
                        }
                    } else {
                        $this->error("团购码有误");
                    }
                    break;
                default:
                    $this->error("劵码有误");
            }
        } else {
            $this->error("参数有误");
        }
    }
    /**
     * 首页-店铺排名
     * @ApiTitle    (首页-店铺排名)
     * @ApiSummary  (shopList)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Shop/shopList)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="pages", type="int", required=true, description="页码")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "shop_name": 店铺名称,
    "name": 店铺名字,
    "city": 地址,
    "shop_image": 店铺图片,
    "state":状态（预留字段，现在不管）
    )})
     */

    public function shopList()
    {
        $pages = abs(input("pages", 1));
        $limit = 10;
        $start = ($pages - 1) * $limit;
        $cate ="merchant";
        $shopList = Db::name("app_member_info")
            ->where('checkdata',$cate)
            ->field("shop_name,name,city,shop_image,state")
            ->order("createtime", "asc")
            ->limit($start, $limit)
            ->select();
        foreach ($shopList as $k =>$value){
            $shopList[$k]['shop_image'] = "http://crm.ixinangou.com".$value['shop_image'];
        }


        if ($shopList) {
            $this->success('返回成功', $shopList);
        } else {
            $this->error('没有更多了');
        }

    }



}