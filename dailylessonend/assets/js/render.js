function renderHtml (renderer, data) {
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
