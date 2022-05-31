<?php

namespace app\admin\model;

use think\Model;


class Integral extends Model
{
    // 表名
    protected $name = 'app_integral_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'typedata_text'
    ];
    

    
    public function getTypedataList()
    {
        return ['day' => __('Typedata day'), 'buy' => __('Typedata buy'),'new' => __('Typedata new'), 'invite' => __('Typedata invite')];
    }


    public function getTypedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typedata']) ? $data['typedata'] : '');
        $list = $this->getTypedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }
//关联模型
    public function member()
    {
        return $this->belongsTo('Member', 'member_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


}
