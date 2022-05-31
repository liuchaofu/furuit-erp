<?php

namespace app\api\controller;

use app\common\controller\Api;
use Exception;
use think\Db;

/**
 * sg-水果抽奖
 * User: haoyu
 * Date: 2022/4/26
 * Time: 22:37
 */

class Luck extends Api
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
     *
     * 抽奖详情
     * @ApiTitle    (抽奖详情)
     * @ApiSummary  (info)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Luck/info)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="code", type="string", required=false, description="抽奖编号")
     * @ApiParams (name="member_id", type="int", required=true, description="member_id")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
        'data':array(
        "luck_id": 1,
        "code": "抽奖编码",
        "status": "状态",
        "address": "地址",
        "num": "查看人数",
        "btime": "开始时间",
        "etime": "结束时间",
        "gps": "位置打卡次数",
        "default": "默认抽奖次数",
        "image": "头像",
        "person_num": 参与人数,
        )})
     */
    public function info()
    {
//        $code = $this->request->request("code","10001");
        $code = "10001";
        $member_id = $this->request->request("member_id");

        if(isset($member_id)&&$member_id>0)
        {
            //查询抽奖详情
            $luck = db("luck")
                ->where("code",$code)
                ->field("luck_id,code,remarks,status,address,num,FROM_UNIXTIME(btime,'%Y-%m-%d %H:%i') as btime,FROM_UNIXTIME(etime,'%Y-%m-%d %H:%i') as etime")->find();

            if($luck){
                //获取位置打卡次数
                $gps = db("app_sign_in")->where("member_id",$member_id)->value("gps_count");
                $luck['gps'] = $gps > 0 ? $gps : 0;
                //默认抽奖次数
                $a ='cj';
                $times = \db('set_up')
                    ->where('tab',$a)
                    ->field('context')
                    ->find();
                $time = $times['context'];
                $luck['default'] = $time;

                //查询已参与抽奖的用户头像
                $image = db("luckrecord ld")->where("ld.luck_id",$luck['luck_id'])
                    ->field("am.head_image")
                    ->join("app_member am","ld.member_id = am.id","left")
                    ->select();
                $luck['image'] = $image;
                $luck['person_num'] = count($image);

                //新增查询次数
                db("luck")->where("luck_id",$luck['luck_id'])->setInc("num",1);
                $this->success("获取成功",$luck);
            }else{
                $this->error('未查询到抽奖活动');
            }
        }else{
            $this->error('参数用户ID错误');
        }
    }


    /**
     *
     * 获取抽奖列表
     * @ApiTitle    (获取抽奖列表)
     * @ApiSummary  (item)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Luck/item)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="luck_id", type="int", required=false, description="抽奖id")
     * @ApiParams (name="status", type="string", required=false, description="状态")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    "status": "状态",
    'luck':array(
        'luck_id':'抽奖id'
        'remarks':'规则'
        'btime':'开始时间'
        'btime':'结束时间'
     ),
    'item':array(
        'luckitem_id':'抽奖设置id'
        'luck_id':'抽奖id'
        'name':'中奖类型'
        'value':'中奖价值'
        ))})
     */
    public function item()
    {
        $luck_id = $this->request->request("luck_id");
        $status = $this->request->request("status","进行中");
        if(isset($luck_id)&&$luck_id>0)
        {
            $luck = db("luck")
                ->where("luck_id",$luck_id)
                ->field("luck_id,remarks,btime,etime")
                ->where("status",$status)->find();
            $data = array();
            if($luck)
            {
                $time = time();
                if($time >= $luck['btime']){
                    if($time < $luck['btime']){
                        $this->error('已结束');
                    }else{
                        $data['status'] = "进行中";
                        $data['luck'] = $luck;
                        $data['item'] = db("luckitem")
                            ->where("luck_id",$luck['luck_id'])
                            ->field("luckitem_id,luck_id,title,name,value")
                            ->select();
                        $this->success('查询成功',$data);
                    }
                }else{
                    $this->error('未开始');
                }
            }else{
                $this->error('未查询到抽奖活动');
            }
        }else{
            $this->error('参数抽奖ID错误');
        }
    }



    /**
     * 抽奖
     *
     * @ApiTitle    (抽奖)
     * @ApiSummary  (luck)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Luck/luck)
     * @ApiParams (name="member_id", type="int", required=false, description="用户ID")
     * @ApiParams (name="luck_id", type="int", required=false, description="活动ID")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    })
     */
    public function luck(){
        $member_id = input("member_id");
        $luck_id = input("luck_id");

        //查询位置打卡次数
        $signTable = Db::name("app_sign_in");
        $gspCount = $signTable->where("member_id",$member_id)->value("gps_count");
        //读取配置表
        $a ='cj';
        $times = \db('set_up')
            ->where('tab',$a)
            ->field('context')
            ->find();
        $time = $times['context'];

        if($gspCount < $time){
            $this->error("对不起，您不满足抽奖条件，位置打卡积累".$gspCount."次");
        }

        $luckTable = Db::name("luck");
        $luckItemTable = Db::name("luckitem");
        $luckRecordTable = Db::name("luckrecord");
        $time = time();
        $luckInfo = $luckTable->where("luck_id","=",$luck_id)->where("status","=","进行中")->field("code")->find();
        if($luckInfo){
            //查询自己当前剩余抽奖次数
            $luckItemList = $luckItemTable->where("luck_id","=",$luck_id)->where("left",">",0)->select();
            if(count($luckItemList) == 0){
                $this->error("奖池为空");
            }

            //满足抽奖条件，开始抽奖操作
            $proarr = array();
            foreach ($luckItemList as $key=>$val){
                $proarr[] = $val['left'];
            }
            //中奖信息
            $result = $luckItemList[$this->get_rand($proarr)];
            $luckRecord_id = 0;
            Db::startTrans();
            try {
                //减少库存
                $flag = $luckItemTable->where("luckitem_id","=",$result['luckitem_id'])->where("left",">",0)->setDec("left",1);
                if($flag){
                    //兑换奖项 优惠券和积分自动兑换
                    $item = $luckItemTable->where("luckitem_id",$result['luckitem_id'])->field("name,coupon_id,value")->find();
                    if($item['name'] == "优惠券"){
                        $status = "已兑奖";
                        //发送优惠券
                        $this->coupon($member_id,$item['coupon_id'],$item['value']);
                    }elseif ($item['name'] == "积分"){
                        //积分累加
                        $this->integral($member_id,$item['value']);
                        $status = "已兑奖";
                    }else{
                        $status = "未兑奖";
                    }
                    //插入信息
                    $luckRecordData = array(
                        'luck_id' => $luck_id,
                        'luckitem_id' => $result['luckitem_id'],
                        'member_id' => $member_id,
                        'code' => $luckInfo['code'],
                        'status' => $status,
                        'checktime' =>$time,
                        'createtime' => $time
                    );
                    $luckRecord_id = $luckRecordTable->insertGetId($luckRecordData);
                    Db::commit();
                }else{
                    $this->error("提交失败");
                }
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if($luckRecord_id){
                $luckitemInfo = $luckItemTable->where("luckitem_id","=",$result['luckitem_id'])->find();
                $signTable->where("member_id",$member_id)->setDec("gps_count",15);
                $this->success("抽奖成功",$luckitemInfo);
            }else{
                $this->error("抽奖失败");
            }
        }

    }


    /**
     * 添加积分
     * @param $member_id
     * @param $value
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    protected function integral($member_id,$value)
    {
        $person = \db('app_member')->where('id', $member_id)->field("integral,integral_total")->find();
        $log['member_id'] = $member_id;
        $log['start_integral'] = $person['integral'];
        $log['add_integral'] = $value;
        $log['end_integral'] = $person['integral'] + $value;
        $log['remarks'] = '抽奖'.$value.'积分';
        $log['createtime'] = time();
        $log['typedata'] = 'luck';
        \db('app_integral_log')->insertGetId($log);
        //修改积分
        $change = array(
            'integral_total' => $person['integral_total'] + $value, //总积分累加
            'integral' => $person['integral'] + $value, //剩余积分累加
        );
       \db('app_member')->where('id', $member_id)->update($change);
    }

    /**
     * 优惠券发送
     * @param $member_id
     * @param $coupon_id
     * @param $value
     */
    protected function coupon($member_id,$coupon_id,$value)
    {
        $coupon = \db("coupon")->where("coupon_id",$coupon_id)->field("left,price")->find();
       //计算出张图
        $num = $value/$coupon['price'];


        $time = time();
        //根据传来的max 数量
        for ($x = 1; $x <= $num; $x++) {
            //插入数据
            $coupondData = array(
                'member_id' => $member_id,
                'coupon_id' => $coupon_id,
                'gettime' => $time,
                'number' => 1,
                'code' => $this->couponCode(),
                'status' => '已领取',
            );
            \db("coupond")->insertGetId($coupondData);
        }
        \db("coupon")->where("coupon_id",$coupon_id)->setDec('left',$num);
    }


    /**
     * 创建优惠券码
     */
    protected function couponCode(){

        $code = $this->createCode(2, 10);
        $code = "SG-".$code;
        $coupondTable = Db::name("coupond");
        $coupondInfo = $coupondTable
            ->where("code","=",$code)
            ->find();
        if($coupondInfo){
            return $this->couponCode();
        }else{
            return $code;
        }
    }

    private function get_rand($proArr) {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);
        //概率数组循环
        foreach ($proArr as $key => $proCur) {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur) {
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
}