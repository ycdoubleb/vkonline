/**
 * 依赖上一级数据的多级下拉组件
 * 
 * @author wskeee
 * @param {Window} win
 * @param {jQuery} $
 * @returns {void}
 */
(function(win,$){
    var DepDropdown = function($config){
        this.config = $.extend({
            name : 'dep-dropdown',
            url : '',
            type : '',
            level : 2,
            pleaceholder : '请选择...',
            value : null
        },$config);
        this.init();
    }
    var p = DepDropdown.prototype;
    p.init = function(){
        var self = this;
        var selects = $("select[data-name='"+this.config['name']+"']");
        $.each(selects,function(index,element){
            $(this).on('change',function(){
                self.onChange.call(self,element);
            });
        });
    }
    
    p.onChange = function(elem){
        var self = this;
        var index = Number($(elem).attr('data-level'));
        $('#'+self.config['name']).val(null);
        //清除其它下拉的选项
        $("select[data-name='"+self.config['name']+"']:gt("+index+")").html('<option>'+self.config['pleaceholder']+'</optoin>');
        $.each($("select[data-name='"+self.config['name']+"']"),function(){
            if($(this).val()!="" && $(this).val()!=self.config['pleaceholder']){
                $('#'+self.config['name']).val($(this).val());
            }
        });
        
        $.get(self.config['url'],{id:$(elem).val()},function(respond){
            //更新下一个下拉选项
            var nextElem = $("select[data-name='"+self.config['name']+"'][data-level="+(index+1)+"]");
            //加上请选择...
            $.each(respond.data,function(){
                $('<option/>').val(this['id']).text(this['name']).appendTo(nextElem);
            })
        });
    }
    
    p.destory = function(){
        
    }
    
    //注册全局
    window.ewidegets = window.ewidegets || {};
    window.ewidegets.DepDropdown = DepDropdown;
})(window,jQuery);

