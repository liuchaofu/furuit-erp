<?php

namespace app\api\controller;

use app\common\controller\Api;
use Monolog\Handler\HandlerWrapper;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;

/**
 * sg-拼团接口
 */
class Group extends Api
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
     * 团购列表
     *
     * @ApiTitle    (团购列表)
     * @ApiSummary  (groupList)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Group/groupList)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="status", type="varchar", required=true, description="当前状态:'未开始','已开始','已结束','已满员','全部'")
     * @ApiParams (name="pages", type="int", required=true, description="页码")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "group_id": 1,
    "name": "团购名称",
    "describe": "团购描述",
    "image": "团购图片",
    "type": "成团类型",
    "unit": "单位",
    "head_image": "活动头部",
    "background_image": "活动背景",
    "number": 成团数量要求,
    "goods_type": "团购内容",
    "value": 团购内容ID,
    "status": "当前状态",
    "stime": "团购开始时间",
    "etime": "团购结束时间",
    "everytime": 每人单次团购最大购买单数,
    "everyorder": 每人单次活动最大参团次数,
    "maxorder": 最大成单数,
    "days": 拼团邀请时间,
    "createtime": "创建时间"
    )})
     */
    public function groupList()
    {
        $pages = input("pages", 1);
        $status = input("status", "已开始");

        $groupTable = Db::name("group");

        $limit = 6;
        $start = ($pages - 1) * $limit;

        $where = array();

        if ($status == '全部' || $status == "") {

        } else {
            $where['status'] = ['=', $status];
        }

        $groupList = $groupTable
            ->where($where)
            ->limit($start, $limit)
            ->order("stime", "asc")
            ->select();
        $fileds = ['stime', 'etime', 'createtime'];
        $groupList = $this->dateformate($groupList, $fileds);

        if ($groupList) {
            $this->success("查询成功", $groupList);
        } else {
            $this->error("没有更多了");
        }
    }

    /**
     * 报名信息
     *
     * @ApiTitle    (报名信息)
     * @ApiSummary  (info)
     * @ApiMethod   (get)
     * @ApiRoute    (/api/Group/info)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="用户ID test53")
     * @ApiParams (name="id", type="int", required=true, description="id 16")
     * @ApiParams (name="group_id", type="int", required=true, description="group_id 10")
     * @ApiReturn   {
    "code": 1,
    "msg": "ok",
    "time": "1652085605",
    "data": {
    "name": "Jdj",
    "phone": "18715753258",
    "city": "四川省/泸州市/江阳区",
    "house_num": "Hjd",
    "title": "5.9测试活动",
    "activity_image": "/uploads/20220506/a3522b93c3883d65f3304fbb0cee3be2.jpg",
    "minimum": "10",
    "surplus": "990"
    }
    }
     */
    public function info()
    {
        $member_id = $this->request->request('member_id');
        $activity_id = $this->request->request('id');
        $group_id = $this->request->request('group_id');


        $img = \db('activity')
            ->where('id', $activity_id)
            ->field('title,activity_image')
            ->find();


        $data = \db('app_member_info')
            ->where('member_id', $member_id)
            ->field('name,phone,city,house_num')
            ->find();
        $data['title'] = isset($img['title']) ? $img['title'] : '';
        $data['activity_image'] = isset($img['activity_image']) ? $img['activity_image'] : '';
        //返回设置的
        $groupTable = Db::name("group");
        $groupInfo = $groupTable
            ->where("group_id", "=", $group_id)
            ->field('minimum,surplus')
            ->find();

        if (isset($groupInfo)) {
            $data['minimum'] =$groupInfo['minimum'];
            $data['surplus'] =$groupInfo['surplus'];
        }else{
            $data['minimum'] ='';
            $data['surplus'] ='';
        }



        if (isset($data)) {
            $this->success('ok', $data);
        } else {
            $this->error('无数据');
        }

    }


    /**
     * 参团
     *
     * @ApiTitle    (参团)
     * @ApiSummary  (cGroup)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Group/cGroup)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="用户ID")
     * @ApiParams (name="group_id", type="int", required=false, description="拼团活动ID")
     * @ApiParams (name="groupd_id", type="int", required=false, description="拼团ID")
     * @ApiParams (name="type", type="int", required=false, description="参团类型1发起新拼团2参与拼团;默认发起新拼团")
     * @ApiParams (name="quantity", type="varchar", required=true, description="团购数量")
     * @ApiParams (name="phone", type="varchar", required=true, description="手机号")
     * @ApiParams (name="city", type="varchar", required=true, description="地址")
     * @ApiParams (name="name", type="varchar", required=true, description="姓名")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(

    )})
     */
    public function cGroup()
    {
        $group_id = input("group_id", null);
        $groupd_id = input("groupd_id", null);
        $member_id = input("member_id");
        $number = input("number", 1);
        $type = input("type", 1);
        $quantity = input("quantity");
        $phone = input("phone");
        $city = input("city");
        $name = input("name");

        $groupTable = Db::name("group");
        $groupdTable = Db::name("groupd");
        $groupcTable = Db::name("groupc");
        $memberTable = Db::name("app_member");
        //判断数量不足
        $groupInfo = $groupTable->where("group_id", "=", $group_id)->find();
        $left =$groupInfo['surplus'];
        if ($left < $quantity) {
            $this->error('剩余数量不足');
        }

        $memberInfo = $memberTable->where("id", "=", $member_id)->find();
        $time = time();

        $result = false;

        if ($type == 1) {
            //发起新拼团
            if ($group_id == null) {
                $this->error("操作错误");
            } else {
                //检查参与条件
                $this->checkCgroup($member_id, $group_id, $number);
                //满足条件，发起拼团
                $groupInfo = $groupTable->where("group_id", "=", $group_id)->find();
                //判断是什么类型的拼团
                if ($groupInfo['type'] == '人数') {
                    $num = 1;
                } else {
                    $num = $number;
                }
            }
            //插入拼团数据
            Db::startTrans();
            try {
                $groupdData = array(
                    'group_id' => $group_id,
                    'name' => $memberInfo['nickname'] . "发起的拼团",
                    'status' => "拼团中",
                    'max_num' => $groupInfo['number'],
                    'num' => $num,
                    'stime' => $time,
                    'etime' => $time + $groupInfo['days'] * 24 * 60 * 60
                );
                $groupd_id = $groupdTable->insertGetId($groupdData);
                //插入参团数据
                $groupcData = array(
                    'group_id' => $group_id,
                    'groupd_id' => $groupd_id,//原写 $group_id
                    'member_id' => $member_id,
                    'number' => $num,
                    'code' => $this->createGroupcCode(),
                    'status' => '已参与',
                    'createtime' => $time,
                );
                $result = $groupcTable->insertGetId($groupcData);
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
        } else {
            //参与拼团
            if ($groupd_id == null) {
                $this->error("参数错误");
            } else {
                $groupdInfo = $groupdTable->where("groupd_id", "=", $groupd_id)->find();
                $group_id = $groupdInfo['group_id'];

                //检查参与条件
                $this->checkCgroup($member_id, $group_id, $number, $groupd_id);
                //满足条件，发起拼团
                $groupInfo = $groupTable->where("group_id", "=", $group_id)->find();
                //判断是什么类型的拼团
                if ($groupInfo['type'] == '人数') {
                    $num = 1;
                } else {
                    $num = $number;
                }
                //插入拼团数据
                Db::startTrans();
                try {
                    //插入参团数据
                    $groupcData = array(
                        'group_id' => $group_id,
                        'groupd_id' => $groupd_id,
                        'member_id' => $member_id,
                        'number' => $num,
                        'code' => $this->createGroupcCode(),
                        'status' => '已参与',
                        'createtime' => $time,
                        'purchase_quantity' => $quantity,
                        'name' => $name,
                        'phone' => $phone,
                        'city' => $city,

                    );
                    //先增加拼团数量
                    $groupdTable->where("groupd_id", "=", $groupd_id)->setInc("num", $num);
                    $result = $groupcTable->insertGetId($groupcData);

                    //操作group的剩余数量

                    $now_surplus = $groupInfo['surplus'] - $quantity;
                    $data['surplus'] = $now_surplus;

                    $res = db('group')
                        ->where('group_id', $group_id)
                        ->update($data);


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
            }
        }
        if ($result) {
            $this->groupdCheck($groupd_id);
            $this->success("参团成功");
        } else {
            $this->error("参团失败");
        }
    }

    /**
     * 查询是否有未完成的拼团,或者是否已经到达了参团上限
     * @param $member_id
     * @param $group_id
     * @param $number
     * @param null $groupd_id
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function checkCgroup($member_id, $group_id, $number, $groupd_id = null)
    {
        $groupTable = Db::name("group");
        $groupcTable = Db::name("groupc");
        $groupdTable = Db::name("groupd");

        $groupInfo = $groupTable->where("group_id", "=", $group_id)->find(); //5 group
        //检查活动是否已达最大数量
        $groupdCount = $groupdTable
            ->where("group_id", "=", $group_id)
            ->where("status", "in", "拼团中,已完成,已发货")
            ->count();
        //暂时不管这个判断
        /*if ($groupdCount >= $groupInfo['maxorder']) {
            $this->error("对不起，已经超过了当前活动最大限额");
        }*/
        //查询参与量是否符合标准
        if ($number > $groupInfo['everyorder']) {
            $this->error("对不起，您超过了本活动一单最大参与量");
        }
        //查询用户是否存在没有完成的拼团
        $groupcNum = $groupcTable
            ->where("group_id", "=", $group_id)
            ->where("member_id", "=", $member_id)
            ->where("status", "=", "已参与")
            ->count();
        if ($groupcNum > 0) {
            //还有未完成的拼团
            $this->error("对不起，您还有未完成的拼团");
        }
        //查询用户是否已经达到了该次拼团达到的上限
        $groupcNumDone = $groupcTable
            ->where("group_id", "=", $group_id)
            ->where("member_id", "=", $member_id)
            ->where("status", "in", "已完成,已发货")
            ->count();
        /*if ($groupcNumDone >= $groupInfo['maxorder']) {
            $this->error("对不起，您已经达到了参与本次活动的上限次数了");
        }*/
        if ($groupd_id) {
            //检查参与拼团是否超过了本次成团数量上限
            $groupdInfo = $groupdTable
                ->where("groupd_id", "=", $groupd_id)
                ->find();
            if ($number + $groupdInfo['num'] > $groupdInfo['max_num']) {
                $this->error("对不起，本次参与超过了成团数量");
            }
            if ($groupdInfo['status'] != '拼团中') {
                $this->error("对不起，当前拼团状态不正确");
            }
        }
    }

    /**
     * 创建参团码
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function createGroupcCode()
    {
        $code = $this->createCode(3, 10);
        $code = "GC-" . $code;
        $groupcTable = Db::name("groupc");
        $groupcInfo = $groupcTable
            ->where("code", "=", $code)
            ->find();
        if ($groupcInfo) {
            return $this->createGroupcCode();
        } else {
            return $code;
        }
    }

    /**
     * 检查拼团是否已经完成
     */
    private function groupdCheck($groupd_id)
    {
        $groupdTable = Db::name("groupd");
        $groupcTable = Db::name("groupc");

        $time = time();

        $groupdInfo = $groupdTable
            ->where("groupd_id", "=", $groupd_id)
            ->find();

        $sum = $groupcTable
            ->where("groupd_id", "=", $groupd_id)
            ->sum("number");
        if ($sum >= $groupdInfo['max_num']) {
            //已达成团条件
            $groupdData = array(
                'groupd_id' => $groupd_id,
                'ctime' => $time,
                'status' => '已完成'
            );
            $groupdTable->update($groupdData);
        }
    }

    /**
     * 拼团列表
     *
     * @ApiTitle    (拼团列表-查询拼团中状态)
     * @ApiSummary  (groupdList)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Group/groupdList)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="group_id", type="int", required=true, description="拼团ID")
     * @ApiParams (name="status", type="varchar", required=true, description="当前状态:'拼团中',默认：拼团中")
     * @ApiParams (name="pages", type="int", required=true, description="页码，默认为1")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "groupd_id": 2,
    "title": "标题",
    "status": "当前状态",
    "image": "图片",
    "stime": "拼团时间",
    )})
     */
    public function groupdList()
    {
        $group_id = input("group_id", null);
        $status = input("status", "拼团中");
        $pages = input("pages", 1);
        $limit = 10;
        $start = ($pages - 1) * $limit;
        $groupdTable = Db::name("groupd gd");
        if ($group_id != null) {
            $groupdTable->where("gd.group_id", "=", $group_id);
        }
        $groupdList = $groupdTable->where("gd.status", "=", $status)
            ->field("gd.groupd_id,concat(gd.name,'-',gd.describe) as title,gd.status,gd.stime,gs.main_image as image")
            ->join("group gp", "gp.group_id = gd.group_id", "left")
            ->join("goods gs", "gs.id = gp.goods_id", "left")
            ->order("gd.num", "desc")
            ->limit($start, $limit)
            ->select();
        if ($groupdList) {
            $fields = ['stime'];
            $groupdList = $this->dateformate($groupdList, $fields);
            $this->success("查询成功", $groupdList);
        } else {
            $this->error("没有更多了~~~");
        }
    }

    /**
     * 我的拼团列表
     *
     * @ApiTitle    (我的拼团列表-查询我的)
     * @ApiSummary  (mygroupcList)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Group/mygroupcList)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="用户ID")
     * @ApiParams (name="status", type="varchar", required=true, description="当前状态:'已参与','已发货（已核销）','失败(已过期)',,默认：已参与")
     * @ApiParams (name="pages", type="int", required=true, description="页码，默认为1")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "groupd_id": 2,
    "title": "标题",
    "status": "当前状态",
    "image": "图片",
    "stime": "拼团时间",
    )})
     */
    public function mygroupcList()
    {
        $member_id = input("member_id", null);
        if ($member_id != null && $member_id > 0) {
            $status = input("status", "已参与");
            $pages = input("pages", 1);
            $groupcTable = Db::name("groupc c");
            $limit = 10;
            $start = ($pages - 1) * $limit;
            $where['c.member_id'] = ['=', $member_id];
            if ($status == '全部' || $status == "") {
            }elseif ($status == "已参与") {
                $groupcTable->whereIn("c.status",array("已参与","成功"));
            }elseif ($status == "已核销") {
                $groupcTable->whereIn("c.status",array("已发货"));
            } else {
                $where['c.status'] = ['=', $status];
            }
            $groupcList = $groupcTable
                ->where($where)
                ->join("sg_groupd d", "c.groupd_id=d.groupd_id", "left")
                ->join("group gp", "d.group_id = gp.group_id", "left")
                ->join("goods gs", "gs.id = gp.goods_id", "left")
                ->field("d.groupd_id,concat(d.name,'-',d.describe) as title,c.status,d.stime,gs.main_image as image,c.groupc_id")
                ->limit($start, $limit)
                ->order("c.groupc_id", "desc")
                ->group("c.groupc_id")
                ->select();

            $fields = ['stime'];
            $groupcList = $this->dateformate($groupcList, $fields);
            if ($groupcList) {
                $this->success("查询成功", $groupcList);
            } else {
                $this->error("没有更多了~~~");
            }
        } else {
            $this->error("参数有误");
        }
    }

    /**
     * 拼团详情
     *
     * @ApiTitle    (拼团详情)
     * @ApiSummary  (mygroupcDetail)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Group/mygroupcDetail)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="用户ID")
     * @ApiParams (name="groupd_id", type="int", required=true, description="拼团id")
     * @ApiReturn  {
     * "code": 1,
     * "msg": "返回成功",
     * "time": "1649087840",
     * "data": {
     * "image": "/uploads/20220328/632185c0b3af16d4ccbd82684a19b38f.png", 背景图
     * "describe": "描述",
     * "stime": 1648521727,
     * "etime": 1649385727,
     * "time": "240: 00: 00",
     * "status": 2,
     * "groupc": [
     * {
     * "member_id": 1,
     * "image": ""}]}}
     */
    public function mygroupcDetail()
    {
        $member_id = input("member_id", null);
        $groupd_id = input("groupd_id", null);
        if ($groupd_id != null && $groupd_id > 0 && $member_id > 0) {

            //查询拼团信息
            $group = Db::name("groupd gd")
                ->where("gd.groupd_id", $groupd_id)
                ->join("group gp", "gd.group_id=gp.group_id", "left")
                ->field("gp.background_image as image,gp.describe,gd.stime,gd.etime,gd.group_id,gd.groupd_id")->find();
            if ($group) {
                //当前时间都大于拼团结束时间了
//                if(time() > $group['etime']){
//                    $this->error('拼团活动过期');
//                }

                $ss = $this->timediff(time(), $group['etime']);
                $group['time'] = $ss;

                unset($group['stime']);
                unset($group['etime']);
                $group['status'] = 1;
                //查询拼团人员信息
                $image = Db::name("groupc gc")
                    ->where("gc.groupd_id", $groupd_id)
                    ->join("app_member am", "am.id=gc.member_id", "left")
                    ->field("gc.member_id,am.head_image as image")
                    ->group("gc.member_id")
                    ->select();

                //补全数组 不足8个 按照8个算
                $m = 10;
                $num = count($image);
                if ($num < $m) {
                    $n = $m - $num;
                    for ($x = 0; $x < $n; $x++) {
                        $image[] = array();
                    }
                }
                $group['groupc'] = $image;
                foreach ($group['groupc'] as $value) {
                    if (isset($value['member_id']) && $value['member_id']) {
                        if ($value['member_id'] == $member_id) {
                            $group['status'] = 2;
                            break;
                        }
                    }
                }
                $this->success("返回成功", $group);
            } else {
                $this->error("参数有误");
            }
        } else {
            $this->error("参数有误");
        }
    }

    //功能：计算两个时间戳之间相差的日时分秒
    //$begin_time  开始时间戳
    //$end_time 结束时间戳
    private function timediff($begin_time, $end_time)
    {
        //交换过期时间   （其实前就判断时间过期即可）
        if ($begin_time < $end_time) {
            $starttime = $begin_time;
            $endtime = $end_time;
        } else {
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $second = $endtime - $starttime;

        $day = floor($second / (3600 * 24));
        $second = $second % (3600 * 24);//除去整天之后剩余的时间
        $hour = floor($second / 3600);
        $second = $second % 3600;//除去整⼩时之后剩余的时间
        $minute = floor($second / 60);
        $second = $second % 60;//除去整分钟之后剩余的时间

        $arr = $day . '天' . $hour . '⼩时' . $minute . '分' . $second . '秒';
//        halt($arr);


        /* //计算小时数
         $remain = $endtime - $starttime;
         $hours = intval($remain / 3600);

         //计算分钟数
         $remain = $remain % 3600;
         $mins = intval($remain / 60);
         //计算秒数
         $secs = $remain % 60;

         $str = $mins == 0 ? "00" : $mins;
         $str1 = $secs == 0 ? "00" : $secs;
         $res = $hours . ": " . $str . ": " . $str1;*/
//        $res = array("hour" => $hours,"min" => $mins == 0 ? "00" : $mins,"sec" => $secs == 0 ? "00" :$secs);
        return $arr;
    }


    /**
     * 拼团图片
     *
     * @ApiTitle    (-拼团图片)
     * @ApiSummary  (groupImg)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Group/groupImg)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="pages", type="int", required=true, description="页码，默认为1")
     * @ApiReturn  {"code":1,"msg":"ok","time":"1651114618","data":[{"image":"\/assets\/img\/qrcode.png","head_image":"\/uploads\/20220328\/7d4e67cb53ead06a3012b9c73f885330.png","background_image":"\/uploads\/20220328\/632185c0b3af16d4ccbd82684a19b38f.png"},{"image":"\/uploads\/20220402\/24fff3326e901195c4d1b0d0c13b7942.jpg","head_image":"","background_image":""},{"image":"\/uploads\/20220408\/10059c61756a3b3992bb4dd388e3a57e.png","head_image":"\/uploads\/20220408\/10059c61756a3b3992bb4dd388e3a57e.png","background_image":"\/uploads\/20220408\/10059c61756a3b3992bb4dd388e3a57e.png"},{"image":"","head_image":"","background_image":""}]}
     */
    public function groupImg()
    {
        $pages = input("pages", 1);
        $limit = 10;
        $start = ($pages - 1) * $limit;

        $img = \db('group')
            ->field('image,head_image,background_image')
            ->limit($start, $limit)
            ->select();

        if ($img) {
            $this->success('ok', $img);
        } else {
            $this->error('无数据');
        }

    }


    /**
     * 我的拼团二维码详情
     *
     * @ApiTitle    (我的拼团二维码详情)
     * @ApiSummary  (groupCode)
     * @ApiMethod   (get)
     * @ApiRoute    (/api/Group/groupCode)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="groupc_id", type="int", required=true, description="用户ID")
     * @ApiReturn  {
    "code": 1,
    "msg": "ok",
    "time": "1651734641",
    "data": {
    "code": "GC-FBSREEPH13",
    "status": "已参与",
    "name": "拼团活动",
    "city": null,
    "phone": null,
    "purchase_quantity": null,
    "member_id": 53,
    "group_id": 5,
    "groupd_id": 4,
    "describe": "拼团活动4.29\r\n"
    }
    }
     */
    public function groupCode()
    {
        $groupc_id = $this->request->request('groupc_id');

        $info = \db('groupc')
            ->where('groupc_id', $groupc_id)
            ->field('code,status,name,city,phone,purchase_quantity,member_id,group_id,groupd_id')
            ->find();
        if (! isset($info)) {
            $this->error('无该拼团记录');
        }

        $groupd_id = $info['groupd_id'];
        //详情
        $describe = \db('groupd')
            ->where('groupd_id', $groupd_id)
            ->field('name,describe')
            ->find();
        $info['name'] =$describe['name'];
        $info['describe'] =$describe['describe'];
        $this->success('ok',$info);

    }


}
