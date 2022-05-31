<?php

namespace app\admin\controller\luck;

use app\common\controller\Backend;
use think\Db;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Luckrecord extends Backend
{

    /**
     * Luckrecord模型对象
     * @var \app\admin\model\luck\Luckrecord
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\luck\Luckrecord;
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
                ->with(['luckitem','luck','member'])
                ->where($where)
                ->order($sort, $order)
                ->paginate($limit);

            foreach ($list as $row) {
                $row->getRelation('luckitem')->visible(['name']);
                $row->getRelation('luck')->visible(['name']);
                $row->getRelation('member')->visible(['nickname']);
            }
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 兑奖
     */
    public function check(){
        $luckrecord_id = input("luckrecord_id");

        $luckrecordTable = Db::name("luckrecord");

        $luckrecordInfo = $luckrecordTable->where("luckrecord_id","=",$luckrecord_id)->find();
        if($luckrecordInfo['status'] == '未兑奖'){
            //兑奖
            $luckrecordData = array(
                'luckrecord_id' => $luckrecord_id,
                'status' => '已兑奖',
                'admin_id' => $this->auth->id,
                'checktime' => time()
            );
            $flag = $luckrecordTable->update($luckrecordData);
            if($flag){
                $this->success("兑奖成功");
            }else{
                $this->error("兑奖失败");
            }
        }else{
            $this->error("对不起，当前已兑奖");
        }
    }
}
