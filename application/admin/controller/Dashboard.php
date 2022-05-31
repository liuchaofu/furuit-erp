<?php

namespace app\admin\controller;

use app\admin\model\Admin;
use app\admin\model\Member;
use app\admin\model\User;
use app\common\controller\Backend;
use app\common\model\Attachment;
use fast\Date;
use think\Db;

/**
 * 控制台
 *
 * @icon   fa fa-dashboard
 * @remark 用于展示当前系统中的统计数据、统计报表及重要实时数据
 */
class Dashboard extends Backend
{

    /**
     * 查看
     */
    public function index()
    {
        try {
            \think\Db::execute("SET @@sql_mode='';");
        } catch (\Exception $e) {

        }
        $column = [];
        $starttime = Date::unixtime('day', -6);
        $endtime = Date::unixtime('day', 0, 'end');
        $joinlist = Db("app_member")->where('createtime', 'between time', [$starttime, $endtime])
            ->field('createtime, state, COUNT(*) AS nums, DATE_FORMAT(FROM_UNIXTIME(createtime), "%Y-%m-%d") AS join_date')
            ->group('join_date')
            ->select();
        for ($time = $starttime; $time <= $endtime;) {
            $column[] = date("Y-m-d", $time);
            $time += 86400;
        }
        $userlist = array_fill_keys($column, 0);
        foreach ($joinlist as $k => $v) {
            $userlist[$v['join_date']] = $v['nums'];
        }

        $dbTableList = Db::query("SHOW TABLE STATUS");
        $addonList = get_addon_list();
        $totalworkingaddon = 0;
        $totaladdon = count($addonList);
        foreach ($addonList as $index => $item) {
            if ($item['state']) {
                $totalworkingaddon += 1;
            }
        }
        $this->view->assign([
            'totaluser'         => Member::count(), //全部用户
            'totaladdon'        => $totaladdon,
            'totaladmin'        => Admin::count(), //管理员
            'totalcategory'     => \app\common\model\Category::count(),
            'todayusersignup'   => Member::whereTime('createtime', 'today')->count(),
           // 'todayuserlogin'    => Member::whereTime('createtime', 'today')->count(),  //无登录次数呀
            'sevendau'          => Member::whereTime('createtime', '-7 days')->count(),
//            'sevendau'          => Member::whereTime('createtime|logintime|prevtime', '-7 days')->count(),
            'thirtydau'         => Member::whereTime('createtime', '-30 days')->count(),
            'threednu'          => Member::whereTime('createtime', '-3 days')->count(),
            'sevendnu'          => Member::whereTime('createtime', '-7 days')->count(),
            'dbtablenums'       => count($dbTableList),
            'dbsize'            => array_sum(array_map(function ($item) {
                return $item['Data_length'] + $item['Index_length'];
            }, $dbTableList)),
            'totalworkingaddon' => $totalworkingaddon,
            'attachmentnums'    => Attachment::count(),
            'attachmentsize'    => Attachment::sum('filesize'),
            'picturenums'       => Attachment::where('mimetype', 'like', 'image/%')->count(),
            'picturesize'       => Attachment::where('mimetype', 'like', 'image/%')->sum('filesize'),
        ]);

        $this->assignconfig('column', array_keys($userlist));
        $this->assignconfig('userdata', array_values($userlist));

        return $this->view->fetch();
    }

}
