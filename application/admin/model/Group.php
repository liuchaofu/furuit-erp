<?php

namespace app\admin\model;

use think\Model;


class Group extends Model
{

    

    

    // 表名
    protected $name = 'group';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'goods_type_text',
        'status_text',
        'stime_text',
        'etime_text',
        'everytime_text'
    ];
    

    
    public function getTypeList()
    {
        return ['人数' => __('人数'), '金额' => __('金额'), '件数' => __('件数')];
    }

    public function getGoodsTypeList()
    {
        return ['商品' => __('商品')];
    }

    public function getStatusList()
    {
        return ['未开始' => __('未开始'), '已开始' => __('已开始'), '已结束' => __('已结束'), '已满员' => __('已满员')];
    }


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getGoodsTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['goods_type']) ? $data['goods_type'] : '');
        $list = $this->getGoodsTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['stime']) ? $data['stime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['etime']) ? $data['etime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEverytimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['everytime']) ? $data['everytime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }

    protected function setStimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEverytimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    //关联模型
    public function goods()
    {
        return $this->belongsTo('Goods', 'goods_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}
