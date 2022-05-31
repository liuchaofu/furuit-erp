<?php

namespace app\api\controller;

use app\common\controller\Api;

/**
 * sg-基本设置接口
 * User: haoyu
 * Date: 2022/4/1
 * Time: 16:42
 */

class Set extends  Api
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
     * 动态配置获取
     * @ApiTitle   动态配置（积分和协议等）
     * @ApiSummary  (index)
     * @ApiMethod   (post)
     * @ApiRoute    (/api/Set/index)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiParams (name="tab", type="string", required=true, description="类型")
     * @ApiReturn   ({
    'code':'1',
    'msg':'返回成功'
    )})
     */
    public function index()
    {
        $tab = $this->request->request("tab");
        $res = db("set_up")->where("tab",$tab)->field("title,context")->find();
        if($res)
        {
            $this->success("返回成功",$res);
        }else{
            $this->error("参数有误");
        }
    }
}