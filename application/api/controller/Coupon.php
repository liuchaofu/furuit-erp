<?php

namespace app\api\controller;

use app\common\library\token\driver\Redis;
use think\Cache;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;

/**
 * sg-优惠券接口
 */
class Coupon extends Common
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
     * 我的优惠券列表
     *
     * @ApiTitle    (我的优惠券列表)
     * @ApiSummary  (coupondList)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Coupon/coupondlist)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="用户ID")
     * @ApiParams (name="status", type="varchar", required=true, description="当前状态:'已领取','已使用','已过期'")
     * @ApiParams (name="pages", type="int", required=true, description="页码")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "coupond_id": 1,
    "coupon_id": 1,
    "member_id": 1,
    "shop_id": 核销店铺ID,
    "usetime": 使用时间,
    "gettime": 领取时间,
    "number": 1,
    "code": "优惠券码",
    "status": "当前状态",
    "name": "优惠券名称",
    "price": "优惠券面值",
    "background_image": "优惠券背景图",
    "type": "折扣方式",
    "useetime": 使用截止日期
    )})
     */
    public function coupondlist()
    {
        $member_id = input("member_id", null);
        $status = input("status", "已领取");
        $pages = abs(input("pages", 1));

        $coupondTable = Db::name("coupond d");

        $limit = 100;
        $start = ($pages - 1) * $limit;


        $coupondList = $coupondTable
            ->join("sg_coupon c", "d.coupon_id=sg_coupon.coupon_id", "left")
            ->field("d.*,c.name,c.price,c.background_image,c.type,c.useetime,c.describe,c.full_minus")
            ->where("d.member_id", "=", $member_id)
            ->where("d.status", "=", $status)
            ->order("d.coupond_id", "desc")
            ->limit($start, $limit)
            ->select();

        foreach ($coupondList as &$value) {
            $string = "";
            switch ($value['type']) {
                case "满减":
                    $string = "满" . $value['full_minus'] . "元减" . $value['price'] . "元";
                    break;
                case "抵用":
                    $string = "抵用" . $value['price'] . "元";
                    break;
            }
            $value['string'] = $string;
        }
        $datetime = ['usetime', 'gettime', 'useetime'];
        $coupondList = $this->dateformate($coupondList, $datetime);


        if ($coupondList) {
            $this->success('返回成功', $coupondList);
        } else {
            $this->error('没有更多了');
        }
    }

    /**
     * 优惠券列表
     *
     * @ApiTitle    (优惠券列表)
     * @ApiSummary  (couponList)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Coupon/couponlist)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="用户ID")
     * @ApiParams (name="status", type="varchar", required=true, description="当前状态:'可领取','已过期','未来可领取'")
     * @ApiParams (name="pages", type="int", required=true, description="页码")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "coupon_id": 1,
    "name": "优惠券名称",
    "describe": "优惠券描述",
    "price": "优惠券面值",
    "background_image": "优惠券背景图",
    "total": 发行量,
    "left": 剩余量,
    "type": "折扣方式：'满减','折扣','抵用'",
    "owntype": "时间类型：'领取计算时长','固定时长'",
    "max": 1,
    "getstime": "2022-03-17 09:44:18",
    "getetime": "2022-04-10 09:44:18",
    "usestime": "2022-03-17 09:44:18",
    "useetime": 1649555058,
    "createtime": 1647481528
    }
    )})
     */
    public function couponList()
    {
        $member_id = input("member_id", null);
        $status = input("status", "可领取");
        $pages = abs(input("pages", 1));

        $couponTable = Db::name("coupon");

        $limit = 10;
        $start = ($pages - 1) * $limit;
        $time = time();

        $where = array();
        switch ($status) {
            case "可领取":
                $where['getstime'] = ["<", $time];
                $where['getetime'] = [">", $time];
                break;
            case "已过期":
                $where['getetime'] = ["<", $time];
                break;
            case "未来可领取":
                $where['getstime'] = ["<", $time];
                break;
            default:
                break;
        }

        $where['voucherdata'] = "receive";

        $couponList = $couponTable
            ->where($where)
            ->order("coupon_id", "desc")
            ->limit($start, $limit)
            ->select();

        $filed = ['getstime', 'getetime', 'usestime', 'usestime'];
        $couponList = $this->dateformate($couponList, $filed);
        if ($couponList) {
            $this->success('返回成功', $couponList);
        } else {
            $this->error('没有更多了');
        }
    }

    /**
     * 领取优惠券
     *
     * @ApiTitle    (领取优惠券)
     * @ApiSummary  (getCoupon)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Coupon/getCoupon)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="用户ID")
     * @ApiParams (name="number", type="int", required=true, description="领取数量，默认为1 列表里面的max 字段")
     * @ApiParams (name="coupon_id", type="varchar", required=true, description="优惠券ID")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(

    }
    )})
     */
    public function getCoupon()
    {
        $member_id = input("member_id", null);
        $coupon_id = input("coupon_id", null);

        $check = Cache::store('redis')->get($member_id . $coupon_id);

        if ($check == 1) {
            $this->success('在抢券中');
        } elseif ($check == 2) {
            $this->success('已抢过该券');
        }else{
            Cache::store('redis')->set($member_id . $coupon_id, 1);
        }


        //读取配置表里的领取数量
        $data = \db('coupon')
            ->where('coupon_id', $coupon_id)
            ->field('name,max,left')
            ->find();
        //数量
        $number = $data['max'];
        $left = $data['left'];
        //判断数量够不够
        if ($number > $left) {
            $this->error('优惠券数量不足');
        }


        $couponTable = Db::name("coupon");
        $coupondTable = Db::name("coupond");
        //领取的券
        $earn = "receive";

        $time = time();
        //检查优惠券领取条件
        $couponInfo = $couponTable
            ->where("coupon_id", "=", $coupon_id)
            ->where("getstime", "<", $time)
            ->where("voucherdata", $earn)
            ->where("getetime", ">", $time)
            ->where("left", ">", 0)
            ->find();

        if ($couponInfo) {
            //查询是否已经领取上限
            $coupondsum = $coupondTable
                ->where("coupon_id", "=", $coupon_id)
                ->where("member_id", "=", $member_id)
                ->sum("number");
            if ($coupondsum + $number > $couponInfo['max']) {
                $this->error("该优惠券已达到领取上限");
            } else {
                //先减少优惠券
                $flag = $couponTable->where("coupon_id", "=", $coupon_id)->where("left", ">=", $number)->setDec("left", $number);
                if ($flag) {
                    //根据传来的max 数量
                    for ($x = 1; $x <= $number; $x++) {
                        //插入数据
                        $coupondData = array(
                            'member_id' => $member_id,
                            'coupon_id' => $coupon_id,
                            'gettime' => $time,
                            'number' => 1,
                            'code' => $this->couponCode(),
                            'status' => '已领取',
                        );
                        $insertflag = $coupondTable->insertGetId($coupondData);
                    }

                    if ($insertflag) {
                        Cache::store('redis')->set($member_id . $coupon_id,2);
                        $this->success("领取成功", $insertflag);
                    } else {
                        //手动回滚数据
                        $couponTable->where("coupon_id", "=", $coupon_id)->setInc("left", $number);
                        $this->error("优惠券领取失败");
                    }
                } else {
                    $this->error("该优惠券已被抢完");
                }
            }
        } else {
            $this->error("该优惠券已被抢完");
        }
    }


    /**
     * 手动发券列表
     *
     * @ApiTitle    (手动发券列表)
     * @ApiSummary  (showIssue)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Coupon/showIssue)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="status", type="varchar", required=true, description="当前状态:'可领取','已过期','未来可领取'")
     * @ApiParams (name="pages", type="int", required=true, description="页码")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "coupon_id": 1,
    "name": "优惠券名称",
    "describe": "优惠券描述",
    "price": "优惠券面值",
    "background_image": "优惠券背景图",
    "total": 发行量,
    "left": 剩余量,
    "type": "折扣方式：'满减','折扣','抵用'",
    "owntype": "时间类型：'领取计算时长','固定时长'",
    "max": 1,
    "getstime": "2022-03-17 09:44:18",
    "getetime": "2022-04-10 09:44:18",
    "usestime": "2022-03-17 09:44:18",
    "useetime": 1649555058,
    "createtime": 1647481528
    "voucherdata": "issue" 手动发券
    }
    )})
     */
    public function showIssue()
    {

        $pages = abs(input("pages", 1));
//        $status = input("status", "可领取");
        $limit = 10;
        $start = ($pages - 1) * $limit;
        $earn = "issue";
        $time = time();
        $where = array();
        $where['getstime'] = ["<", $time];
        $where['getetime'] = [">", $time];
        /*   switch ($status) {
               case "可领取":
                   $where['getstime'] = ["<", $time];
                   $where['getetime'] = [">", $time];
                   break;
               case "已过期":
                   $where['getetime'] = ["<", $time];
                   break;
               case "未来可领取":
                   $where['getstime'] = ["<", $time];
                   break;
               default:
                   break;
           }*/

        $couponList = \db('coupon')
            ->where($where)
            ->where("voucherdata", $earn)
            ->order("coupon_id", "desc")
            ->limit($start, $limit)
            ->select();

        foreach ($couponList as &$value) {
            $string = "";
            switch ($value['type']) {
                case "满减":
                    $string = "满" . $value['full_minus'] . "元减" . $value['price'] . "元";
                    break;
                case "抵用":
                    $string = "抵用" . $value['price'] . "元";
                    break;
            }
            $value['string'] = $string;
        }

        $filed = ['getstime', 'getetime', 'usestime', 'usestime'];
        $couponList = $this->dateformate($couponList, $filed);

        if ($couponList) {
            $this->success('返回成功', $couponList);
        } else {
            $this->error('没有更多了');
        }

    }

    /**
     * 发送优惠券
     *
     * @ApiTitle    (发送优惠券)
     * @ApiSummary  (sendCoupon)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Coupon/sendCoupon)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="mobile", type="int", required=true, description="用户手机号")
     * @ApiParams (name="coupon_id", type="varchar", required=true, description="优惠券ID")
     * @ApiParams (name="member_id", type="int", required=true, description="当前用户member_id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(

    }
    )})
     */
    public function sendCoupon()
    {
        $coupon_id = input("coupon_id", null);
        $mobile = input("mobile", null);
        $send_member_id = input("member_id", null);


        //读取配置表里的领取数量
        $data = \db('coupon')
            ->where('coupon_id', $coupon_id)
            ->field('name,max,left')
            ->find();

        //数量
        $number = $data['max'];
        $left = $data['left'];
        //判断数量够不够
        if ($number > $left) {
            $this->error('优惠券数量不足');
        }


        $couponTable = Db::name("coupon");
        $coupondTable = Db::name("coupond");
        //领取的券
        $earn = "issue";

        $time = time();
        //检查优惠券领取条件
        /*  $couponInfo = $couponTable
              ->where("coupon_id", "=", $coupon_id)
              ->where("getstime", "<", $time)
              ->where("voucherdata", $earn)
              ->where("getetime", ">", $time)
              ->where("left", ">", 0)
              ->find();*/


        //查询该用户是不是在数据库里面 member_info
        $data = \db('app_member_info')
            ->where('phone', $mobile)
            ->find();

        if ($data) {
            $member_id = $data['member_id'];
            /* //判断一天只能发一次
             $check = \db('coupond')
                 ->where('coupon_id', $coupon_id)
                 ->where('number', $number)
                 ->where('send_member_id', $send_member_id)
                 ->select();

             foreach ($check as $k => $value) {
                 $check[$k]['time'] = date('Y-m-d', $value['gettime']);
                 if ($check[$k]['time'] == date('Y-m-d')) {
                     //如果等于今天，
                     if ($value['coupon_id'] == $coupon_id && $value['send_member_id'] == $send_member_id && $value['member_id'] == $member_id) {
                         $this->error('您今天已经发券过了！');
                     }

                 }

             }*/

            Db::startTrans();
            try {
                //发券
                //先减少优惠券
                $flag = $couponTable->where("coupon_id", "=", $coupon_id)->where("left", ">=", $number)->setDec("left", $number);
                Db::commit();
            } catch (ValidateException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            if ($flag) {
                //根据传来的max 数量
                for ($x = 1; $x <= $number; $x++) {
                    //插入数据
                    $coupondData = array(
                        'member_id' => $member_id,
                        'coupon_id' => $coupon_id,
                        'gettime' => $time,
                        'number' => 1,
                        'code' => $this->couponCode(),
                        'status' => '已领取',
                        'shop_id' => 0,//默认未核销
                        'send_member_id' => $send_member_id
                    );
                    $insertflag = $coupondTable->insertGetId($coupondData);

                }
                if ($insertflag) {
                    $this->success("发送成功", $insertflag);
                } else {
                    //手动回滚数据
                    $couponTable->where("coupon_id", "=", $coupon_id)->setInc("left", $number);
                    $this->error("优惠券发送失败");
                }
            } else {
                $this->error("该优惠券不符合发送条件，数量不足");
            }
        } else {
            $this->error('无该用户');
        }


    }


//查看优惠券时间有误过期 ----定时访问
    public function expired()
    {
        //更改优惠券
        $cart = db('coupond')
            ->alias('c')
            ->join('coupon g', 'c.coupon_id =g.coupon_id')
            ->where('c.status', "已领取")
            ->field('c.*,g.name,g.price,g.background_image,g.useetime')
            ->order('c.coupond_id desc')
            ->select();


        $time = time();
        $status = ['status' => "已过期"];
        foreach ($cart as $k => $value) {
            //如果当前用户券使用时间小于了当天时间 该状态为已经过期
            if ($time > $value['useetime']) {
                $change = \db('coupond')
                    ->where('coupond_id', $value['coupond_id'])
                    ->update($status);

                if($change === false){
                    $this->error('更改失败');
                }
            }
        }

        //活动状态更改
        $activity = db('activity')
            ->alias('a')
            ->field('a.*')
            ->order('a.id desc')
            ->select();


        $state1 = ['status' => "已停用"];
        foreach ($activity as $k => $value) {
            //如果当前用户券使用时间小于了当天时间 该状态为已经过期
            if ($time > $value['etime']) {
                $change1 = \db('activity')
                    ->where('id', $value['id'])
                    ->update($state1);

                if($change1 === false){
                    $this->error('更改失败');
                }
            }
        }



        //拼团状态更改
        $group = db('group')
            ->alias('c')
            ->field('c.*')
            ->order('c.group_id desc')
            ->select();
        //更改未开始到已开始
        $startState = ['status' => "已开始"];
        $now_states ="未开始";
        foreach ($group as $k => $value) {
            //如果当前用户券使用时间小于了当天时间 该状态为已经过期
            if ($time > $value['stime']) {
                $change2 = \db('group')
                    ->where('group_id', $value['group_id'])
                    ->where('status',$now_states)
                    ->update($startState);

                if($change2 === false){
                    $this->error('更改失败');
                }
            }
        }


        //结束时间小于当前时间
        $state = ['status' => "已结束"];
        foreach ($group as $k => $value) {
            //如果当前用户券使用时间小于了当天时间 该状态为已经过期
            if ($time > $value['etime']) {
                $change3 = \db('group')
                    ->where('group_id', $value['group_id'])
                    ->update($state);

                if($change3 === false){
                    $this->error('更改失败');
                }
            }
        }

        //5分钟一次更改拼团参与状态
        $groupc = db('groupc')
            ->alias('c')
            ->field('c.*')
            ->order('c.group_id desc')
            ->select();

        $ok = ['status' => "成功"];
        $now ="已参与";
        foreach ($groupc as $k => $value) {
            //
            $changeStatus = \db('groupc')
                ->where('groupc_id', $value['groupc_id'])
                ->where('status',$now)
                ->update($ok);
            if($changeStatus === false){
                $this->error('更改失败');
            }

        }


        $this->success('更改成功');

    }

    /**
     * 抢券列表
     *
     * @ApiTitle    (抢券列表)
     * @ApiSummary  (grabCoupon)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Coupon/grabCoupon)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="pages", type="int", required=true, description="页码")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "coupon_id": 1,
    "name": "优惠券名称",
    "describe": "优惠券描述",
    "price": "优惠券面值",
    "background_image": "优惠券背景图",
    "total": 发行量,
    "left": 剩余量,
    "type": "折扣方式：'满减','折扣','抵用'",
    "owntype": "时间类型：'领取计算时长','固定时长'",
    "max": 3,
    "getstime": "2022-03-17 09:44:18",
    "getetime": "2022-04-10 09:44:18",
    "usestime": "2022-03-17 09:44:18",
    "useetime": 1649555058,
    "createtime": 1647481528
    "voucherdata": "share" 抢券
    "string" :  拼接
    }
    )})
     */
    public
    function grabCoupon()
    {

        $pages = abs(input("pages", 1));
//        $status = input("status", "可领取");
        $limit = 10;
        $start = ($pages - 1) * $limit;
        $earn = "share";
        $time = time();
        $where = array();
        $where['getstime'] = ["<", $time];
        $where['getetime'] = [">", $time];
        /*   switch ($status) {
               case "可领取":
                   $where['getstime'] = ["<", $time];
                   $where['getetime'] = [">", $time];
                   break;
               case "已过期":
                   $where['getetime'] = ["<", $time];
                   break;
               case "未来可领取":
                   $where['getstime'] = ["<", $time];
                   break;
               default:
                   break;
           }*/

        $couponList = \db('coupon')
            ->where($where)
            ->where("voucherdata", $earn)
            ->order("coupon_id", "desc")
            ->limit($start, $limit)
            ->select();

        foreach ($couponList as &$value) {
            $string = "";
            switch ($value['type']) {
                case "满减":
                    $string = "满" . $value['full_minus'] . "元减" . $value['price'] . "元";
                    break;
                case "抵用":
                    $string = "抵用" . $value['price'] . "元";
                    break;
            }
            $value['string'] = $string;
        }

        $filed = ['getstime', 'getetime', 'usestime', 'usestime'];
        $couponList = $this->dateformate($couponList, $filed);

        if ($couponList) {
            $this->success('返回成功', $couponList);
        } else {
            $this->error('没有更多了');
        }

    }

    /**
     * 抢券
     *
     * @ApiTitle    (抢券)
     * @ApiSummary  (getGrabCoupon)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Coupon/getGrabCoupon)
     * @ApiParams (name="coupon_id", type="varchar", required=true, description="优惠券ID")
     * @ApiParams (name="member_id", type="int", required=true, description="当前用户member_id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(

    }
    )})
     */
    public function getGrabCoupon()
    {
        $data = input('post.');
        $member_id = isset($data['member_id']) ? $data['member_id'] : '';
        $coupon_id = isset($data['coupon_id']) ? $data['coupon_id'] : '';
        if (empty($member_id) || empty($coupon_id)) {
            $this->error('参数有误');
        }
        //redis缓存
        $check = Cache::store('redis')->get($member_id . $coupon_id);
        if ($check == 1) {
            $this->success('在抢券中');
        } elseif ($check == 2) {
            $this->error('您今天已经抢过该类型的券了');
        }else{
            Cache::store('redis')->set($member_id . $coupon_id, 1);
        }
        //判断有无此人
        $member = \db('app_member')->where('id', $member_id)->count();
        if (empty($member)) {
            Cache::store('redis')->rm($member_id . $coupon_id);
            $this->error('没有您的信息请注册！');
        }
        //获取当天的开始时间和结束时间
        $timeDeal = $this->getTime();
        //判断用户一天只能抢同一类的一次
        $checkCount = \db('coupond')
            ->where('coupon_id', $coupon_id)
            ->whereBetween("gettime", $timeDeal['s'].','.$timeDeal['e'])
            ->where('member_id', $member_id)->count();
        if($checkCount > 0)
        {
            Cache::store('redis')->set($member_id . $coupon_id,2);
            $this->error('您今天已经抢过该类型的券了！');
        }

        //读取配置表里的领取数量
        $data = \db('coupon')
            ->where('coupon_id', $coupon_id)
            ->field('name,max,left')
            ->find();
        if (empty($data)) {
            Cache::store('redis')->rm($member_id . $coupon_id);
            $this->error('没有该券！');
        } else {
            //数量
            $number = $data['max'];
            $left = $data['left'];
            //判断数量够不够
            if ($number > $left) {
                Cache::store('redis')->rm($member_id . $coupon_id);
                $this->error('优惠券数量不足');
            }
            //判断券有没有被抢完
            if ($left > 0) {
                $insertFlag = 0;
                Db::startTrans();
                try {
                    $couponTable = Db::name("coupon");
                    $coupondTable = Db::name("coupond");
                    $time = time();
                    //发券
                    //先减少优惠券
                    //$number = 1;//一张
                    $flag = $couponTable->where("coupon_id", "=", $coupon_id)
                        ->where("left", ">=", $number)->setDec("left", $number);
                    if ($flag) {
                        $couponDData = array();
                        //根据传来的max 数量
                        for ($x = 1; $x <= $number; $x++) {
                            //插入数据
                            $couponDData[] = array(
                                'member_id' => $member_id,
                                'coupon_id' => $coupon_id,
                                'gettime' => $time,
                                'number' => 1,
                                'code' => $this->couponCode(),
                                'status' => '已领取',
                                'shop_id' => 0,//默认未核销
                                'send_member_id' => '',
                                'settlementtime' => ''
                            );
                        }
                        $insertFlag = $coupondTable->insertAll($couponDData);
                        if($insertFlag){
                            Db::commit();
                        }else{
                            Db::rollback();
                        }
                    }else{
                        Db::rollback();
                    }
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                    $this->error("网络超时，请重试");
                }
                if($insertFlag>0)
                {
                    Cache::store('redis')->set($member_id . $coupon_id, 2);
                    $this->success("抢卷成功");
                }else{
                    Cache::store('redis')->rm($member_id . $coupon_id);
                    $this->error("网络超时，请重试");
                }
            } else {
                Cache::store('redis')->rm($member_id . $coupon_id);
                $this->error('已经抢光了，请下次再来');
            }
        }
    }


    /**
     * 天天神券页面
     *
     * @ApiTitle    (天天神券页面)
     * @ApiSummary  (getGrabCoupon)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Coupon/showMore)
     * @ApiParams (name="coupon_id", type="varchar", required=true, description="优惠券ID")
     * @ApiReturn  {
     * "code": 1,
     * "msg": "ok",
     * "time": "1649904801",
     * "data": {
     * "coupon_id": 8,
     * "name": "测试抢券2",
     * "share_total": "15.00",//总金额
     * "string": "满35.00元减5.00元",
     * "total": 3,张数
     * "day": "36天后过期"
     * "coupond": [
     *  {
     *   "nickname": "A 陈洪",
     *   "gettime": "2022-04-13 20:25:39",
     *   "member_id": 11,
     *   "num": 6
     *   }
     *   ]
     * }
     * }
     */

    public function showMore()
    {
        $coupon_id = input('coupon_id');
        $member_id = input('member_id');
        if (!isset($coupon_id)&&empty($coupon_id)&&!isset($member_id)&&empty($member_id)) {
            $this->error('参数有误');
        }
        //redis缓存
        $check = Cache::store('redis')->get($member_id . $coupon_id);
        if($check == 2){
            $this->error('您今天已经抢过该类型的券了！');
        }
        //获取当天的开始时间和结束时间
        $timeDeal = $this->getTime();
        //判断用户一天只能抢同一类的一次
        $check = \db('coupond')
            ->where('coupon_id', $coupon_id)
            ->whereBetween("gettime", $timeDeal['s'].','.$timeDeal['e'])
            ->where('member_id', $member_id)->count();
        if($check > 0)
        {
            Cache::store('redis')->set($member_id . $coupon_id,2);
            $this->error('您今天已经抢过该类型的券了！');
        }


        $data = \db('coupon')
            ->where('coupon_id', $coupon_id)
            ->select();
        $time = time();
        foreach ($data as $k => $value) {
            $string = "";
            switch ($value['type']) {
                case "满减":
                    $string = "满" . $value['full_minus'] . "元减" . $value['price'] . "元";
                    break;
                case "抵用":
                    $string = "抵用" . $value['price'] . "元";
                    break;
            }
            $data[$k]['string'] = $string;
            $day = $value['useetime'] - $time;
            $data[$k]['day'] = floor($day / 86400);
            if ($data[$k]['day'] < 0) {
                $this->error('该券已经过期');
            }
        }

        $filed = ['getstime', 'getetime', 'usestime', 'useetime'];
        $all = $this->dateformate($data, $filed);
        $coupond = \db('coupond cd')
            ->where("cd.coupon_id", $coupon_id)
            ->join("app_member ar", "cd.member_id = ar.id", "left")
            ->field("ar.nickname,FROM_UNIXTIME(cd.gettime,'%Y-%m-%d %H:%i:%s') as gettime,cd.member_id,count(cd.coupond_id) as num")
            ->group("cd.member_id")
            ->select();

        $detai = array(
            'coupon_id' => $all[0]['coupon_id'],
            'name' => $all[0]['name'],
            'share_total' => $all[0]['share_total'],
            'string' => $all[0]['string'],
            'total' => $all[0]['total'],
            'day' => $all[0]['day'] . "天后过期",
            'max' => $all[0]['max'],
            'coupond' => $coupond,
        );
        $this->success('ok', $detai);

    }

    public function test()
    {
        $group = db('group')
            ->alias('c')
            ->field('c.*')
            ->order('c.group_id desc')
            ->select();
        //更改未开始到已开始
        $startState = ['status' => "已开始"];
        $now_states ="未开始";
        $time =time();
        foreach ($group as $k => $value) {
            //如果当前用户券使用时间小于了当天时间 该状态为已经过期
            if ($time > $value['stime']) {
                $change2 = \db('group')
                    ->where('group_id', $value['group_id'])
                    ->where('status',$now_states)
                    ->update($startState);

                if($change2 === false){
                    $this->error('更改失败');
                }
            }
        }
        $this->success('ok');

    }
}
