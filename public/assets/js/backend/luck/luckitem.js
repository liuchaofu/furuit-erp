define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'luck/luckitem/index' + location.search,
                    add_url: 'luck/luckitem/add',
                    edit_url: 'luck/luckitem/edit',
                    del_url: 'luck/luckitem/del',
                    multi_url: 'luck/luckitem/multi',
                    import_url: 'luck/luckitem/import',
                    table: 'luckitem',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'luckitem_id',
                sortName: 'luckitem_id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'luckitem_id', title: __('ID')},
                        {field: 'luck.name', title: __('Luck.name')},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'name', title: __('Name'), searchList: {"优惠券":__('优惠券'),"积分":__('积分'),"其它":__('其它'),"礼品":__('礼品')}, formatter: Table.api.formatter.normal},
                        {field: 'coupon.name', title: __('Coupon.name')},
                        {field: 'value', title: __('Value'), operate: 'LIKE'},
                        {field: 'probability', title: __('Probability'), operate:'BETWEEN'},
                        {field: 'total', title: __('Total')},
                        {field: 'left', title: __('Left')},
                        {field: 'logo_image', title: __('Logo_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
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
