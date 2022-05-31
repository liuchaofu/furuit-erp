<?php

namespace app\admin\model;

use think\Model;


class Groupc extends Model
{

    

    

    // 表名
    protected $name = 'groupc';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];
    

    
    public function getStatusList()
    {
        return ['已参与' => __('已参与'), '成功' => __('成功'), '失败' => __('失败'), '已发货' => __('已发货')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function group()
    {
        return $this->belongsTo('Group', 'group_id', 'group_id', [], 'LEFT')->setEagerlyType(0);
    }


    public function groupd()
    {
        return $this->belongsTo('Groupd', 'groupd_id', 'groupd_id', [], 'LEFT')->setEagerlyType(0);
    }


    public function member()
    {
        return $this->belongsTo('app\admin\model\app\Member', 'member_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
