define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'log/oprate/index' + location.search,
                    add_url: 'log/oprate/add',
                    edit_url: 'log/oprate/edit',
                    del_url: 'log/oprate/del',
                    multi_url: 'log/oprate/multi',
                    import_url: 'log/oprate/import',
                    table: 'oprate_log',
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
                        {field: 'username', title: __('Username'), operate: 'LIKE'},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {field: 'catedata', title: __('Catedata'), searchList: {"add":__('Catedata add'),"edit":__('Catedata edit'),"write":__('Catedata write')}, formatter: Table.api.formatter.normal},
                        {field: 'key', title: __('Key'), operate: false},
                        {field: 'value', title: __('Value'), operate: false},
                        {field: 'tablename', title: __('Tablename'), operate: 'LIKE'},
                        {field: 'coupond_code', title: __('Coupond_code'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name:'showDetail',
                                    text:'详情',
                                    title:'详情',
                                    classname: 'btn btn-xs btn-primary btn-view btn-dialog',
                                    // icon: 'fa fa-arrow-down',
                                    url: 'log/oprate/showDetail',
                                },

                            ],formatter: Table.api.formatter.operate,
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
