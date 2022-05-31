KindEditor.plugin('math', function (K) {
    var self = this, name = 'math', lang = self.lang(name + '.');
    self.clickToolbar(name, function () {
        var img = self.plugin.getSelectedImage();
        var latex = $(img).data("latex") || '';
        var html = [
            '<div class="ke-dialog-content-inner">',
            '<div class="tabs"></div>',
            '<div class="ke-formula" style="width:510px;height:380px;"></div>',
            '</div>'
        ].join('');
        var iframe = K('<iframe class="" frameborder="0" src="' + self.pluginsPath + 'math/formula.html?latex=' + encodeURIComponent(latex) + '&previewUrl=' + encodeURIComponent(self.options.formulaPreviewUrl) + '" style="width:100%;height:300px;"></iframe>');

        var dialog = self.createDialog({
                name: name,
                width: Math.min(document.body.clientWidth, 500),
                height: 380,
                title: "插入公式",
                body: html,
                yesBtn: {
                    name: '插入',
                    click: function (e) {
                        var win = iframe[0].contentWindow;
                        var url = win.$("#codecogslink").attr("href");
                        var latex = win.$("#latex-source").val();
                        if (latex == '') {
                            Layer.msg("请选择或输入公式");
                            return false;
                        }
                        self.insertHtml("<img src='" + url + "' data-latex='" + latex + "'>");
                        self.hideDialog().focus();
                    }
                },
                noBtn: {
                    name: self.lang('no'),
                    click: function (e) {
                        self.hideDialog().focus();
                    }
                }
            }),
            div = dialog.div;

        K('.ke-formula', div).replaceWith(iframe);
        return;

    });
});
