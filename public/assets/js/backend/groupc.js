define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'groupc/index' + location.search,
                    add_url: 'groupc/add',
                    edit_url: 'groupc/edit',
                    del_url: 'groupc/del',
                    multi_url: 'groupc/multi',
                    import_url: 'groupc/import',
                    table: 'groupc',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'groupc_id',
                sortName: 'groupc_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'groupc_id', title: __('Groupc_id')},
                        {field: 'groupd_id', title: __('Groupd_id'),visible:false,operate:false},
                        {field: 'group_id', title: __('Group_id'),visible:false,operate:false},
                        {field: 'member_id', title: __('Member_id'),visible:false,operate:false},
                        {field: 'group.name', title: __('团购名称'), operate: 'LIKE'},
                        {field: 'groupd.name', title: __('成团名称'), operate: 'LIKE'},
                        {field: 'member.nickname', title: __('Member.nickname'), operate: 'LIKE'},
                        {field: 'purchase_quantity', title: __('团购数量')},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"已参与":__('已参与'),"成功":__('成功'),"失败":__('失败'),"已发货":__('已发货')}, formatter: Table.api.formatter.status},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
