define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'member/integral/index' + location.search,
                    add_url: 'member/integral/add',
                    edit_url: 'member/integral/edit',
                    del_url: 'member/integral/del',
                    multi_url: 'member/integral/multi',
                    import_url: 'member/integral/import',
                    table: 'app_integral_log',
                }
            });

            var table = $("#table");

            //表格加载完成后执行
            // table.on('post-common-search.bs.table',function(event, table){
            //     var form = $("form", table.$commonsearch);
            //     $("input[name='members_id']", form).addClass("selectpage").data("source", "member/integral/index").data('primaryKey', 'id').data("field", 'realname').data('orderBy', "id desc");
            //     Form.events.cxselect(form);
            //     Form.events.selectpage(form);
            // });


            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'typedata', title: __('Typedata'), searchList: {"day":__('Typedata day'),"buy":__('Typedata buy'),"new":__('Typedata new'),"invite":__('Typedata invite')}, formatter: Table.api.formatter.normal},
                        {field: 'member_id', title: __('Member_id'),operate:false},
                        {field: 'member.nickname', title: __('昵称'),operate: 'LIKE'},
                        {field: 'member.realname', title: __('真实姓名'), operate: 'LIKE'},
                        {field: 'start_integral', title: __('Start_integral'),operate:false},
                        {field: 'add_integral', title: __('Add_integral'),operate:false},
                        {field: 'end_integral', title: __('End_integral'),operate:false},
                        {field: 'remarks', title: __('Remarks'),operate:false},
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
