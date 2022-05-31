<?php

namespace app\admin\model;

use think\Model;


class Member extends Model
{

    

    

    // 表名
    protected $name = 'app_member';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'typedata_text',
        'state_text',
        'is_parentstate_text'
    ];
    

    
    public function getTypedataList()
    {
        return ['member' => __('Typedata member'), 'shop' => __('Typedata shop'), 'channel' => __('Typedata channel')];
    }

    public function getStateList()
    {
        return ['0' => __('State 0'), '1' => __('State 1'), '2' => __('State 2'), '3' => __('State 3')];
    }

    public function getPowerList()
    {
        return ['0' => __('Power 0'), '1' => __('Power 1')];
    }

    public function getIsParentstateList()
    {
        return ['0' => __('Is_parentstate 0'), '1' => __('Is_parentstate 1')];
    }


    public function getTypedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typedata']) ? $data['typedata'] : '');
        $list = $this->getTypedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['state']) ? $data['state'] : '');
        $list = $this->getStateList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getPowerTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['power']) ? $data['power'] : '');
        $list = $this->getPowerList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIsParentstateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['is_parentstate']) ? $data['is_parentstate'] : '');
        $list = $this->getIsParentstateList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
