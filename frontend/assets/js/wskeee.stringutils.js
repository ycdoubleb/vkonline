/**
 * 字符工具
 * Created by Administrator on 2018-03-23 .
 */
(function(win){

    function StringUtil(){};
    /**
     * 渲染DOM
     * @param renderer      DOM模板
     * @param data          变量数据
     * @returns {Object}    html
     */
    StringUtil.renderDOM = function (renderer, data)
    {
        var daName = [],
            daVal = [],
            efn = [];
        for (var i in data) {
            daName.push(i);
            daVal.push("data." + i);
        }
        var _renderer = "'" + renderer + "'";
        _renderer = _renderer.replace(/\{\%/g, "'+");
        _renderer = _renderer.replace(/\%\}/g, "+'");
        efn.push("(function(");
        efn.push(daName.join(","));
        efn.push("){");
        efn.push("return " + _renderer);
        efn.push("})(");
        efn.push(daVal.join(","));
        efn.push(")");
        return eval(efn.join(""));
    };
    
    /**
     * 数字转换成时间格式
     * @param integer $value
     * @param boolean $default [true(hh:mm:ss), false(mm:ss)]
     */
    StringUtil.intToTime = function($value, $default = false)
    {
        var $h = Math.floor($value / 3600);
        var $m = Math.floor($value % 3600 / 60);
        var $s = Math.floor($value % 60);
        
        return ($default ? StringUtil.zeor($h) + ':' : '') + StringUtil.zeor($m) + ':' + StringUtil.zeor($s);
    }
    /**
     * 字符转int
     * 
     * @param string $strTime     12:20:21
     * @return int 长度
     */
    StringUtil.timeToInt = function($strTime){
        /*
        if(!typeof($strTime) == 'number')  
        {  
            var $times = [];
            if(strpos($strTime ,":"))  
            {  
                $times =  explode(":", $strTime);  
            }else if(strpos($strTime ,'：')){  
                $times =  explode(":", $strTime);  
            }else  
            {  
                return 0;  
            }  
            var $h = (number)$times[0] ;  
            var $m = (number)$times[1];  
            var $s = count($times) == 3 ? (int)$times[2] : 0;  
            return $h * 3600 + $m * 60 + $s;  
        }else{
            return 0;
        } */
;    }
    
    /**
     * 小于9自动在数字前添加0
     * @param int $value
     */
    StringUtil.zeor = function($value)
    {
        return $value > 9 ? ""+$value :"0"+$value;
    }
    
    win.Wskeee = win.Wskeee || {};
    win.Wskeee.StringUtil = StringUtil;
    return StringUtil;
})(window);