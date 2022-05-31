<?php

return [
    [
        'name'    => 'classname',
        'title'   => '渲染文本框元素',
        'type'    => 'string',
        'content' => [],
        'value'   => '.editor',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '用于对指定的元素渲染，一般情况下无需修改',
        'ok'      => '',
        'extend'  => '',
    ],
    [
        'name'    => 'theme',
        'title'   => '编辑器主题',
        'type'    => 'select',
        'content' => [
            'default' => '经典主题',
            'black'   => '雅黑主题',
            'blue'    => '淡蓝主题',
            'grey'    => '深灰主题',
            'primary' => '深绿主题',
        ],
        'value'   => 'black',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'formulapreviewurl',
        'title'   => '数学公式预览URL',
        'type'    => 'string',
        'content' => [
        ],
        'value'   => 'https://math.now.sh?from={latex}',
        'rule'    => '',
        'msg'     => '',
        'tip'     => '用于渲染数学公式的URL',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'wordimageserver',
        'title'   => '启用word图片替换服务器',
        'type'    => 'radio',
        'content' => [
            '1' => '是',
            '0' => '否',
        ],
        'value'   => '0',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '如果启用，请务必先运行word.exe',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'attachmentmode_admin',
        'title'   => '管理员附件选择模式',
        'type'    => 'select',
        'content' => [
            'all'      => '任何管理员均可以查看全部上传的文件',
            'auth'     => '仅可以查看自己及所有子管理员上传的文件',
            'personal' => '仅可以查看选择自己上传的文件',
        ],
        'value'   => 'all',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
    [
        'name'    => 'attachmentmode_index',
        'title'   => '前台附件选择模式',
        'type'    => 'select',
        'content' => [
            'all'      => '任何会员均可以查看全部上传的文件',
            'personal' => '仅可以查看选择自己上传的文件',
        ],
        'value'   => 'personal',
        'rule'    => 'required',
        'msg'     => '',
        'tip'     => '',
        'ok'      => '',
        'extend'  => ''
    ],
];
