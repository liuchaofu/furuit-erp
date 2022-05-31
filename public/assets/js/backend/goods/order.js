define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'goods/order/index' + location.search,
                    add_url: 'goods/order/add',
                    edit_url: 'goods/order/edit',
                    del_url: 'goods/order/del',
                    multi_url: 'goods/order/multi',
                    import_url: 'goods/order/import',
                    table: 'goods_order',
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
                        {field: 'order_id', title: __('Order_id'), operate: 'LIKE'},
                        {field: 'order_time', title: __('Order_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false},
                        {field: 'goods_id', title: __('Goods_id')},
                        {field: 'weight', title: __('Weight'), operate:'BETWEEN'},
                        {field: 'pay_typedata', title: __('Pay_typedata'), searchList: {"online":__('Pay_typedata online'),"offline":__('Pay_typedata offline')}, formatter: Table.api.formatter.normal},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'total_price', title: __('Total_price'), operate:'BETWEEN'},
                        {field: 'group_price', title: __('Group_price'), operate:'BETWEEN'},
                        {field: 'coupon_price', title: __('Coupon_price'), operate:'BETWEEN'},
                        {field: 'freight', title: __('Freight'), operate:'BETWEEN'},
                        {field: 'pay_price', title: __('Pay_price'), operate:'BETWEEN'},
                        {field: 'pay_in', title: __('Pay_in'), operate:'BETWEEN'},
                        {field: 'activity_group_id', title: __('Activity_group_id')},
                        {field: 'activity_coupon_id', title: __('Activity_coupon_id')},
                        {field: 'member_id', title: __('Member_id')},
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
