<?php

namespace app\admin\model;

use think\Model;


class Oprate extends Model
{

    

    

    // 表名
    protected $name = 'oprate_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'catedata_text'
    ];
    

    
    public function getCatedataList()
    {
        return ['add' => __('Catedata add'), 'edit' => __('Catedata edit'), 'write' => __('Catedata write')];
    }


    public function getCatedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['catedata']) ? $data['catedata'] : '');
        $list = $this->getCatedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
