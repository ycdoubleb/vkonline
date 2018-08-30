(function (win, $) {
    
    /**
     * 
     * @param {Object} plugOptions
     * @returns {TabSelfColumnL#1.TabSelfColumn}
     */
    var TabSelfColumn = function(plugOptions){
        this.init(plugOptions);
    }
    
    /**
     * 初始化
     * @param {Object} plugOptions
     * @returns {void}
     */
    TabSelfColumn.prototype.init = function(plugOptions){
        /* 配置 */
        this.plugOptions = $.extend({
            id: 'tabcolumn_' + Math.ceil(Math.random() * 100000),
            labels: ['否', '是'],   //触发式按钮显示,默认['否','是']，eg:['禁用','启用']
            values: [0, 1],         //触发式按钮值,触发式按钮值,默认[0,1],eg:[0,10]
            url: 'change-value',    //链接
            type: 'checkbox',   //类型
            disabled: false,    //禁用
            //交互事件
            events: {
                checkbox : 'onclick',
                input: 'onchange',
            },
            //数据
            data:{
                key: 0,
                fieldName: '',
                value: 0,
                dome: "this",
            }
        }, plugOptions);
        
        //创建组件数据中心，保存各个组件的labels和values
        window.TabSelfColumn = window.TabSelfColumn || {};
        //添加当前组件数据到数据中心
        window.TabSelfColumn[this.plugOptions.id] = {
            'labels': this.plugOptions.labels,
            'values': this.plugOptions.values
        };
        
        //提供数据单元内容
        return this.renderDataCellContent(this.plugOptions.data);
    }
    
    /**
     * 提供数据单元内容
     * @param {Object} data
     * @returns {void}
     */
    TabSelfColumn.prototype.renderDataCellContent = function(data) {
        var content;
        var events = this.plugOptions.events;   //交互事件
        var labels = this.plugOptions.labels;   //触发式按钮显示
        var values = this.plugOptions.values;   //触发式按钮值
        var inputOptions = new Array();     //对象配置
        
        //如果非“禁用”，则执行，否则设置对象为“禁用”
        if(!this.plugOptions.disabled){
            inputOptions['id'] = this.plugOptions.id;
            inputOptions[events[this.plugOptions.type]] = "changeSelfColumnVal("+data.key+",'"+data.fieldName+"',"+ data.dome+",'"+this.plugOptions.url+"')";
        }else{
            inputOptions['disabled'] = 'disabled';
            inputOptions['style'] = 'opacity: 0.5';
        }
        
        // 生成dome
        switch(this.plugOptions.type){
            case 'checkbox':
                var label = data.value == values[1] ? '<i class="fa fa-check-circle"></i>' + labels[1] : '<i class="fa fa-ban"></i>' + labels[0];
                content = $('<span />').html(label).attr($.extend({class: data.value == values[1] ? 'yes' : 'no'}, inputOptions));
                break;
            case 'input':
                content = $('<input />').attr($.extend({type: 'text', class: 'form-control', value: data.value}, inputOptions));
                break;
        }
        
        return content;
    }
    
    /**
     * 更新字段值
     * @param {int|string} id       目标ID
     * @param {string} fieldName    字段名称
     * @param {Object} _this        dome
     * @param {string} url          链接        
     * @returns {void}
     */
    window.changeSelfColumnVal = function(key, fieldName, _this, url){
        var plugin_id = $(_this).attr('id');    //获取对象id
        var plugin_data = window.TabSelfColumn[plugin_id];  //获取生成的组件数据
        var labels = plugin_data['labels'];     //触发式按钮显示
        var values = plugin_data['values'];     //触发式按钮值
        var value;  //保存数据值
        
        // 触发式按钮，如果该对象存在样式“no”，则执行
        if($(_this).hasClass('no')) 
        {          
            $(_this).removeClass('no').addClass('yes');
            $(_this).html("<i class='fa fa-check-circle'></i>" + labels[1]);
            value = values[1];
        // 触发式按钮，如果该对象存在样式“yes”，则执行
        }else if($(_this).hasClass('yes')){                    
            $(_this).removeClass('yes').addClass('no');
            $(_this).html("<i class='fa fa-ban'></i>" + labels[0]);
            value = values[0];
        // 其他输入框操作
        }else{ 
            value = $(_this).val();
        }
        
        // $.ajax 提交执行保存
        $.ajax({
            url: url,
            data:{id: key, fieldName: fieldName, value: value},
            success: function(data){
                if(data['result'] == 0){
                    $.notify({
                        message: data['message'],
                    },{type: 'danger'}); 
                }
            }
        });		
    }
    
    window.tabcolumn = window.tabcolumn || {};
    window.tabcolumn.TabSelfColumn = TabSelfColumn;
    
})(window, jQuery);