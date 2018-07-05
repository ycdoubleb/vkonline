(function (win, $) {
    /**
     * 
     * @param {Object} config
     *      bg String 背景ID
     * @returns {void}
     */
    var Watermark = function(config){
        /* 配置 */
        this.config = $.extends({
            bg:'#bg',
            container:'#container',
        },config);
        
        /* 容器 */
        this.container = $(this.config['container']);
        /* 背景 */
        this.bg = null;
        /* 所有水印 */
        this.watermarks = {};
    }
    
    /**
     * 添加水印
     * @param {string} waterId          水印ID
     * @param {type} waterConfig        水印配置
     * @returns {void}
     */
    Watermark.property.addWatermark = function (waterId, waterConfig) {
        this.watermarks [waterId] = waterConfig;
        //找到原先的 watermark com，如果没有新建
        //...
        //$('<img id="'+waterId+'"/>').appendTo(this.container);
        //更新水印
        //...
        
        this.updateWatermark(waterId, waterConfig);
    }

    /**
     * 更新水印
     * @param {string} waterId      水印ID
     * @param {type} waterConfig    水印配置
     * @returns {void}
     */
    Watermark.prototype.updateWatermark = function (waterId, waterConfig) {
        this.watermarks [waterId] = waterConfig;
        //获取对应 watermark com
        var tatermark = null;//....
        
        //验证数据
        var config = waterConfig;
        config.width = Number(config.width);
        config.height = Number(config.height);
        if(config.width <= 8){
            //百份比
            config.width = config.width <= 0 ? config.width = 0.13 : config.width;
            config.width = config.width > 1 ? config.width = 1 : config.width;
            config.width = config.width * this.bg.width();
        }else if(config.width >= 8){
            //真实大小
            config.width = config.width > 4096 ? config.width = 4096 : config.width;
        }
        
        if(config.height <= 8){
            //百份比
            config.height = config.height <= 0 ? config.height = 0.13 : config.height;
            config.height = config.height > 1 ? config.height = 1 : config.height;
            config.height = config.height * this.bg.height();
        }else if(config.height >= 8){
            //真实大小
            config.height = config.height > 4096 ? config.height = 4096 : config.height;
        }
        
        //更新水印图片
        tatermark.attr({src: Wskeee.StringUtil.completeFilePath(config.src)})     //水印图路径
        
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
    
    window.youxueba = window.youxueba || {};
    window.youxueba.Watermark = Watermark;
    
})(window, jQuery);