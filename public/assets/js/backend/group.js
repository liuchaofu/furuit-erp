define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'group/index' + location.search,
                    add_url: 'group/add',
                    edit_url: 'group/edit',
                    del_url: 'group/del',
                    multi_url: 'group/multi',
                    import_url: 'group/import',
                    table: 'group',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'group_id',
                sortName: 'group_id',
                fixedColumns: true,
                fixedRightNumber: 1,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'group_id', title: __('Group_id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        // {field: 'image', title: __('Image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'type', title: __('Type'), searchList: {"人数":__('人数'),"金额":__('金额'),"件数":__('件数')}, formatter: Table.api.formatter.normal},
                        // {field: 'head_image', title: __('Head_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'background_image', title: __('Background_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'number', title: __('Number')},
                        {field: 'unit', title: __('Unit'), operate: 'LIKE'},
                        {field: 'minimum', title: __('起购数量')},
                        {field: 'surplus', title: __('剩余数量')},
                        // {field: 'maxorder', title: __('成单数')},
                        {field: 'goods_type', title: __('Goods_type'), searchList: {"商品":__('商品')}, formatter: Table.api.formatter.normal},
                        // {field: 'goods_id', title: __('Goods_id')},
                        {field: 'goods.name', title: __('商品名'), operate: 'LIKE'},
                        {field: 'goods_price', title: __('Goods_price')},
                        {field: 'group_price', title: __('Group_price')},
                        // {field: 'value', title: __('Value')},
                        {field: 'status', title: __('Status'), searchList: {"未开始":__('未开始'),"已开始":__('已开始'),"已结束":__('已结束'),"已满员":__('已满员')}, formatter: Table.api.formatter.status},
                        {field: 'stime', title: __('Stime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'etime', title: __('Etime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'everytime', title: __('Everytime')},
                        // {field: 'everyorder', title: __('Everyorder')},

                        // {field: 'days', title: __('Days'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        // {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
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

$(document).on("change", "#c-goods_id", function(){
   // var s =  $('#c-goods_id').selectPageText();
// console.log(row);
    // alert(row.id);
});
