<?php


namespace app\api\controller;

/**
 * sg-小程序首页展示图
 * User: Administrator
 * Date: 2022/3/29
 * Time: 13:52
 */
class Banner extends Common
{
    /**
     * 首页-首页展示图片
     *
     * @ApiTitle    (首页-首页展示图片)
     * @ApiSummary  (list)
     * @ApiMethod   (POST)
     * @ApiRoute    (/api/Banner/showBanner)
     * @ApiHeaders  (name=token, type=string, required=true, description="请求的Token")
     * @ApiReturn   {
     * "code": 1,
     * "msg": "查询成功",
     * "time": "1648559185",
     * "data": [
     * {
     * "title": "test25242",
     * "image": "",
     * "sort": 55
     * },
     * {
     * "title": "基本信息",
     * "image": "",
     * "sort": 50
     * },
     * {
     * "title": "员工01",
     * "image": "/uploads/20220326/209bc56fc4d293dd95b31bf04676dd6c.jpg",
     * "sort": 20
     * }
     * ]
     * }
     */

    //展示banner图 展示sort倒叙排序的几条然后返回
    public function showBanner()
    {
        //读取设置中首页轮播展示几张图片
        $tab = 'banner';
        $set = \db('set_up')
            ->where('tab', $tab)
            ->field('context')
            ->find();

        $number = isset($set['context']) ? $set['context'] : 1;

        $data = db('banner')
            ->field('title,image,sort')
            ->order('sort desc')
            ->limit($number)
            ->select();

        if ($data) {
            $this->success('查询成功', $data);
        }
        $this->error('没有数据');


    }
}