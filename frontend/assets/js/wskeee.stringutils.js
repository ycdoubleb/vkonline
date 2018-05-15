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
    win.Wskeee = win.Wskeee || {};
    win.Wskeee.StringUtil = StringUtil;
    return StringUtil;
})(window);