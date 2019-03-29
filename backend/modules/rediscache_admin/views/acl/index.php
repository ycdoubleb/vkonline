<?php

use backend\modules\rediscache_admin\assets\RedisCacheAdminAsset;
use yii\web\View;

/* @var $this View */

RedisCacheAdminAsset::register($this);

$this->title = Yii::t('app', '{Visit}{Path}', [
            'Visit' => Yii::t('app', 'Visit'), 'Path' => Yii::t('app', 'Path')
        ]);
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="rediscache_admin-default-index">
    <div style="width:50%;display: inline-block;margin-right: 10px;">
        <input id="key-input" list="browsers" name="browser" onchange="changeInput()" class="form-control" />
        <datalist id="browsers">
            <option value="mediacloud:acl:data:*">
            <option value="mediacloud:user_visit_log:*">
            <option value="mediacloud:media_visit_log:*">
            <option value="mediacloud:acl:dirty">
        </datalist>
        <select multiple="multiple" class="form-control keylist" size="30" onchange="selectKey($(this).val()[0])">
        </select>
    </div>
    <div style="width:49%;display: inline-block;float: right;">
        <table class="key-info">
            <tbody>
                <tr class="name">
                    <th>Key</th>
                    <td id="info-name"></td>
                </tr>
                <tr class="type">
                    <th>Type</th>
                    <td id="info-type"></td>
                </tr>
                <tr class="ttl">
                    <th>TTL</th>
                    <td id="info-ttl"></td>
                </tr>
            </tbody>
        </table>
        <div class="detail-flow">
            <table class="key-detail">

            </table>
        </div>
    </div>
</div>
<script>
    window.onload = function(){
        searchKey();
    };
    
    /**
     * 搜索框内容更改后调用searchKey()
     * @returns {undefined}
     */
    function changeInput(){
        var val = $("#key-input").val();
        // 判断最后一个字符串是否为‘*’
//        if(val.slice(-1) === "*"){
            searchKey(val);
//        }else{
//            searchKey(val + '*');
//        }
    }
    
    /**
     * 搜索key
     * @param {type} key    搜索条件
     * @returns {undefined}
     */
    function searchKey(key){
        if(key == null || key == ""){
            key = "*";
        }
        $.get('/rediscache_admin/acl/search-key',{key:key},function(r){
            buildKeyList(r.data.keys);
        });
    }
    /**
     * 构建键列表
     * @param {array} kyes
     * @returns {void}
     */
    function buildKeyList(keys){
        $('.keylist').empty();
        $.each(keys,function(index,item){
            $('.keylist').append($(Wskeee.StringUtil.renderDOM('<option value="{%label%}">{%label%}</option>',{label:item})));
        });
    }
    
    /**
     * 选择显示当前key
     * @param {string} key
     * @returns {void}
     */
    function selectKey(key){
        $.get('/rediscache_admin/acl/get-value',{key:key},function(rel){
            reflashKeyDetail(rel.data);
        });
    }
    
    /**
     * 获取当前key的详细信息
     * @param {object} data
     * @returns {undefined}
     */
    function reflashKeyDetail(data){
        $('#info-name').html(data.key);
        $('#info-type').html(data.type);
        $('#info-ttl').html(data.ttl);
        $(".key-detail").empty();   // 清空
        switch(data.type){
            case 'zset': setZsetValue(data.values);
                break;
            case 'hash': setHash(data.values);
                break;
            default: setDefault(data.values);
        }
    }
    
    /**
     * 生成表格 Type:zset
     * @param {array} values
     * @returns {undefined}
     */
    function setZsetValue(values){
        var score = [],
            value = [];
        // 拆分为两个数组
        for(var i = 0, len = values.length; i < len; i++){
            if (i % 2) {
                score.push(values[i]);
            } else {
                value.push(values[i]);
            }
        }
        var _table = '<thead><tr><th>Score</th-><th>Value</th></tr></thead><tbody>',
            _tr = '';
        for(var i = 0, len = score.length; i < len; i++){
            _tr += "<tr><td>" + score[i]+"</td><td>" + value[i]+"</td></tr>";
        }
        var _tabsle = _table +  _tr + '</tbody>';
        $(".key-detail").html(_tabsle);
    }
    
    /**
     * 生成表格 Type:hash
     * @param {array} values
     * @returns {undefined}
     */
    function setHash(values){
        var _table = '<thead><tr><th>Field</th><th>Value</th></tr></thead><tbody>',
            _tr = '';
        for(var key in values){
            _tr += "<tr><td>" + key +"</td><td>" + values[key] +"</td></tr>";
        }
        var _tabsle = _table +  _tr + '</tbody>';
        $(".key-detail").html(_tabsle);
    }
    
    /**
     * 生成表格 Type:set string list
     * @param {array} values
     * @returns {undefined}
     */
    function setDefault(values){
        var _table = '<thead><tr><th>Value</th></tr></thead><tbody>',
            _tr = '';
        for(var key in values){
            _tr += "<tr><td>" + values[key] +"</td></tr>";
        }
        var _tabsle = _table +  _tr + '</tbody>';
        $(".key-detail").html(_tabsle);
    }
</script>
