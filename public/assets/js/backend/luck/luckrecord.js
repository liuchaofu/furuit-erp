define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'luck/luckrecord/index' + location.search,
                    add_url: 'luck/luckrecord/add',
                    edit_url: 'luck/luckrecord/edit',
                    del_url: 'luck/luckrecord/del',
                    multi_url: 'luck/luckrecord/multi',
                    import_url: 'luck/luckrecord/import',
                    table: 'luckrecord',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'luckrecord_id',
                sortName: 'luckrecord_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'luckrecord_id', title: __('Luckrecord_id')},
                        {field: 'luck_id', title: __('Luck_id'),visible:false},
                        {field: 'member_id', title: __('Member_id'),visible:false},
                        {field: 'luckitem_id', title: __('Luckitem_id'),visible:false},
                        {field: 'luck.name', title: __('Luck.name'), operate: 'LIKE'},
                        {field: 'member.nickname', title: __('Member.nickname'), operate: 'LIKE'},
                        {field: 'admin.nickname', title: __('Admin.nickname'), operate: 'LIKE'},
                        // {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'luckitem.name', title: __('Luckitem.name'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"已兑奖":__('已兑奖'),"未兑奖":__('未兑奖')}, formatter: Table.api.formatter.status},
                        {field: 'admin_id', title: __('Admin_id'),visible:false},
                        {field: 'checktime', title: __('Checktime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {
                                    name: 'ajax',
                                    text: __('兑奖'),
                                    title: __('兑奖'),
                                    classname: 'btn btn-xs btn-warning btn-magic btn-ajax',
                                    // icon: 'fa fa-magic',
                                    url: 'luck/luckrecord/check/luckrecord_id/{luckrecord_id}',
                                    confirm: '确认兑奖么',
                                    success: function (data,ret) {
                                        table.bootstrapTable('refresh',{silent: true });
                                    }
                                }
                            ],
                            events: Table.api.events.operate, formatter: Table.api.formatter.operate
                        }
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
