define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'groupd/index' + location.search,
                    add_url: 'groupd/add',
                    edit_url: 'groupd/edit',
                    del_url: 'groupd/del',
                    multi_url: 'groupd/multi',
                    import_url: 'groupd/import',
                    table: 'groupd',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'groupd_id',
                sortName: 'groupd_id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        // {field: 'groupd_id', title: __('Groupd_id')},
                        // {field: 'group_id', title: __('Group_id')},
                        {field: 'group.name', title: __('团购名称'), operate: 'LIKE'},
                        {field: 'name', title: __('成团名称'), operate: 'LIKE'},
                        {field: 'describe', title: __('描述'), operate: 'LIKE'},

                        {field: 'max_num', title: __('Max_num')},
                        {field: 'num', title: __('Num')},
                        {field: 'status', title: __('Status'), searchList: {"拼团中":__('拼团中'),"已完成":__('已完成'),"已流单":__('已流单'),"已发货":__('已发货')}, formatter: Table.api.formatter.status},
                        {field: 'stime', title: __('Stime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'etime', title: __('Etime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'ctime', title: __('Ctime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'ptime', title: __('Ptime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

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
