<?php

namespace app\admin\model;

use think\Model;


class Order extends Model
{





    // 表名
    protected $name = 'goods_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'pay_typedata_text'
    ];



    public function getPayTypedataList()
    {
        return ['offline' => __('Pay_typedata offline'),'online' => __('Pay_typedata online')];
    }


    public function getPayTypedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['pay_typedata']) ? $data['pay_typedata'] : '');
        $list = $this->getPayTypedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
