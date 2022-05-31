<?php

namespace app\admin\model\luck;

use think\Model;


class Luckitem extends Model
{

    

    

    // 表名
    protected $name = 'luckitem';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'name_text'
    ];
    

    
    public function getNameList()
    {
        return ['优惠券' => __('优惠券'), '积分' => __('积分'), '其它' => __('其它'), '礼品' => __('礼品')];
    }


    public function getNameTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['name']) ? $data['name'] : '');
        $list = $this->getNameList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function luck()
    {
        return $this->belongsTo('Luck', 'luck_id', 'luck_id', [], 'LEFT')->setEagerlyType(0);
    }

    public function coupon()
    {
        return $this->belongsTo('app\admin\model\Coupon', 'coupon_id', 'coupon_id', [], 'LEFT')->setEagerlyType(0);
    }
}
