<?php

namespace app\admin\model\luck;

use think\Model;


class Luckrecord extends Model
{

    

    

    // 表名
    protected $name = 'luckrecord';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text',
        'checktime_text'
    ];
    

    
    public function getStatusList()
    {
        return ['已兑奖' => __('已兑奖'), '未兑奖' => __('未兑奖')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getChecktimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['checktime']) ? $data['checktime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setChecktimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    public function luckitem()
    {
        return $this->belongsTo('Luckitem', 'luckitem_id', 'luckitem_id', [], 'LEFT')->setEagerlyType(0);
    }


    public function luck()
    {
        return $this->belongsTo('Luck', 'luck_id', 'luck_id', [], 'LEFT')->setEagerlyType(0);
    }


    public function member()
    {
        return $this->belongsTo('app\admin\model\app\Member', 'member_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

}
