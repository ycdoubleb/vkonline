/**
 * 字符工具
 * Created by Administrator on 2018-03-23 .
 */
(function (win) {

    function StringUtil() {}
    ;
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
     * 分析 Jquery(form).serialize() 生成对应json对象
     * @param {string} serialize
     * @param {boolean} decoed     是否需要使用decodeURIComponent对serialize转义 默认为true
     * @returns {wskeee.stringutilsL#5.StringUtil.parseJquerySerialize.obj}
     */
    StringUtil.parseJquerySerialize = function (serialize, decoed) {
        var reg = /([^=&\s]+)[=\s]*([^&\s]*)/g;
        var obj = {};
        if (decoed == undefined) {
            decoed = true;
        }
        while (reg.exec(decoed ? decodeURIComponent(serialize) : serialize)) {
            if (!obj[RegExp.$1]) {
                obj[RegExp.$1] = RegExp.$2;
            } else if (obj[RegExp.$1]) {
                if (typeof obj[RegExp.$1] != 'object') {
                    obj[RegExp.$1] = [obj[RegExp.$1]];
                }
                obj[RegExp.$1].push(RegExp.$2);
            }
        }
        return obj;
    };

    /**
     * 数字转换成时间格式
     * @param integer $value
     * @param boolean $default [true(hh:mm:ss), false(mm:ss)]
     */
    StringUtil.intToTime = function ($value, $default)
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
    StringUtil.timeToInt = function ($strTime) {
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
    StringUtil.zeor = function ($value) {
        return $value > 9 ? "" + $value : "0" + $value;
    }

    /**
     * 补全文件路径
     * @param srting path  路径
     * @param string|array withStr 指定的字符，默认['http://', 'https://', '/']
     * @param srting appendStr  补全的字符，默认‘/’
     * @return srting
     */
    StringUtil.completeFilePath = function (path, withStr, appendStr) {
        appendStr = appendStr || '/';
        //如果withStr为空的，默认['http://', 'https://', '/']
        if (withStr == '' || withStr == null) {
            withStr = ['http://', 'https://', '/'];
        }
        //如果withStr不是数组，默认转为数组
        if (!(withStr instanceof Array)) {
            withStr = [withStr];
        }
        //如果参数path为空，默认为空字符串
        if (path == 'undefined' || path == null) {
            path = '';
        }
        //判断指定的字符串是否存在，若不存在则补全
        var isAppendStr = false;
        for (var str in withStr) {
            if (path.indexOf(withStr[str]) !== 0) {
                isAppendStr = true;
            } else {
                isAppendStr = false;
                break;
            }
        }

        return isAppendStr ? appendStr + path : path;
    }
    
    /**
     * 格式化字节   如：传 formatBytes(1024) 返回 1k
     * @param {int} bytes           字节数
     * @param {int} decimals        小数默认 2
     * @returns {String}
     */
    StringUtil.formatBytes = function (bytes, decimals) {
        if (bytes == 0)
            return '0 Bytes';
        var k = 1024,
                dm = decimals || 2,
                sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'],
                i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    /**
     * 获取文件的后缀名
     * 如果存在指定的文件后缀名类型，则直接返回获取到的文件后缀名
     * @param {String} filePath 文件路径
     * @param {Object|Array} suffixNameMap  指定的文件后缀名
     * @returns {String}    如：docx、xlsx、pptx
     */
    StringUtil.getFileSuffixName = function (filePath, suffixNameMap) {
        suffixNameMap = $.extend(['docx', 'xlsx', 'pptx'], suffixNameMap);   //特定的文件后缀名
        var suffixName = '',
            regLastStr = /^[A-Z]+$/,   //大写字符串字母正则表达式
            suffix = filePath.substring(filePath.lastIndexOf(".") + 1),     //获取后缀名
            lastStr = suffix.substr(suffix.length -1, 1);   //获取最后一个后缀名的字符串
        /* 不在suffixNameMap（特定的后缀名数组）里，则执行 */
        if($.inArray(suffix, suffixNameMap) == -1){
            //如果后缀名最后一个字符是大写
            if(regLastStr.test(lastStr) && lastStr == 'X'){
                suffixName = suffix.replace(/X/, 'x');  //大写的X替换为小写
            }else if(lastStr == 'x'){
                suffixName = suffix;
            }else{
                suffixName = suffix + 'x';
            }
        }else{
            suffixName = suffix;
        }
        
        return suffixName;
    }

    /**
     * 判断字符串对象是否是Integer类型
     * @param {string} value
     * @return {Boolean}
     */
    StringUtil.isInteger = function (value) {
        if ((/^(\+|-)?\d+$/.test(value))) {
            return true;
        } else {
            return false;
        }
    }

    win.Wskeee = win.Wskeee || {};
    win.Wskeee.StringUtil = StringUtil;
    return StringUtil;
})(window);