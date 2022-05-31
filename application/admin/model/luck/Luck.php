<?php

namespace app\admin\model\luck;

use think\Model;


class Luck extends Model
{

    

    

    // 表名
    protected $name = 'luck';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'btime_text',
        'etime_text',
        'status_text',
        'max_time_text'
    ];
    

    
    public function getStatusList()
    {
        return ['未开始' => __('未开始'), '进行中' => __('进行中'), '已结束' => __('已结束')];
    }


    public function getBtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['btime']) ? $data['btime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['etime']) ? $data['etime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getMaxTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['max_time']) ? $data['max_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setBtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setMaxTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
