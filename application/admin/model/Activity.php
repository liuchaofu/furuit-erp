<?php

namespace app\admin\model;

use think\Model;


class Activity extends Model
{

    

    

    // 表名
    protected $name = 'app_channel_activity';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'typedata_text',

    ];
    

    
    public function getTypedataList()
    {
        return ['coupon' => __('Typedata coupon'), 'group' => __('Typedata group')];
    }




    public function getTypedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typedata']) ? $data['typedata'] : '');
        $list = $this->getTypedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
