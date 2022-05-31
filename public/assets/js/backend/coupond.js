define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'coupond/index' + location.search,
                    add_url: 'coupond/add',
                    edit_url: 'coupond/edit',
                    del_url: 'coupond/del',
                    multi_url: 'coupond/multi',
                    import_url: 'coupond/import',
                    table: 'coupond',
                }
            });

            var table = $("#table");

            //改为核销
            $(document).on("click", ".btn-write", function () {
                var data = table.bootstrapTable('getSelections');
                var ids = [];
                if (data.length === 0) {
                    Toastr.error("请选择操作信息");
                    return;
                }
                for (var i = 0; i < data.length; i++) {
                    //拿参数值
                    ids[i] = data[i]['coupond_id']
                }
                Layer.confirm(
                    '确认选中的' + ids.length + '条改为核销吗?', {
                        icon: 3,
                        title: __('Warning'),
                        offset: '40%',
                        shadeClose: true
                    },
                    function (index) {
                        Layer.close(index);
                        Backend.api.ajax({
                            // url: "coupond/write?ids=" + JSON.stringify(ids),
                            //方法一：传参方式，后台需要转换变成数组
                            /*url: "coupond/write?ids=" + (ids),
                            data: {}*/
                            //方法二：传参方式，直接是数组传递给后台
                            url: "coupond/write",
                            data: {
                                ids: ids
                            }
                        }, function (data, ret) { //成功的回调
                            if (ret.code === 1) {

                                table.bootstrapTable('refresh');
                                Layer.close(index);
                            } else {
                                Layer.close(index);
                                Toastr.error(ret.msg);
                            }
                        }, function (data, ret) { //失败的回调
                            console.log(ret);
                            // Toastr.error(ret.msg);
                            Layer.close(index);
                        });
                    }
                );
            });

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'coupond_id',
                sortName: 'coupond_id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'coupond_id', title: __('Coupond_id')},
                        {field: 'coupon_id', title: __('Coupon_id'),visible:false,operate:false},
                        {field: 'member_id', title: __('Member_id'),visible:false,operate:false},

                        {field: 'user.name', title: __('领券人姓名'), operate: 'LIKE'},
                        {field: 'user.phone', title: __('领券人电话'), operate: 'LIKE'},

                        {field: 'coupon.name', title: __('Coupon.name'), operate: 'LIKE'},
                        {field: 'member.shop_name', title: __('Member.shop_name'), operate: false},
                        {field: 'member.name', title: __('Member.name'), operate: 'LIKE'},
                        {field: 'member.phone', title: __('Member.phone'), operate: 'LIKE'},
                        {field: 'member.id_card', title: __('Member.id_card'), operate: false},
                        {field: 'member.house_num', title: __('Member.house_num'), operate: false},
                        {field: 'number', title: __('Number'),operate: false},
                        {field: 'shop_id', title: __('Shop_id')},
                        {field: 'usetime', title: __('Usetime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'gettime', title: __('Gettime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'code', title: __('Code'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {"已领取":__('已领取'),"已使用":__('已使用'),"已过期":__('已过期'),"已结算":__('已结算')}, formatter: Table.api.formatter.status},
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
