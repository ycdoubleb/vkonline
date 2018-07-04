/**
* 预览水印图位置
* @param object|json config
*/
window.Watermark = function(config){
   config = $.extend({}, config);
   //如果width不是整数，则乘底图width
   if(!Wskeee.StringUtil.isInteger(config.width)){
       config.width = config.width * $("#preview").width();
   }
   //如果width为0的时候，水印图的width为底图width * 0.13
   if(Number(config.width) == 0){
       config.width = $("#preview").width() * 0.13;
   }
   //如果height不是整数，则乘底图height
   if(!Wskeee.StringUtil.isInteger(Number(config.height))){
       config.height = config.height * $("#preview").height();
   }
   //如果height为0的时候，水印图的height为底图height * 0.13
   if(Number(config.height) == 0){
       config.height = $("#preview").height() * 0.13;
   }
   $(".watermark").attr({src: Wskeee.StringUtil.completeFilePath(config.src)})     //水印图路径
   //判断水印的位置
   switch(config.refer_pos){
       case 'TopRight':
           $(".watermark").css({bottom: '', left: ''})
           $(".watermark").css({
               top: config.shifting_Y + 'px',  right: config.shifting_X + 'px', 
               width: config.width + 'px',  height: config.height + 'px',
           });
           break;
       case 'TopLeft':
           $(".watermark").css({bottom: '', right: ''})
           $(".watermark").css({
               top: config.shifting_Y + 'px', left: config.shifting_X + 'px',
               width: config.width + 'px', height: config.height + 'px',
           });
           break;
       case 'BottomRight':
           $(".watermark").css({top: '', left: ''});
           $(".watermark").css({
               bottom: config.shifting_Y + 'px', right: config.shifting_X + 'px',
               width: config.width + 'px', height: config.height + 'px',
           });
           break;
       case 'BottomLeft':
           $(".watermark").css({top: '', right: ''});
           $(".watermark").css({
               bottom: config.shifting_Y + 'px', left: config.shifting_X + 'px',
               width: config.width + 'px', height: config.height + 'px',
           });
           break;
       default:
           $(".watermark").css({top: '0px', right: '0px'});
   }
}