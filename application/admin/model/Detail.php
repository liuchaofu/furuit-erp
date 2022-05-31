<?php

namespace app\admin\model;

use think\Model;


class Detail extends Model
{

    

    

    // 表名
    protected $name = 'app_sign_detail';
    
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

    public function sign()
    {
        return $this->belongsTo('Sign', 'sign_id', 'id', [], 'LEFT')->setEagerlyType(0);

    }


}
