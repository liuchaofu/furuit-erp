define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'member/detail/index' + location.search,
                    add_url: 'member/detail/add',
                    edit_url: 'member/detail/edit',
                    del_url: 'member/detail/del',
                    multi_url: 'member/detail/multi',
                    import_url: 'member/detail/import',
                    table: 'app_sign_detail',
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
                        {field: 'sign_id', title: __('Sign_id')},
                        {field: 'sign_time', title: __('Sign_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'sign.member_id', title: __('签到id'),operate:false,},
                        {field: 'sign.sign_count', title: __('连续签到数量'),operate:false,},
                        {field: 'sign.total_count', title: __('签到总数'),operate:false,},

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
