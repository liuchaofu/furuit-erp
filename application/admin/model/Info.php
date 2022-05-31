<?php

namespace app\admin\model;

use think\Model;


class Info extends Model
{

    

    

    // 表名
    protected $name = 'app_member_info';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'typedata_text',
        'catedata_text',
        'state_text',
        'checkdata_text'
    ];
    

    
    public function getTypedataList()
    {
        return ['0' => __('Typedata 0'), '1' => __('Typedata 1'), '2' => __('Typedata 2'), '3' => __('Typedata 3'), '4' => __('Typedata 4'),];
    }

    public function getCheckdataList()
    {
        return ['channel' => __('Checkdata channel'), 'merchant' => __('Checkdata merchant')];
    }

    public function getCatedataList()
    {
        return ['store' => __('Store'), 'person' => __('Person')];
    }

    public function getStateList()
    {
        return ['0' => __('State 0'), '1' => __('State 1'), '2' => __('State 2')];
    }

    public function getCheckdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['checkdata']) ? $data['checkdata'] : '');
        $list = $this->getCheckdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTypedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typedata']) ? $data['typedata'] : '');
        $list = $this->getTypedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getCatedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['catedata']) ? $data['catedata'] : '');
        $list = $this->getCatedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['state']) ? $data['state'] : '');
        $list = $this->getStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }

//关联模型
    public function member()
    {
        return $this->belongsTo('Member', 'member_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }



}
