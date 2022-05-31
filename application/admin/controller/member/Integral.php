<?php

namespace app\admin\controller\member;

use app\common\controller\Backend;

/**
 * 用户积分
 *
 * @icon fa fa-circle-o
 */
class Integral extends Backend
{
//    protected $relationSearch = true;

    /**
     * Integral模型对象
     * @var \app\admin\model\Integral
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Integral;
        $this->view->assign("typedataList", $this->model->getTypedataList());
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


//            $total = $this->model
//                ->with(['member'])
//                ->where($where)
//                ->order($sort, $order)
//                ->count();
            $list = $this->model
                ->with(['member'])
//                ->field('member.realname as realname,member.id as members_id,member.nickname')
//                ->with('member')->removeOption('field')->field('realname')
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {
//                $row->visible(['id', 'typedata', 'member_id', 'start_integral', 'add_integral', 'end_integral', 'remarks','realname','nickname']);
                $row->getRelation('member')->visible(['realname', 'nickname']);
            }

//            $result = array("total" => $total, "rows" => $list->items());
            $result = array("total" =>$list->total(), "rows" => $list->items());
            return json($result);
        }
        return $this->view->fetch();
    }

}
