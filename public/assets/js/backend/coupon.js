define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupon/index' + location.search,
                    add_url: 'coupon/add',
                    edit_url: 'coupon/edit',
                    del_url: 'coupon/del',
                    multi_url: 'coupon/multi',
                    import_url: 'coupon/import',
                    table: 'coupon',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'coupon_id',
                sortName: 'coupon_id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'coupon_id', title: __('Coupon_id')},
                        {field: 'voucherdata', title: __('Voucherdata'), searchList: {"receive":__('Voucherdata receive'),"issue":__('Voucherdata issue'),"share":__('Voucherdata share'),"luck":__('Voucherdata luck')}, formatter: Table.api.formatter.normal},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'price', title: __('Price'), operate:'BETWEEN'},
                        {field: 'max', title: __('Max')},
                        {field: 'share_total', title: __('Share_total'), operate:'BETWEEN'},
                        {field: 'full_minus', title: __('Full_minus'), operate:'BETWEEN'},
                        {field: 'background_image', title: __('Background_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'total', title: __('Total')},
                        {field: 'left', title: __('Left')},
                        {field: 'type', title: __('Type'), searchList: {"满减":__('满减'),"折扣":__('折扣'),"抵用":__('抵用')}, formatter: Table.api.formatter.normal},

                        {field: 'owntype', title: __('Owntype'), searchList: {"领取计算时长":__('领取计算时长'),"固定时长":__('固定时长')}, formatter: Table.api.formatter.normal},

                        {field: 'getstime', title: __('Getstime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'getetime', title: __('Getetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'usestime', title: __('Usestime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'useetime', title: __('Useetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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
