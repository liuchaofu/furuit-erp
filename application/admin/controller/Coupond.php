<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Session;

/**
 * 优惠券领取使用管理
 *
 * @icon fa fa-circle-o
 */
class Coupond extends Backend
{

    /**
     * Coupond模型对象
     * @var \app\admin\model\Coupond
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Coupond;
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->with(['coupon', 'member', 'user'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);


            foreach ($list as $row) {
                $row->getRelation('coupon')->visible(['name']);
                $row->getRelation('member')->visible(['shop_name', 'name', 'phone', 'id_card', 'house_num']);
                $row->getRelation('user')->visible(['name', 'phone']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }



    /*
     * 核销
     */
    public function write($ids)
    {


        foreach ($ids as $k => $v) {

            $res = $this->model->where('coupond_id', $ids[$k])->value('status');
            if ($res !== "已使用") {
                $this->error('结算状态不对请检查');
            }

            $admin = Session::get('admin');
            $time = time();
            $log['username'] = $admin['username'];
            $log['nickname'] = $admin['nickname'];
            $log['createtime'] = $time;
            $log['updatetime'] = $time;
            $log['catedata'] = 'write';
            $info = \db('coupond')
                ->where('coupond_id', $ids[$k])
                ->field('coupond_id,code')
                ->find();
            $log['coupond_id'] = $info['coupond_id'];
            $log['coupond_code'] = $info['code'];
            $log['tablename'] = "优惠券领取详情";

            //添加到记录表
            $oprate = \db('oprate_log')->insertGetId($log);

            if ($oprate) {

                $data = [
                    'status' => "已结算",
                    'settlementtime' => time(),
                ];
                $result = $this->model->where('coupond_id', $ids[$k])->update($data);
                /*if ($result) {
                    $this->success();
                }else{
                    $this->error('更改状态失败');
                }*/

            } else {
                $this->error('添加数据失败');
            }

        }
        $this->success();

    }


}
