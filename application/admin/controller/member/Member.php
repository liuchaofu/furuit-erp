<?php

namespace app\admin\controller\member;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;
/**
 * 小程序用户管理
 *
 * @icon fa fa-circle-o
 */
class Member extends Backend
{

    /**
     * Member模型对象
     * @var \app\admin\model\Member
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Member;
        $this->view->assign("typedataList", $this->model->getTypedataList());
        $this->view->assign("powerList", $this->model->getPowerList());
        $this->view->assign("stateList", $this->model->getStateList());
        $this->view->assign("isParentstateList", $this->model->getIsParentstateList());
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

            $data = $this->request->request();
//            halt($data);
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
//            dump($where);die();
            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id', 'typedata','power', 'nickname', 'realname', 'head_image', 'code', 'idcard', 'phone', 'email', 'integral_total', 'integral', 'birthdaydate', 'state','createtime']);

            }
//            dump($list);die();
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }


//展示
    public function memberlist()
    {
        //当前是否为关联查询
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        //如果发送的来源是Selectpage，则转发到Selectpage
        if ($this->request->request('keyField')) {
            return $this->selectpage();
        }
        list($where, $sort, $order, $offset, $limit) = $this->buildparams();
        $where = [];
        $where['state'] = '1';
        $custom = $this->request->get('name', 1);//post('name/a');
        $name = $custom['0'];
        $where['realname'] = array('like', "%{$name}%");
        $total = Db::name('app_member')->where($where)->count();
        //$page = $this->request->get('pageNumber/d', 1);
        $list = Db::name('app_member')->where($where)->order('realname')->field('id,realname')->select();
        $list = collection($list)->toArray();
        $result = array("total" => $total, "list" => $list);
        return json($result);
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
            $result = \db('app_member')
                ->where('id', $id)
                ->update(['state' => 1]);
            if ($result) {
                $this->success('成功', '', $result);
            }

            // }
            $this->error('商户未提交审核');

        }
    }

    /**
     * 禁用状态
     */
    public function close()
    {
        $id = input('ids');
        if ($id != null) {
            $result = \db('app_member')
                ->where('id', $id)
                ->update(['state' => 2]);
            $this->success('禁用成功', '', $result);
        }
    }

    /**
     * 展示分享了多少人数
     */

    public function shareNumber()
    {
        $id = input('ids');
        if ($id != null) {
            $result = \db('extension')
                ->where('one_member_id', $id)
                ->count();

            //查询扫过自己的用户
            $info = \db('extension')
                ->alias('e')
                ->join('app_member m', 'e.two_member_id = m.id')
//                ->join('app_integral_log l', 'e.id = l.extension_id')
                ->where('e.one_member_id', $id)
                ->field('e.*,m.nickname,m.realname,m.head_image')
                ->select();

            foreach ($info as $k => $value) {
                if (strpos($info[$k]['head_image'], 'http') !== false) {
                   //有不管
                } else {
                    //不包含
                    $info[$k]['head_image'] = 'https://' . $_SERVER['HTTP_HOST'] . $value['head_image'];
                }

                $info[$k]['createtime'] =date("Y-d-m H:m:s",$value['createtime']);

            }

            return $this->view->fetch('share_number', ['count' => $result,'info'=>$info]);
        }

    }

    /**
     * 展示当前用户的推广码
     */
    public function showCode()
    {
        $id = input('ids');
        if ($id != null) {
            $result = \db('app_member')
                ->where('id', $id)
                ->field('')
                ->find();

            $result['share_code'] = 'https://' . $_SERVER['HTTP_HOST'] . $result['code'];

            return $this->view->fetch('share_code', ['code' => $result]);
        }

    }



}
