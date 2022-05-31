<?php

namespace app\admin\model;

use think\Model;


class Coupon extends Model
{

    

    

    // 表名
    protected $name = 'coupon';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'owntype_text',
        'getstime_text',
        'getetime_text',
        'usestime_text',
        'useetime_text',
        'voucherdata_text'
    ];


    public function getVoucherdataList()
    {
        return ['receive' => __('Voucherdata receive'), 'issue' => __('Voucherdata issue'),'share' => __('Voucherdata share'),'luck' => __('Voucherdata luck')];
    }

    
    public function getTypeList()
    {
        return ['满减' => __('满减'), '折扣' => __('折扣'), '抵用' => __('抵用')];
    }

    public function getOwntypeList()
    {
        return ['领取计算时长' => __('领取计算时长'), '固定时长' => __('固定时长')];
    }

    public function getVoucherdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['voucherdata']) ? $data['voucherdata'] : '');
        $list = $this->getVoucherdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getOwntypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['owntype']) ? $data['owntype'] : '');
        $list = $this->getOwntypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getGetstimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['getstime']) ? $data['getstime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getGetetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['getetime']) ? $data['getetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUsestimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['usestime']) ? $data['usestime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getUseetimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['useetime']) ? $data['useetime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setGetstimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setGetetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUsestimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setUseetimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
