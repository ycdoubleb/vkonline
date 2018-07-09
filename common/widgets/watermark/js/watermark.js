(function (win, $) {
    /**
     * 
     * @param {Object} config
     *      container string 容器id
     *      background string 图片 or 颜色
     *      watermark string 水印：图片或其他
     * @returns {void}
     */
    var Watermark = function(config){
        /* 配置 */
        this.config = $.extend({
            container:'#container',
            background: '',
            watermark: '<img />'
        },config);
        
        /* 容器 */
        this.container = $(this.config['container']);
        /* 背景 */
        this.container.css({"background": this.config['background']});
        /* 所有水印 */
        this.watermarks = {};
    }
    
    /**
     * 添加水印
     * @param {string} waterId          水印ID
     * @param {type} waterConfig        水印配置
     * @returns {void}
     */
    Watermark.prototype.addWatermark = function (waterId, waterConfig) {
        this.watermarks [waterId] = waterConfig;
        //找到原先的 watermark com，如果没有新建
        if(this.container.find($(this.config['watermark'])).length <= 0){
            $(this.config['watermark']).attr("id", waterId).addClass('watermark').appendTo(this.container);
        }
        //更新水印
        this.updateWatermark(waterId, waterConfig);
    }

    /**
     * 更新水印
     * @param {string} waterId      水印ID
     * @param {type} waterConfig    水印配置{width, height, shifting_X, shifting_Y}    
     * @returns {void}
     */
    Watermark.prototype.updateWatermark = function (waterId, waterConfig) {
        this.watermarks [waterId] = waterConfig;
        //获取对应 watermark com
        var tatermark = $('#' + waterId);
        
        //验证数据
        var config = waterConfig;
        config.width = this.valuableWatermark(config.width, this.container.width());
        config.height = this.valuableWatermark(config.height, this.container.height());
        config.shifting_X = this.valuableWatermark(config.shifting_X, this.container.width());
        config.shifting_Y = this.valuableWatermark(config.shifting_Y, this.container.height());
        
        //更新水印图片
        if(tatermark.get(0).tagName == 'IMG'){
            tatermark.attr({src: Wskeee.StringUtil.completeFilePath(config.path)})
        }
        
        //判断水印的位置
        switch (config.refer_pos) {
            case 'TopRight':
                tatermark.css({bottom: '', left: ''})
                tatermark.css({
                    top: config.shifting_Y + 'px', right: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
            case 'TopLeft':
                tatermark.css({bottom: '', right: ''})
                tatermark.css({
                    top: config.shifting_Y + 'px', left: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
            case 'BottomRight':
                tatermark.css({top: '', left: ''});
                tatermark.css({
                    bottom: config.shifting_Y + 'px', right: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
            case 'BottomLeft':
                tatermark.css({top: '', right: ''});
                tatermark.css({
                    bottom: config.shifting_Y + 'px', left: config.shifting_X + 'px',
                    width: config.width + 'px', height: config.height + 'px',
                });
                break;
            default:
                tatermark.css({top: '0px', right: '0px'});
            }
    }
    
    /**
     * 删除水印
     * @param {string} waterId  水印ID
     * @return {void}
     */
    Watermark.prototype.removeWatermark = function(waterId){
        //获取对应 watermark com
        var tatermark = $('#' + waterId);
        //删除元素
        tatermark.remove();
    }
    
    /**
     * 验证数字 (0,1)[8,4096]
     * @param {Number} value    验证的数值
     * @param {Number} bgsize   背景宽高大小
     * @return {Number|@var;value}
     */
    Watermark.prototype.valuableWatermark = function(value, bgsize){
        value = Number(value);  //转为数字
        bgsize = Number(bgsize);//转为数字
        if (value < 8) {
            value = value <= 0 ? value = 0.13 : value;
            value = value > 1 ? value = 1 : value;
            value = value * bgsize;
        } else {
            value = value > 4096 ? value = 4096 : parseInt(value);
        }
        
        return value;
    }
    
    window.youxueba = window.youxueba || {};
    window.youxueba.Watermark = Watermark;
    
})(window, jQuery);