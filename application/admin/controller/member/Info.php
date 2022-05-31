<?php

namespace app\admin\controller\member;

use app\common\controller\Backend;

/**
 * 渠道商和商户信息
 *
 * @icon fa fa-circle-o
 */
class Info extends Backend
{

    /**
     * Info模型对象
     * @var \app\admin\model\Info
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Info;
        $this->view->assign("typedataList", $this->model->getTypedataList());
        $this->view->assign("catedataList", $this->model->getCatedataList());
        $this->view->assign("stateList", $this->model->getStateList());
        $this->view->assign("checkdataList", $this->model->getCheckdataList());
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
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            //新增判断显示渠道商条件
//            $where['typedata'] = 'person';
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
//                ->with(['member'])
//                ->field('member.realname as realname,member.id as members_id')
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

//            $list =json_decode($list);
            foreach ($list as $row) {
                $row->visible(['id', 'member_id', 'typedata', 'shop_name', 'shop_image', 'name', 'phone', 'id_card', 'city', 'house_num', 'state','checkdata']);

            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 更改状态
     */
    public function adopt()
    {
        $id = input('ids');
        if ($id != null) {
            //根据这个id去找认证表有无记录有则改 --- 暂时不判断

//            $data =\db('app_member_info')
//                ->where('member_id',$id)
//                ->find();
            //有则改状态
            //if ($data) {
            $result =\db('app_member_info')
                ->where('id',$id)
                ->update(['state' => 1]);
            if ($result) {
                $this->success('成功', '', $result);
            }

            // }
            $this->error('更改失败');

        }
    }

    /**
     * 禁用状态
     */
    public function close()
    {
        $id = input('ids');
        if ($id != null) {
            $result =\db('app_member_info')
                ->where('id',$id)
                ->update(['state' => 2]);
            $this->success('禁用成功', '', $result);
        }
    }



}
