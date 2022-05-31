<?php

namespace app\admin\model;

use think\Model;


class Coupond extends Model
{


    // 表名
    protected $name = 'coupond';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'usetime_text',
        'gettime_text',
        'status_text'
    ];


    public function getStatusList()
    {
        return ['已领取' => __('已领取'), '已使用' => __('已使用'), '已过期' => __('已过期'), '已结算' => __('已结算')];
    }


    public function getUsetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['usetime']) ? $data['usetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getGettimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['gettime']) ? $data['gettime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setUsetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setGettimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function coupon()
    {
        return $this->belongsTo('Coupon', 'coupon_id', 'coupon_id', [], 'LEFT')->setEagerlyType(0);
    }


//    public function member()
//    {
//        return $this->belongsTo('app\admin\model\app\Member', 'member_id', 'id', [], 'LEFT')->setEagerlyType(0);
//    }
    public function member()
    {
        return $this->belongsTo('Info', 'shop_id', 'crm_shop_id', [], 'LEFT')->setEagerlyType(0);
    }

    public function user()
    {
        return $this->belongsTo('Info', 'member_id', 'member_id', [], 'LEFT')->setEagerlyType(0);
    }

}
