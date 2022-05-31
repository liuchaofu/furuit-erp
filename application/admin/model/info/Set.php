<?php

namespace app\admin\model\info;

use think\Model;


class Set extends Model
{

    

    

    // 表名
    protected $name = 'set_up';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







}
