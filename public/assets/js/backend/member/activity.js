define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'member/activity/index' + location.search,
                    add_url: 'member/activity/add',
                    edit_url: 'member/activity/edit',
                    del_url: 'member/activity/del',
                    multi_url: 'member/activity/multi',
                    import_url: 'member/activity/import',
                    table: 'app_channel_activity',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'typedata', title: __('Typedata'), searchList: {"coupon":__('Typedata coupon'),"group":__('Typedata group')}, formatter: Table.api.formatter.normal},
                        {field: 'member_id', title: __('Member_id')},
                        {field: 'activity_id', title: __('Activity_id')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
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
