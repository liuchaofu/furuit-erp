<?php

namespace app\admin\controller\log;

use app\common\controller\Backend;
use think\Session;

/**
 * 操作日志
 *
 * @icon fa fa-circle-o
 */
class Oprate extends Backend
{

    /**
     * Oprate模型对象
     * @var \app\admin\model\Oprate
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Oprate;
        $this->view->assign("catedataList", $this->model->getCatedataList());
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

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            //判断当前用户看自己的

            $admin = Session::get('admin');
            $username = $admin['username'];
            if ($username == 'admin') {
                $where1 = [];
            } else {
                $where1['username']  =$username;

            }

            $list = $this->model
                ->where($where)
                ->where($where1)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $value) {
                if (strlen($value['key']) > 10) {

                    $value['key'] = mb_substr($value['key'], 0, 10, 'utf-8') . "...";
                }
                if (strlen($value['value']) > 10) {

                    $value['value'] = mb_substr($value['value'], 0, 10, 'utf-8') . "...";
                }


            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }


    /**
     * 展示当前用户的修改或者添加的数据
     */
    public function showDetail()
    {
        $id = input('ids');
        if ($id != null) {
            $result = \db('oprate_log')
                ->where('id', $id)
                ->field('key,value')
                ->find();

            if ($result['key'] == '') {
                $arr = [
                    0 => '核销无字段'
                ];
                return $this->view->fetch('add_detail', ['worth' => $arr]);
            }

            //判断增加还是修改

            $a = ',';
            if (strstr($result['key'], $a) !== false) {
                //修改

                //修改前值
                $key = $result['key'];
                $testArr = explode(',', $key);

                //分拆为2数组  修改前的值
                $key_name = explode('+', $testArr[0]);
                $key_value = explode('+', $testArr[1]);
                $arr = [];
                foreach ($key_value as $k => $v) {
                    $arr[$key_name[$k]] = $key_name[$k] . ":   " . $v;
                }


                //修改后的值
                $value = $result['value'];
                $valueArr = explode(',', $value);

                //分拆为2数组  修改前的值
                $value_name = explode('+', $valueArr[0]);
                $value_value = explode('+', $valueArr[1]);
                $arr1 = [];
                foreach ($value_value as $k => $v) {
                    $arr1[$value_name[$k]] = $value_name[$k] . ":   " . $v;
                }
                $old = $arr;
                $new = $arr1;

                return $this->view->fetch('edit_detail', ['old' => $old, 'new' => $new]);

            } else {
                //添加
                $value_key = explode('+', $result['key']);

                $value_value = explode('+', $result['value']);

                $arr = [];
                foreach ($value_value as $k => $v) {
                    $arr[$value_key[$k]] = $value_key[$k] . ":   " . $v;
                }

                return $this->view->fetch('add_detail', ['worth' => $arr]);
            }

        }

    }

}
