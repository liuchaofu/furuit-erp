<?php


namespace app\api\controller;

use app\common\controller\Api;

/**
 * sg-活动管理
 * User: haoyu
 * Date: 2022/4/2
 * Time: 15:25
 */
class Activity extends Api
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
     * 活动列表
     * @ApiTitle    (活动列表)
     * @ApiSummary  (list)
     * @ApiMethod   (get)
     * @ApiRoute    (/api/Activity/list)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功和失败'
    )})
     */
    public function list()
    {
        $res = db("activity")
            ->where("status", "活动中")
            ->field("id as activity_id,title,activity_image")
            ->order('createtime desc')
            ->select();

        $this->success("返回成功", $res);
    }

    /**
     * 活动详情
     * @ApiTitle    (活动详情)
     * @ApiSummary  (find)
     * @ApiMethod   (get)
     * @ApiRoute    (/api/Activity/find)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功和失败'
    )})
     */
    public function find()
    {
        $id = $this->request->request("activity_id");
        if (isset($id) && $id > 0) {
            $res = db("activity")->where("id", $id)->find();

            if ($res) {
                db("activity")->where("id", $id)->setInc("num", 1);

                $image = db("groupc gc")
                    ->where("gc.group_id", $res['group_id'])
                    ->join("app_member am", "gc.member_id = am.id")
                    ->field("am.head_image")
                    ->select();
                $res['image'] = $image;
                $res['person_num'] = count($image);
                $res['stime'] = $this->dealTime($res['stime']);
                $res['etime'] = $this->dealTime($res['etime']);
            }
            $this->success("返回成功", $res);
        } else {
            $this->error("参数有误");
        }
    }
}