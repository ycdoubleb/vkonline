(function (win, $) {
    /**
     * 创建DOM
     * @param {String} dom renderer = "<div>{%name%}</div><div>{%title%}</div>"
     * @param {Object} data {name:xxxx,title:xxxx}
     * @return Dom;
     */
    var Wskeee = win.Wskeee = win.Wskeee || {};
    var StringUtil = Wskeee.StringUtil || {};

    StringUtil.createDOM = function (renderer, data)
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
    Wskeee.StringUtil = StringUtil;
})(window, jQuery);



(function (win, $) {

    var Wskeee = win.Wskeee = win.Wskeee || {};
    var StringUtil = Wskeee.StringUtil;
    /* 容器 */
    var ROOT_CONTAINER_DOM = '<div class="euploader-root">'
                                + '<div class="euploader-btns">'
                                    + '<div id="picker">选择文件</div>'
                                        + '<a id="euploader-ctl-btn" class="btn btn-default euploader-ctl-btn">开始上传</a>'
                                + '</div>'
                                + '<div class="euploader-list-container">'
                                    + '<table id="euploader-list" class="table table-striped euploader-list"><tbody></tbody></table>'
                                + '</div>'
                            + '</div>';

    /* 行DOM模板 */
    var TR_ITEM_DOM = '<tr id="{%id%}" class="{%status%}">'
                        + '<td class="euploader-item-input"><input name="files[]" type="hidden" value="{%value%}" {%disabled%}/></td>'
                        + '<td class="euploader-item-status"><span>{%statusIcon%}</span></td>'
                        + '<td class="euploader-item-filename"><span>{%name%}</span></td>'
                        + '<td class="euploader-item-state">{%state%}</td>'
                        + '<td class="euploader-item-size">{%size%}</td>'
                        + '<td class="euploader-item-btns"><a class="btn btn-sm btn-danger euploader-del-btn" data-file="{%id%}"><i class="glyphicon glyphicon-trash"></i></a></td>'
                    + '</tr>';
            
    /* 状态提示信息DOM模板 */
    var STATE_DOM = '<span>【{%text%}】</span>';
    /* 进度条DOM模板 */
    var PROGRESS_BAR_DOM = '<div class="progress" style="margin-bottom: 0px;">'
                            + '<div class="progress-bar" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">'
                                + '{%value%}'
                            + '</div>'
                        + '</div>';

    /* 等待图标 */
    var STATUS_WAITING_ICON = '<i class="glyphicon glyphicon-time"></i>';
    /* 上传中图标 */
    var STATUS_UPLOADING_ICON = '<i class="glyphicon glyphicon-upload"></i>';
    /* 成功图标 */
    var STATUS_SUCCEE_ICON = '<i class="glyphicon glyphicon-ok"></i>';
    /* 失败图标 */
    var STATUS_FAIL_ICON = '<i class="glyphicon glyphicon-remove"></i>';
    
    var console = window.console || {
        log:function(){},
        error:function(){}
    };
    
    /**
     * @param {Object} config
     *      【必须】config.swf              上传flash组件路径
     *      【必须】config.server           上传分片路径
     *      【必须】config.checkFile        检查文件是否存在路径
     *      【必须】config.mergeChunks      分片合并路径
     *      【必须】config.container        上传容器
     *      【必须】config.formData         每次上传都传过到服务器的数据
     *             formData._csrf               csrf验证
     *             formData.app_path            指定appPath
     *      
     *      【可选】config.resize           不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
     *      【可选】config.chunked          分片 ture/false
     *      【可选】config.auto             自动上传，选择完文件自动上传
     *      【可选】config.sendAsBinary     是否使用二进制上传
     *      
     * @returns {euploader_L1.Uploader}
     */
    var Uploader = function (config) {
        
        var _self = this;
        /* 全局配置 */
        this.config = $.extend({
            name:'euploader-'+Math.round(Math.random()*100000000),
            swf: '/Uploader.swf',
            // 文件接收服务端。
            server: '/site/upload',
            //检查文件是否存在
            checkFile:'/site/check-file',
            //分片合并
            mergeChunks:'/site/merge-chunks',
            // 选择文件的按钮。可选。
            // 上传容器
            container: '#euploader-container',
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '#picker',
            //列表
            list: '#euploader-list tbody',
            //控制按钮
            ctlBtn:'#euploader-ctl-btn',
            // 不压缩image, 默认如果是jpeg，文件上传前会压缩一把再上传！
            resize: false,
            //分片
            chunked: true,
            auto: false,
            sendAsBinary: true,
            formData: {
                _csrf:'',       //csrf验证
                app_path:''    //指定appPath
            }
        }, config);
        /* 是否完成 */
        this.isFinish = true;
        this.hasError = false;
        //上传错误的文件
        this.errorFiles = {};

        /* 根目录 */
        var $container = $(this.config['container']);
        //添加 DOM 内容
        $(ROOT_CONTAINER_DOM).appendTo($container);
        /* 列表 */
        var $list = $container.find(this.config['list']);
        /* 控制按钮 */
        var $ctlBtn = $container.find(this.config['ctlBtn']);
        
        /*************************************************************************************
         * method:before-send
         * 在分片发送之前request，可以用来做分片验证，如果此分片已经上传成功了，可返回一个rejected promise来跳过此分片上传
         * para:block: 分片对象
         *************************************************************************************/
        WebUploader.Uploader.register({
            "name":this.config['name'],//删除名称，删除
            //"add-file": "addFile", //整个文件上传前
            "before-send-file": "beforeSendFile", //整个文件上传前
            "before-send": "beforeSend", //每个分片上传前
            "after-send-file": "afterSendFile", //分片上传完毕
        }, {
            //时间点1：所有分块进行上传之前调用此函数    
            beforeSendFile: function (file) {
                var deferred = WebUploader.Deferred();
                var owner = this.owner;
                //1、计算文件的唯一标记fileMd5，用于断点续传  如果.md5File(file)方法里只写一个file参数则计算MD5值会很慢 所以加了后面的参数：10*1024*1024  
                var thenFun = function(val){
                    $('#' + file.id).find('td.euploader-item-input input').attr('value',val);
                    $('#' + file.id).find('td.euploader-item-state').html(StringUtil.createDOM(STATE_DOM, {text:'成功获取文件信息...'}));
                    if(!file.fileMd5){
                        file.fileMd5 = val;
                    }
                    //获取文件信息后进入下一步
                    $.ajax({
                        type: "POST",
                        url: _self.config['checkFile'], //ajax验证每一个分片  
                        data: {
                            fileMd5: val, //文件唯一标记    
                            _csrf:_self.config['formData._csrf'],
                        },
                        cache: false,
                        async: false, // 与js同步  
                        timeout: 1000, //todo 超时的话，只能认为该分片未上传过  
                        dataType: "json",
                        success: function (response) {
                            if (response.exist) {
                                //分块存在，跳过   
                                file.dbFile = response.result;
                                deferred.reject('已存在!');
                            } else {
                                //分块不存在或不完整，重新发送该分块内容   
                                file.chunkMd5s = response.result;
                                deferred.resolve();
                            }
                        },
                        fail: function (data) {
                            deferred.reject();
                        },
                        error: function (data) {
                            deferred.reject();
                        }
                    });
                    //deferred.resolve();
                }
                //如果已经计算过md5就跳过计算，直接验证
                if(file.fileMd5 != null){
                    setTimeout(function(){
                        thenFun(file.fileMd5);
                    },50);
                }else{
                    owner.md5File(file).progress(function (percentage) {
                        $('#' + file.id).find('td.euploader-item-state').html(StringUtil.createDOM(STATE_DOM, {text: '正在读取文件信息...' + Math.floor(percentage * 100) + '%'}));
                    }).then(thenFun);
                }
                return deferred.promise();
            },
            //时间点2：如果有分块上传，则每个分块上传之前调用此函数    
            beforeSend: function (block) {
                var me = this;
                var owner = this.owner;
                var deferred = WebUploader.Deferred();
                var chunkFile = block.blob;
                var file = block.file;
                var chunk = block.chunk;
                var chunks = block.chunks;
                var start = block.start;
                var end = block.end;
                var total = block.total;
                file.chunks = chunks;
                if (chunks > 1) { //文件大于chunksize 分片上传
                    owner.md5File(chunkFile)
                            .progress(function (percentage) {
                                //分片MD5计算可以不知道计算进度
                            })
                            .then(function (chunkMd5) {
                                //设定自定义参数
                                block.formData = {
                                    fileMd5 : file.fileMd5,
                                    chunkMd5 : chunkMd5,
                                    chunk: chunk,
                                    chunks: chunks
                                };
                                var chunkMd5s = file.chunkMd5s;
                                var exists = false;
                                if (chunkMd5s == null) {
                                    exists = false;
                                } else {
                                    exists = chunkMd5s[chunkMd5] != null ? true : false;
                                }

                                if (exists) {
                                    deferred.reject();
                                } else {
                                    deferred.resolve();
                                }
                            });
                } else {//未分片文件上传
                    //设定自定义参数
                    block.formData = {fileMd5:file.fileMd5,chunkMd5:file.fileMd5};
                    deferred.resolve();
                }
                return deferred.promise();
            },
            //时间点3：所有分块上传成功后调用此函数    
            afterSendFile: function (file) {
                //如果分块上传成功，则通知后台合并分块  
                var owner = this.owner;
                var deferred = WebUploader.Deferred();
                $.ajax({
                    type: "POST",
                    url: _self.config['mergeChunks'], //ajax将所有片段合并成整体 
                    data: $.extend(owner.options.formData,{name: file.name, size: file.size, fileMd5: file.fileMd5}),
                    cache: false,
                    async: false, // 与js同步  
                    timeout: 1000, //todo 超时的话，只能认为该分片未上传过  
                    dataType: "json",
                    success: function (data) {
                        if (data.error == null) {
                            file.dbFile = data.result;
                            deferred.resolve();
                        } else {
                            console.error(data);
                            deferred.reject();
                        }
                    },
                    fail: function (data) {
                        deferred.reject();
                    },
                    error: function (data) {
                        deferred.reject();
                    }
                });
                return deferred.promise();
            }
        });
        
        /*************************************************************************************
         *
         * 创建 WebUploader
         *
         *************************************************************************************/
        var uploader = this.uploader = WebUploader.create(this.config);
        // 当有文件被添加进队列的时候
        uploader.on('fileQueued', function (file) {
            $list.append(StringUtil.createDOM(TR_ITEM_DOM, {
                id: file.id,
                dbID: file.fileMd5,
                status: 'euploader-item-waiting',
                name: file.name,
                statusIcon: STATUS_WAITING_ICON,
                state: StringUtil.createDOM(STATE_DOM, {text: '等待上传'}),
                disabled:'disabled',
                value:'',
                size:WebUploader.Base.formatSize(file.size)
            }));
            // 删除文件
            $('#'+file.id).find('.euploader-del-btn').on('click', function () {
                uploader.removeFile($(this).attr('data-file'),true);
            });
            _self.isFinish = false;
        });
        // 当有文件被除出队列
        uploader.on('fileDequeued', function (file) {
            $('#'+file.id).remove();
            delete _self.errorFiles[file.id];
        });
        // 当某个文件的分块在发送前触发，主要用来询问是否要添加附带参数，大文件在开起分片上传的前提下此事件可能会触发多次。
        uploader.on('uploadBeforeSend', function (obj,data) {
            data = $.extend(data,obj.formData);
        });
        // 文件上传过程中创建进度条实时显示。
        uploader.on('uploadProgress', function (file, percentage) {
            var $li = $('#' + file.id),
                $percent = $li.find('.progress .progress-bar');
            // 避免重复创建
            if (!$percent.length) {
                $percent = $li.find('td.euploader-item-state').html($(StringUtil.createDOM(PROGRESS_BAR_DOM, {value: 0}))).find('.progress-bar');
            }
            //设置状态为上传
            $li.attr('class', 'euploader-item-uploading');
            $li.find('td.euploader-item-status').html(STATUS_UPLOADING_ICON);
            $percent.css('width', percentage * 100 + '%');
            $percent.html(Math.floor(percentage * 100)+'%');
        });
        uploader.on('uploadSuccess', function (file, response) {
            $('#' + file.id).attr('class', 'euploader-item-succeed');
            $('#' + file.id).find('td.euploader-item-status').html(STATUS_SUCCEE_ICON);
            $('#' + file.id).find('td.euploader-item-state').html(StringUtil.createDOM(STATE_DOM, {text: '已上传'}));
            $('#' + file.id).find('td.euploader-item-input input').removeAttr('disabled');
        });
        uploader.on('uploadError', function (file, reason) {
            var isExist = reason == '已存在!';
            if(!isExist){
                _self.errorFiles[file.id] = true;
            }else{
                $('#' + file.id).find('td.euploader-item-input input').removeAttr('disabled');
            }
            $('#' + file.id).attr('class', isExist ? 'euploader-item-exist' : 'euploader-item-fail');
            $('#' + file.id).find('td.euploader-item-status').html(isExist ? STATUS_SUCCEE_ICON :  STATUS_FAIL_ICON);
            $('#' + file.id).find('td.euploader-item-state').html(StringUtil.createDOM(STATE_DOM, {text: reason ? reason : '上传出错'}));
        });
        uploader.on('uploadComplete', function (file) {
            //console.log(file);
        });
        uploader.on('uploadFinished', function (file) {
            //console.log('uploadComplete');
            $ctlBtn.html('开始上传');
            _self.isFinish = true;
            _self.hasError = false;
            $.each(_self.errorFiles,function(i, item){ 
               _self.hasError = true;
            });
            if(_self.hasError){
                $ctlBtn.html('重新上传')
            }
        });
        $ctlBtn.on('click', function () {
            var text = $ctlBtn.html();
            if(text == '开始上传' || text == '继续上传'){
                uploader.upload();
                $ctlBtn.html('暂停上传');
            }else if(text == '暂停上传'){
                uploader.stop();
                $ctlBtn.html('继续上传');
            }else if(text == '重新上传'){
                uploader.retry();
                $ctlBtn.html('暂停上传');
            }
        });
        
        /*************************************************************************************
         *
         * publich method
         *
         *************************************************************************************/
        /**
         * 
         * @param {array} files
         *      id      文件id
         *      name    名称
         *      path    路径
         *      size    大小
         * @returns {void}
         */
        this.addCompleteFiles = function(files){
            var file;
            for(var i=0,len=files.length;i<len;i++){
                file = files[i];
                $list.append(StringUtil.createDOM(TR_ITEM_DOM, {
                    id: file.id,
                    dbID: file.id,
                    status: 'euploader-item-waiting',
                    name: file.name,
                    statusIcon: STATUS_SUCCEE_ICON,
                    state: StringUtil.createDOM(STATE_DOM, {text: '已上传'}),
                    disabled: '',
                    value: file.id,
                    size:WebUploader.Base.formatSize(file.size)
                }));
                // 删除文件
                $('#'+file.id).find('.euploader-del-btn').on('click', function () {
                    $('#'+$(this).attr('data-file')).remove();
                });
            }
        }
        
        /**
         * 销毁对象
         * @returns {void}
         */
        this.destroy = function(){
            uploader.destroy();
            WebUploader.Uploader.unRegister(this.config['name']);
            //console.log('销毁 '+this.config['name']);
        }
    };
    
    Wskeee.Uploader = Uploader;
})(window, jQuery);
