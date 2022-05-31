<?php

namespace app\admin\model;

use think\Model;


class Sign extends Model
{




    protected $relationSearch = true;

    // 表名
    protected $name = 'app_sign_in';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'sign_time_text'
    ];
    

    public function getSignTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['sign_time']) ? $data['sign_time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setSignTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    //关联模型
    public function member()
    {
        return $this->belongsTo('Info', 'member_id', 'member_id', [], 'LEFT')->setEagerlyType(0);
    }


}
