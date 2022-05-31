define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'member/sign/index' + location.search,
                    add_url: 'member/sign/add',
                    edit_url: 'member/sign/edit',
                    del_url: 'member/sign/del',
                    multi_url: 'member/sign/multi',
                    import_url: 'member/sign/import',
                    table: 'app_sign_in',
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
                        // {field: 'member_id', title: __('Member_id'),operate:false,},
                        // {field: 'admin_id', title: '操作人', addclass:"selectpage", extend:'data-source="auth/admin/index" data-field="username"'},
                        {field: 'member.name', title: __('真实姓名'),operate: 'LIKE'},
                        // {field: 'member.nickname', title: __('昵称'),addclass:"selectpage", extend:'data-source="member/member/index" data-field="nickname"',operate: 'LIKE'},
                        {field: 'sign_time', title: __('Sign_time'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime,},
                        {field: 'total_count', title: __('签到总数'),operate:false,},
                        {field: 'sign_count', title: __('连续签到'),operate:false,},
                        {field: 'gps_count', title: __('位置签到'),operate:false,},
                        {field: 'detail', title: __('签到列表'),table: table,
                            buttons:[
                                {
                                    name:'showDetail',
                                    text:'签到详情',
                                    title:'签到详情',
                                    classname: 'btn btn-xs btn-primary btn-view btn-dialog',
                                    // icon: 'fa fa-arrow-down',
                                    url: 'member/sign/showDetail',
                                },

                            ],formatter: Table.api.formatter.buttons,operate:false},

                        // {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ],
                search: false,
                commonSearch: true,
                //让搜索始终显示
                // searchFormVisible: true,
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
