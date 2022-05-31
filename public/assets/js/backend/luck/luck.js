define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'luck/luck/index' + location.search,
                    add_url: 'luck/luck/add',
                    edit_url: 'luck/luck/edit',
                    del_url: 'luck/luck/del',
                    multi_url: 'luck/luck/multi',
                    import_url: 'luck/luck/import',
                    table: 'luck',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'luck_id',
                sortName: 'luck_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'luck_id', title: __('Luck_id')},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'total', title: __('Total'), operate: 'LIKE'},
                        {field: 'background_image', title: __('Background_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        {field: 'btime', title: __('Btime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'etime', title: __('Etime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"未开始":__('未开始'),"进行中":__('进行中'),"已结束":__('已结束')}, formatter: Table.api.formatter.status},
                        {field: 'max_time', title: __('Max_time')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table,
                            buttons: [
                                {name: '奖项设置', text: '奖项设置', title: '奖项设置', icon: 'fa fa-list', classname: 'btn btn-xs btn-primary btn-dialog', url: 'luck/luckitem/index/luck_id/{luck_id}'},
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