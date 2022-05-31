define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'info/activity/index' + location.search,
                    add_url: 'info/activity/add',
                    edit_url: 'info/activity/edit',
                    del_url: 'info/activity/del',
                    multi_url: 'info/activity/multi',
                    import_url: 'info/activity/import',
                    table: 'activity',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'id', title: __('Id')},
                        {field: 'type', title: __('Type'), searchList: {"团购":__('团购'),"其它":__('其它')}, formatter: Table.api.formatter.normal},
                        {field: 'group_id', title: __('Group_id')},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'activity_image', title: __('Activity_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'num', title: __('Num')},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        {field: 'stime', title: __('Stime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'etime', title: __('Etime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('状态'),searchList: {"活动中":__('活动中'),"已停用":__('已停用')}, formatter: Table.api.formatter.normal},
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
