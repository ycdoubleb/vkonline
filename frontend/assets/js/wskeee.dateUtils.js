/**
 * 日期工具
 * Created by Administrator on 2018-03-23 .
 */
(function(win){
    
    function DateUtil(){};
    
    /**
     * 当前时间戳
     * @return <int>    unix时间戳(秒) 
     */
    DateUtil.curTime = function()
    {
        var date = new Date();
        return date.parse(date) / 1000;
    }
    
    /**       
     * 日期 转换为 Unix时间戳
     * @param <string> 2014-01-01 20:20:20 日期格式       
     * @return <int>    unix时间戳(秒)       
     */
    DateUtil.dateToUnix = function(string)
    {
        var f = string.split(' ', 2);
        var d = (f[0] ? f[0] : '').split('-', 3);
        var t = (f[1] ? f[1] : '').split(':', 3);
        return (new Date(
            parseInt(d[0], 10) || null,
            (parseInt(d[1], 10) || 1) - 1,
            parseInt(d[2], 10) || null,
            parseInt(t[0], 10) || null,
            parseInt(t[1], 10) || null,
            parseInt(t[2], 10) || null
        )).getTime() / 1000;
    }
    
    /**       
     * 时间戳 转换 日期       
     * @param <str> format  格式 (Y-m-d 或者 Y-m-d H:i 或者 Y-m-d H:i:s)     
     * @param <int> unixTime  待时间戳(秒)       
     * @param <int> timeZone  时区       
     */
    DateUtil.unixToDate = function(format, unixTime, timeZone)
    {
        if (typeof (timeZone) == 'number'){
            unixTime = parseInt(unixTime) + parseInt(timeZone) * 60 * 60;
        }
        var time = new Date(unixTime * 1000);
        var strTime = "";
        switch(format){
            case 'Y-m-d H:i':
                strTime = time.getUTCFullYear() + "-" + (time.getUTCMonth() + 1) + "-" + time.getUTCDate() + " " + time.getUTCHours() + ":" + time.getUTCMinutes();
                break;
            case 'Y-m-d H:i:s':
                strTime = time.getUTCFullYear() + "-" + (time.getUTCMonth() + 1) + "-" + time.getUTCDate() + " " + time.getUTCHours() + ":" + time.getUTCMinutes() + ":" + time.getUTCSeconds();
                break;
            default:
                strTime = time.getUTCFullYear() + "-" + (time.getUTCMonth() + 1) + "-" + time.getUTCDate();
        }
       
        return strTime;
    }
    
    /**
     * 数字转换成时间格式
     * @param integer value
     * @param boolean defaultFormat [true(hh:mm:ss), false(mm:ss)]
     * @returns hh:mm:ss|mm:ss
     */
    DateUtil.intToTime = function (value, defaultFormat)
    {
        var h = parseInt(value / 3600);
        var i = parseInt(value % 3600 / 60);
        var s = parseInt(value % 60);
        
        return (defaultFormat ? this.zeor(h) + ':' : '')  + this.zeor(i) + ':' +  this.zeor(s);
    };
    
    /**
     * 表示作为人类可读格式的持续时间的值
     * @param integer value
     * @param boolean defaultFormat [true(hh:mm:ss), false(mm:ss)]
     * @returns hh:mm:ss|mm:ss
     */
    DateUtil.asDuration = function (value, defaultFormat)
    {
        var h = parseInt(value / 3600);
        var i = parseInt(value % 3600 / 60);
        var s = parseInt(value % 60);
        
        return (defaultFormat ? (h > 0 ? h + '时' : '') : '') + (i > 0 ? i + '分钟' : '') + (s > 0 ? s + '秒' : '');
    };
    
    /**
     * 小于9自动在数字前添加0
     * @param integer value
     */
    DateUtil.zeor = function (value)
    {
        return value > 9 ? value : "0" + value;
    };
    
    win.Wskeee = win.Wskeee || {};
    win.Wskeee.DateUtil = DateUtil;
    return DateUtil;    
})(window);