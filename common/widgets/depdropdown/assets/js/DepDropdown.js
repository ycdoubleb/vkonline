/**
 * 依赖上一级数据的多级下拉组件
 * 
 * @author wskeee
 * @param {Window} win
 * @param {jQuery} $
 * @returns {void}
 */
(function (win, $) {
    var DepDropdown = function ($config) {
        //组件配置
        this.config = $.extend({
            name: 'dep-dropdown',
            url: '',
            type: '',
            max_level: 4,
            prompt: '请选择...',
            itemOptions: '', //select 组件的父级DIV样式
            itemInputOptions: '', //select 组件样式
            value: null
        }, $config);
        //组件顶级容器
        this.container = $('#' + this.config['plug_id']);

        this.init();
    }
    var p = DepDropdown.prototype;
    p.init = function () {
        var self = this;
        var selects = this.container.find("select");
        $.each(selects, function (index, element) {
            $(this).on('change', function () {
                self.onChange.call(self, element);
            });
        });
    }

    p.onChange = function (elem) {
        var self = this;
        var index = Number($(elem).attr('data-level'));
        $('#' + self.config['name']).val(null);
        //清除其它下拉的选项
        //$("select[data-name='" + self.config['name'] + "']:gt(" + index + ")").html('<option>' + self.config['prompt'] + '</optoin>');
        this.container.find("select:gt(" + index + ")").remove();
        $.each(this.container.find("select"), function () {
            if ($(this).val() != "" && $(this).val() != self.config['prompt']) {
                $('#' + self.config['name']).val(self.config.value = $(this).val());
            }
        });
        /* 触发 onChange 事件 */
        if(self.config['onChangeEvent'] && typeof self.config['onChangeEvent'] === "function"){
            self.config['onChangeEvent'].call(self, self.config.value);
        }
        if ($(elem).val() == '' || index>= self.config['max_level'] - 1 ) {
            return;
        }

        $.get(self.config['url'], {id: $(elem).val()}, function (respond) {
            if (respond.data.length == 0) {
                return;
            }
            //更新下一个下拉选项
            var nextElem = self.__getSelect(index + 1, true);

            //加上请选择...
            $.each(respond.data, function () {
                $('<option/>').val(this['id']).text(this['name']).appendTo(nextElem);
            })
        });
    }

    /**
     * @param {int} level 
     * @param {bool} autoCreate 如果不存即自动创建
     * @returns {JQuery}
     */
    p.__getSelect = function (level, autoCreate) {
        var self = this;
        var selects = this.container.find("select[data-level=" + level + "]");
        if (selects.length > 0) {
            return selects[0];
        } else if (autoCreate) {
            var selectAtts = " data-level=" + level + " data-name=" + self.config['name'];
            var select = $(this.__tag('select', self.config['itemOptions'] + selectAtts)).appendTo(self.container);
            //添加 请选择...
            $('<option/>').val('').text(self.config['prompt']).appendTo(select);
            $(select).on('change', function () {
                self.onChange.call(self, select);
            });
            return select;
        }
        return null;
    }


    /**
     * 创建DOM
     * @param {string} name         dom标签
     * @param {string} options      dom选项
     * @returns {undefined}
     */
    p.__tag = function (name, options) {
        return '<' + name + " " + options + "></" + name + ">\n";
    }


    p.destory = function () {

    }

    //注册全局
    window.ewidegets = window.ewidegets || {};
    window.ewidegets.DepDropdown = DepDropdown;
})(window, jQuery);

