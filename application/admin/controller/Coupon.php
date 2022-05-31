<?php

namespace app\admin\controller;

use app\common\controller\Backend;
use think\Db;
use Exception;
use think\exception\PDOException;
use think\exception\ValidateException;
use think\Session;

/**
 * 优惠券管理
 *
 * @icon fa fa-circle-o
 */
class Coupon extends Backend
{

    /**
     * Coupon模型对象
     * @var \app\admin\model\Coupon
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\Coupon;
        $this->view->assign("typeList", $this->model->getTypeList());

        $this->view->assign("owntypeList", $this->model->getOwntypeList());
        $this->view->assign("voucherdataList", $this->model->getVoucherdataList());

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
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }



    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);

                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;

                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                        $this->model->validateFailException(true)->validate($validate);
                    }

                    $result = $this->model->allowField(true)->save($params);
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
                }
                if ($result !== false) {
                    //加入记录表

                    $admin = Session::get('admin');
                    $time = time();
                    $value['username'] = $admin['username'];
                    $value['nickname'] = $admin['nickname'];
                    $value['createtime'] = $time;
                    $value['updatetime'] = $time;
                    $value['tablename'] = '优惠券列表';
                    $value['catedata'] = 'add';
                    //key
                    $keysData = array_keys($params);
                    $arr1 = implode("+", $keysData);
                    $value['key'] = $arr1;
                    //value
                    $arr2 = implode("+", $params);
                    $value['value'] = $arr2;
                    $result = \db('oprate_log')->insertGetId($value);
                    if ($result) {
                        $this->success();
                    } else {
                        $this->error('插入记录表失败');
                    }

                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        //修改前的值 key
        $before = \db('coupon')
            ->where('coupon_id', $ids)
            ->select();

        $filed = ['getstime', 'getetime', 'usestime', 'useetime', 'createtime'];
        $editList = $this->dateformate($before, $filed);
        //删除数据库暂时不用数据
        unset($editList[0]['yuzhi']);
        unset($editList[0]['get_type']);
        unset($editList[0]['get_type']);

        $old = $editList[0];

        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            if ($params) {
                $params = $this->preExcludeFields($params);
                $result = false;
                Db::startTrans();
                try {
                    //是否采用模型验证
                    if ($this->modelValidate) {
                        $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                        $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                        $row->validateFailException(true)->validate($validate);
                    }

                    $result = $row->allowField(true)->save($params);
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
                }
                if ($result !== false) {
                    //加入记录表

                    $admin = Session::get('admin');
                    $time = time();
                    $value['username'] = $admin['username'];
                    $value['nickname'] = $admin['nickname'];
                    $value['createtime'] = $time;
                    $value['updatetime'] = $time;
                    $value['tablename'] = '优惠券列表';
                    $value['catedata'] = 'edit';

                    //之前 k
                    $before_key = array_diff_assoc($old, $params);
                    $arr1 = array_keys($before_key);
                    $value['key'] = implode("+", $arr1) .",". implode("+", $before_key);

                    //现在 v
                    $now_value = array_diff_assoc($params, $old);
                    $arr = array_keys($now_value);
                    $value['value'] = implode("+", $arr) .",". implode("+", $now_value);


                    $result = \db('oprate_log')->insertGetId($value);
                    if ($result) {
                        $this->success();
                    } else {
                        $this->error('插入记录表失败');
                    }

                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        return $this->view->fetch();
    }


}
