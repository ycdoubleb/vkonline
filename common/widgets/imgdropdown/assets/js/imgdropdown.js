/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

(function (win, $) {
    
    /**
     * 
     * @param {Object} plugOptions
     * @returns {undefined}
     */
    var imgDropdown = function(plugOptions){
        this.init(plugOptions);
    }
    
    /**
     * 初始化
     * @param {Object} plugOptions
     * @returns {void}
     */
    imgDropdown.prototype.init = function(plugOptions){
        /* 配置 */
        this.plugOptions = $.extend({
            id: 'imgdd',
            name: 'imgdd',
            disabled: false,    //禁用
            data: [],
        }, plugOptions);
        
        //下拉框id
        this.select_id = $this.plugOptions.id + '_' + Math.ceil(Math.random() * 1000);
        //下拉框name
        this.select_name = $this.plugOptions.name;
        
        //数据对象
        this.data = this.plugOptions.data;
        
        return this.render();
    }
    
    /**
     * 
     * @returns {undefined}
     */
    imgDropdown.prototype.initPlaceholder = function() {
        
        var dropdownObject = $('select').attr({id: this.select_id, name: this.select_name});  
        
        $.ecah(this.data);
        
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
