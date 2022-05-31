define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'member/info/index' + location.search,
                    add_url: 'member/info/add',
                    edit_url: 'member/info/edit',
                    del_url: 'member/info/del',
                    multi_url: 'member/info/multi',
                    import_url: 'member/info/import',
                    table: 'app_member_info',
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
                        // {field: 'member_id', title: __('Member_id')},
                        {field: 'checkdata', title: __('Checkdata'), searchList: {"channel":__('Checkdata channel'),"merchant":__('Checkdata merchant')}, formatter: Table.api.formatter.normal},
                        {field: 'typedata', title: __('Typedata'), searchList: {"0":__('Typedata 0'),"1":__('Typedata 1'),"2":__('Typedata 2'),"3":__('Typedata 3'),"4":__('Typedata 4'),}, formatter: Table.api.formatter.normal},
                        {field: 'shop_name', title: __('Shop_name'), operate: 'LIKE'},
                        // {field: 'shop_image', title: __('Shop_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        {field: 'phone', title: __('Phone'), operate: false,},
                        {field: 'id_card', title: __('Id_card'), operate: false,},
                        {field: 'city', title: __('City'), operate: false,},
                        {field: 'house_num', title: __('House_num'), operate: false,},
                        {field: 'state', title: __('State'), searchList: {"0":__('State 0'),"1":__('State 1'),"2":__('State 2')}, formatter: Table.api.formatter.normal},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name:'adopt',
                                    text:'通过',
                                    title:'通过',
                                    classname: 'btn btn-xs btn-primary btn-ajax',
                                    // icon: 'fa fa-arrow-up',
                                    url: 'member/info/adopt/',
                                    visible:function(row){
                                        if(row.state==0){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    },
                                    refresh:true
                                },
                                {
                                    name:'close',
                                    text:'禁用',
                                    title:'禁用',
                                    classname: 'btn btn-xs btn-danger btn-view btn-ajax',
                                    // icon: 'fa fa-arrow-down',
                                    url: 'member/info/close',
                                    visible:function(row){
                                        if(row.state==0 || row.state==1){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    },
                                    refresh:true
                                },

                            ],formatter: Table.api.formatter.operate,

                        }
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
