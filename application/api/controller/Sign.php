<?php


namespace app\api\controller;


use app\admin\model\Detail;
use think\Db;
use think\Env;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;

/**
 * sg-小程序签到
 * User: Administrator
 * Date: 2022/3/29
 * Time: 13:52
 */
class Sign extends Common
{


    /**
     * 签到
     *
     * @ApiTitle    (签到)
     * @ApiSummary  (signAction)
     * @ApiMethod   (get)
     * @ApiRoute    (/api/Sign/signAction)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="member_id")
     * @ApiParams (name="lat", type="string", required=true, description="经度")
     * @ApiParams (name="lng", type="string", required=true, description="纬度")
     * @ApiParams (name="address", type="string", required=true, description="地址")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    )})
     */
    public function signAction()
    {
        ini_set('date.timezone', 'Asia/Shanghai');
        $mid = isset($_GET['member_id']) ? $_GET['member_id'] : '';

        $lat = isset($_GET['lat']) ? $_GET['lat'] : '';//经度
        $lng = isset($_GET['lng']) ? $_GET['lng'] : '';//纬度
        $address = isset($_GET['address']) ? $_GET['address'] : '';//地址

        //.env文件配置 默认的地址的 经度和纬度 、和距离多少米
        $envLat = Env::get('location.lat');
        $envLng = Env::get('location.lng');
        $envM = Env::get('location.m');
        if(!isset($envLat)&&!$envLat&&!isset($envLng)&&!$envLng&&!isset($envM)&&!$envM){
            $this->error('未配置默认经纬度值，请联系管理员');
        }
        //定位打卡，计算出默认位置和当前位置的距离
        $decimal = $this->GetDistance($envLat,$envLng,$lat,$lng,1);
        $gps = 0;//GPS打卡次数
        if($decimal <= $envM){
            $gps = 1;
        }

        if (!$mid) {
            $this->error('参数缺失！');
        }
        $time = time();

        //判断用户表有没有此人
        $check = \db('app_member')
            ->where('id', $mid)
            ->find();
        if (empty($check)) {
            $this->error('你好，你不是我们的用户');
        }

//        $signInfo = $mSign->getOne(['uid' => $mid]);
        $signInfo = \db('app_sign_in')
            ->where('member_id', $mid)
            ->find();
//        halt($signInfo);
        if ($signInfo) {

            $sign_id = $signInfo['id'];
            $last_time = date('Y-m-d', $signInfo['sign_time']);

            $sign_count = $signInfo['sign_count'];
            $total_count = $signInfo['total_count'];
            $gps_count = $signInfo['gps_count'];

            $yesterday = date('Y-m-d', strtotime('-1 day'));    //昨天的日期格式 2018-07-02

            if ($last_time == date('Y-m-d')) {    //如果等于今天，$sign_count不变
                $this->error('您今天已经签到过了！');
            } else if ($last_time == $yesterday) {    //如果用户昨天打了卡，连续签到次数加1；否则重置为1
                $sign_count += 1;
                $total_count += 1;
                $gps_count += $gps;
            } else {
                $sign_count = 1;
                $total_count += 1;
                $gps_count += $gps;
            }

            //$result = $mSign->edit($editData, $where);        //编辑详情表
            Db::startTrans();
            try {

                $where = ['member_id' => $mid];
                $editData = ['sign_count' => $sign_count, 'sign_time' => $time, 'createtime' => $time, 'total_count' => $total_count,'gps_count'=>$gps_count];
                $res = \db('app_sign_in')
                    ->where($where)
                    ->update($editData);

                //展示签到的
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }


            if ($res) {
                $result = $signInfo['id'];
            } else {
                $this->error('更新出错');
            }


        } else {    //主表数据为空，是新用户登录

            Db::startTrans();
            try {

                $data = [
                    'member_id' => $mid,
                    'sign_count' => 1,
                    'total_count' => 1,
                    'sign_time' => $time,
                    'createtime' => $time
                ];
                $sign_id = \db('app_sign_in')->insertGetId($data);
                $result = $sign_id;

                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }


        }

        if ($result) {    //获取主表的主键ID，统一操作详情变
            Db::startTrans();
            try {

                $detailData = [
                    'sign_id' => $result,
                    'sign_time' => $time,
                    'lat' => $lat,
                    'lng' => $lng,
                    'address' => $address,
                ];
                //$detailRet = $mSignDetail->add($detailData);    //添加数据到详情表
                $detailRet = \db('app_sign_detail')
                    ->insert($detailData);
                Db::commit();
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            if ($detailRet) {
                //积分功能判断
                Db::startTrans();
                try {

                    //去用户表查询当前积分
                    $integral = db('app_member')
                        ->where('id', $mid)
                        ->field('id,realname,integral,integral_total')
                        ->find();

                    //当前积分
                    $log['start_integral'] = $integral['integral'];

                    //判断逻辑 当前用户的连续签到数量
                    $info = \db('app_sign_in')
                        ->where('member_id', $mid)
                        ->field('sign_count')
                        ->find();
                    $count = $info['sign_count'];

                    //每次分享增加的积分 读取配置得积分
                    $tab = 'qd';
                    $set = \db('set_up')
                        ->where('tab', $tab)
                        ->field('title')
                        ->find();
                    $add = $set['title'];
                    //读取配置积分
                    $first = $this->cut_str($add, '-', 0);//5
                    $two = $this->cut_str($add, '-', 1);//8
                    $three = $this->cut_str($add, '-', 2);//15
                    $four = $this->cut_str($add, '-', 3);//30
                    $five = $this->cut_str($add, '-', -2);//50
                    $six = $this->cut_str($add, '-', -1);//100


                    if ($count >= 1 && $count <= 5) {
                        //5
                        $add_integral = $first;
                        if ($count == 5) {
                            //15
                            $add_integral = $add_integral + $three;
                        }
                    }
                    if ($count >= 6 && $count <= 30) {
                        //8
                        $add_integral = $two;
                        if ($count == 10) {
                            //30
                            $add_integral = $add_integral + $four;
                        }
                        if ($count == 20) {
                            //50
                            $add_integral = $add_integral + $five;
                        }
                        if ($count == 30) {
                            //100
                            $add_integral = $add_integral + $six;
                        }
                    }


                    $log['add_integral'] = $add_integral;

//                $log['add_integral'] = 15;
                    //现在的积分
                    $log['end_integral'] = $log['start_integral'] + $log['add_integral'];
                    //加积分记录到记录表
                    $log['typedata'] = "day";
                    $log['member_id'] = $mid;
                    $log['createtime'] = $time;

                    //添加到log表
                    $log = db('app_integral_log')->insert($log);

                    Db::commit();
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }


                if ($log) {
                    Db::startTrans();
                    try {

                        //判断逻辑 当前用户的连续签到数量
                        $info = \db('app_sign_in')
                            ->where('member_id', $mid)
                            ->field('sign_count')
                            ->find();
                        $count = $info['sign_count'];

                        //每次分享增加的积分 读取配置得积分
                        $tab = 'qd';
                        $set = \db('set_up')
                            ->where('tab', $tab)
                            ->field('title')
                            ->find();
                        $add = $set['title'];
                        //读取配置积分
                        $first = $this->cut_str($add, '-', 0);//5
                        $two = $this->cut_str($add, '-', 1);//8
                        $three = $this->cut_str($add, '-', 2);//15
                        $four = $this->cut_str($add, '-', 3);//30
                        $five = $this->cut_str($add, '-', -2);//50
                        $six = $this->cut_str($add, '-', -1);//100


                        if ($count >= 1 && $count <= 5) {
                            //5
                            $add_integral = $first;
                            if ($count == 5) {
                                //15
                                $add_integral = $add_integral + $three;
                            }
                        }
                        if ($count >= 6 && $count <= 30) {
                            //8
                            $add_integral = $two;
                            if ($count == 10) {
                                //30
                                $add_integral = $add_integral + $four;
                            }
                            if ($count == 20) {
                                //50
                                $add_integral = $add_integral + $five;
                            }
                            if ($count == 30) {
                                //100
                                $add_integral = $add_integral + $six;
                            }
                        }

//                        $set['title'] = $add_integral;

                        //去用户主表增加积分 2个积分都要增加
                        $now_integral = $integral['integral'] + $add_integral;
                        $total_integral = $integral['integral_total'] + $add_integral;

                        $member_data['integral'] = $now_integral;
                        $member_data['integral_total'] = $total_integral;

                        //更改
                        $res = db('app_member')
                            ->where('id', $mid)
                            ->update($member_data);
                        Db::commit();
                    } catch (Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    //签到30天后签到数量清空为0
                    $info = \db('app_sign_in')
                        ->where('member_id', $mid)
                        ->field('sign_count')
                        ->find();
                    $count = $info['sign_count'];

                    if ($count == 30) {
                        Db::startTrans();
                        try {
                            $up['sign_count'] = 0;
                            $result = \db('app_sign_in')
                                ->where('member_id', $mid)
                                ->update($up);
                            Db::commit();
                        } catch (Exception $e) {
                            Db::rollback();
                            $this->error($e->getMessage());
                        }
                    }


                    if ($res) {
                        if($gps > 0)
                        {
                            $this->success('指定位置签到成功');
                        }else{
                            $this->success('签到成功');
                        }
                    } else {
                        $this->error('签到失败 mm');
                    }
                } else {
                    $this->error('更新失败 jl');
                }

            } else {
                $this->error('签到失败！');
            }
        } else {
            $this->error('操作主表异常！');
        }

    }

    /**
     * 签到列表
     *
     * @ApiTitle    (签到列表)
     * @ApiSummary  (signDay)
     * @ApiMethod   (get)
     * @ApiRoute    (/api/Sign/signDay)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="member_id", type="int", required=true, description="member_id")
     * @ApiReturn   {
     * "code": 1,
     * "msg": "ok",
     * "time": "1648779773",
     * "data": {
     * "info": {
     * "id": 7,
     * "member_id": 2,
     * "sign_count": 2,连续签到
     * "sign_time": 1648778926,
     * "total_count":1 签到总数
     * "gps_count":1 位置签到
     * "signTime": "2022-04-01  10:08"
     * },
     * "detail": [
     * {
     * "id": 7,
     * "sign_id": 7,
     * "sign_time": 1648706635,
     * "createtime": null,
     * "updatetime": null,
     * "signTime": "2022-03-31  14:03"
     * }
     * ]
     * }
     * }
     */


    public function signDay()
    {

        $member_id = $this->request->request('member_id');

        //查询用户的积分 现在的积分
        $integral = \db('app_member')
            ->where('id', $member_id)
            ->field('integral')
            ->find();


        if ($integral) {
            $code = $integral['integral'];
        } else {
            $this->error('没有该数据');
        }

        //积分
        $now_code = isset($code) ? $code : 0;


        //查询签到表的连续签到次数 最后一次签到的时间
        $sign = \db('app_sign_in')
            ->where('member_id', $member_id)
            ->field('id,member_id,sign_count,sign_time,total_count,gps_count')
            ->find();


        if (empty($sign)) {

            //创建用户的信息
            $data = [
                'member_id' => $member_id,
                'sign_count' => 1,
                'total_count' => 1,
//                'sign_time' => time(),
                'createtime' => time(),
                'updatetime' => time(),
            ];
            $sign_id = \db('app_sign_in')->insertGetId($data);

            //用户信息
            $sign_info = \db('app_sign_in')
                ->where('member_id', $member_id)
                ->field('id,member_id,sign_count,sign_time,total_count,gps_count')
                ->find();

            //新用户不可能有详情表
            $signDate = [
                'info' => $sign_info,
                'integral' => $now_code,
                'detail' => []
            ];

            $this->success('ok', $signDate);


//            $new_sign['signTime'] = date("Y-m-d  H:i", $new_sign['sign_time']);
            //新用户默认给他签到一天
            /*$detai = [
                'sign_id' => $sign_id,
                'sign_time' => time()
            ];
            //加入签到详情表
            $detai_id = \db('app_sign_detail')->insertGetId($detai);

            //增加积分 +5

            //去用户表查询当前积分
            $integral = db('app_member')
                ->where('id', $member_id)
                ->field('id,realname,integral,integral_total')
                ->find();

            Db::startTrans();
            try {
                //当前积分
                $log['start_integral'] = $integral['integral'];


                $log['add_integral'] = 5;

                //现在的积分
                $log['end_integral'] = $log['start_integral'] + $log['add_integral'];
                //加积分记录到记录表
                $log['typedata'] = "day";
                $log['member_id'] = $member_id;
                $log['createtime'] = time();
                $log['remarks'] = '新人默认首次签到';

                //添加到log表
                $log = db('app_integral_log')->insertGetId($log);
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
            }*/

            /*if ($log) {

//                Db::startTrans();
//                try {
                    //首次签到积分  5
                    $add_integral = 5;
                    //去用户主表增加积分 2个积分都要增加
                    $now_integral = $integral['integral'] + $add_integral;
                    $total_integral = $integral['integral_total'] + $add_integral;

                    $member_data['integral'] = $now_integral;
                    $member_data['integral_total'] = $total_integral;

                    //更改
                    $res = db('app_member')
                        ->where('id', $member_id)
                        ->update($member_data);

                    if ($res) {

                        $sign_detail = \db('app_sign_detail')
                            ->where('id', $detai_id)
                            ->select();
                        if (!empty($sign_detail)) {
                            foreach ($sign_detail as $k => $v) {
                                $sign_detail[$k]['signTime'] = date("Y-m-d  H:i", $sign_detail[$k]['sign_time']);
                            }
                        }

                        $sign_info = \db('app_sign_in')
                            ->where('member_id', $member_id)
                            ->field('id,member_id,sign_count,sign_time,total_count')
                            ->find();

                        //新用户不可能有详情表
                        $signDate = [
                            'info' => $sign_info,
                            'integral' => $now_code,
                            'detail' => $sign_detail
                        ];


                        $this->success('ok', $signDate);
                    } else {
                        $this->error('用户积分修改失败');
                    }

//                    Db::commit();
//                } catch (ValidateException $e) {
//                    Db::rollback();
//                    $this->error($e->getMessage());
//                } catch (PDOException $e) {
//                    Db::rollback();
//                    $this->error($e->getMessage());
//                } catch (Exception $e) {
//                    Db::rollback();
//                    $this->error($e->getMessage());
//                }

            } else {
                $this->error('插入记录失败');
            }*/

        } else {
            $sign['signTime'] = date("Y-m-d  H:i", $sign['sign_time']);
            //查询详细签到的数据
            $sign_detail = \db('app_sign_detail')
                ->where('sign_id', $sign['id'])
                ->select();
            if (!empty($sign_detail)) {
                foreach ($sign_detail as $k => $v) {
                    $sign_detail[$k]['signTime'] = date("Y-m-d  H:i", $sign_detail[$k]['sign_time']);
                }
            }

            $signDate = [
                'info' => $sign,
                'integral' => $now_code,
                'detail' => $sign_detail
            ];

            $this->success('ok', $signDate);
        }
    }

    /**
     * 签到提示
     * @ApiTitle    (签到提示)
     * @ApiSummary  (isGsp)
     * @ApiMethod   (get)
     * @ApiRoute    (/api/Sign/isGsp)
     * @ApiParams (name="lat", type="string", required=true, description="经度")
     * @ApiParams (name="lng", type="string", required=true, description="纬度")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    'data':array(
    )})
     */
    public function isGsp()
    {
        $lat = isset($_GET['lat']) ? $_GET['lat'] : '';//经度
        $lng = isset($_GET['lng']) ? $_GET['lng'] : '';//纬度
        if($lat&&$lng){
            //.env文件配置 默认的地址的 经度和纬度 、和距离多少米
            $envLat = Env::get('location.lat');
            $envLng = Env::get('location.lng');
            $envM = Env::get('location.m');
            if(!isset($envLat)&&!$envLat&&!isset($envLng)&&!$envLng&&!isset($envM)&&!$envM){
                $this->error('未配置默认经纬度值，请联系管理员');
            }
            //定位打卡，计算出默认位置和当前位置的距离
            $decimal = $this->GetDistance($envLat,$envLng,$lat,$lng,1);
            if($decimal <= $envM){
                $this->success("已进入指定签到位置");
            }else{
                $this->success("未进入指定签到位置",null,2);
            }
        }else{
            $this->error('经纬度为空');
        }
    }
}