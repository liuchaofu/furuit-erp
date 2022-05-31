define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'member/member/index' + location.search,
                    add_url: 'member/member/add',
                    edit_url: 'member/member/edit',
                    del_url: 'member/member/del',
                    multi_url: 'member/member/multi',
                    import_url: 'member/member/import',
                    table: 'app_member',
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
                        {field: 'typedata', title: __('Typedata'), searchList: {"member":__('Typedata member'),"shop":__('Typedata shop'),"channel":__('Typedata channel')}, formatter: Table.api.formatter.normal},
                        {field: 'power', title: __('Power'), searchList: {"0":__('Power 0'),"1":__('Power 1')}, formatter: Table.api.formatter.normal},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {field: 'realname', title: __('Realname'), operate: 'LIKE'},
                        {field: 'head_image', title: __('Head_image'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},

                        {field: 'code', title: __('Code'),table: table,
                            buttons:[
                                {
                                    name:'showCode',
                                    text:'推广码',
                                    title:'推广码',
                                    classname: 'btn btn-xs btn-primary btn-view btn-dialog',
                                    // icon: 'fa fa-arrow-down',
                                    url: 'member/member/showCode',
                                },

                            ],formatter: Table.api.formatter.buttons,operate:false},


                        // {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'idcard', title: __('Idcard'), operate:false},
                        {field: 'phone', title: __('Phone'), operate:false},
                        // {field: 'email', title: __('Email'), operate: 'LIKE'},
                        {field: 'integral_total', title: __('Integral_total'),operate:false},
                        {field: 'integral', title: __('Integral'),operate:false},
                        // {field: 'birthdaydate', title: __('Birthdaydate'),formatter: Table.api.formatter.datetime, operate: 'RANGE', addclass: 'date', sortable: true
                        // },
                        {field: 'state', title: __('State'), searchList: {"0":__('State 0'),"1":__('State 1'),"2":__('State 2'),"3":__('State 3')}, formatter: Table.api.formatter.normal,operate:false},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate,
                            buttons:[
                                {
                                    name:'adopt',
                                    text:'认证',
                                    title:'认证',
                                    classname: 'btn btn-xs btn-primary btn-ajax',
                                    // icon: 'fa fa-arrow-up',
                                    url: 'member/member/adopt/',
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
                                    url: 'member/member/close',
                                    visible:function(row){
                                        if(row.state==0 || row.state == 1){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    },
                                    refresh:true
                                },
                                {
                                    name:'shareNumber',
                                    text:'分享人数',
                                    title:'分享人数',
                                    classname: 'btn btn-xs btn-primary btn-view btn-dialog',
                                    // icon: 'fa fa-arrow-down',
                                    url: 'member/member/shareNumber',
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
var check = $('input:radio[name="row[typedata]"]:checked').val();
if(check === 'member'){
    $("#dis").show();
}else{
    $('input:radio[name="row[power]"]:checked').val(0);
    $("#dis").hide();
}

$('input:radio[name="row[typedata]"]').click(function(){
    var checkValue = $('input:radio[name="row[typedata]"]:checked').val();
    if(checkValue === 'member'){
        $("#dis").show();
    }else{
        $('input:radio[name="row[power]"]:checked').val(0);
        $("#dis").hide();
    }
});
