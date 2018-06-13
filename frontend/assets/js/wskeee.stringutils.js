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
    }
    
    /**
     * 删除左右两端的空格
     * @param {string} str
     * @returns {string}
     */
    StringUtil.trim = function (str) {
        return str.replace(/(^\s*)|(\s*$)/g, "");
    }

    /**
     * 删除左边的空格
     * @param {string} str
     * @returns {string}
     */
    StringUtil.ltrim = function (str) {
        return str.replace(/(^\s*)/g, "");
    }

    /**
     * 删除右边的空格
     * @param {string} str
     * @returns {string}
     */
    StringUtil.rtrim = function (str) {
        return str.replace(/(\s*$)/g, "");
    }   
    
    /**
     * 小于9自动在数字前添加0
     * @param int $value
     */
    StringUtil.zeor = function($value){
        return $value > 9 ? ""+$value :"0"+$value;
    }
    
    /**
     * 补全文件路径
     * @param srting path  路径
     * @param string|array withStr 指定的字符，默认['http://', 'https://', '/']
     * @param srting appendStr  补全的字符，默认‘/’
     * @return srting
     */
    StringUtil.completeFilePath = function(path, withStr = '', appendStr = '/'){
        //如果withStr为空的，默认['http://', 'https://', '/']
        if(withStr == '' || withStr == null){
            withStr = ['http://', 'https://', '/'];
        }
        //如果withStr不是数组，默认转为数组
        if(!(withStr instanceof Array)){
            withStr = [withStr];
        }
        //判断指定的字符串是否存在，若不存在则补全
        var isAppendStr = false;
        for(var str in withStr){
            if(path.indexOf(withStr[str]) !== 0){
                isAppendStr = true;
            }else{
                isAppendStr = false;
                break;
            }
        }
        
        return isAppendStr ? appendStr + path : path;
    }
    
    win.Wskeee = win.Wskeee || {};
    win.Wskeee.StringUtil = StringUtil;
    return StringUtil;
})(window);