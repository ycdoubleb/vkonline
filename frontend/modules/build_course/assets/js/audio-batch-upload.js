/**
 * 音频批量上传
 * @param {Window} win
 * @param {jQuery} $
 * @returns {undefined}
 */
(function (win, $) {
    //================================================================================================
    //
    // AudioData class
    //
    //================================================================================================
    /**
     * 音频信息模型
     * @param {int} id 
     * @param {array} data
     * @returns {audio-batch-uploadL#7.AudioData}
     */
    var AudioData = function (id,data) {
        var _self = this;

        this.id = id;                                       //ID
        this.audio_name = data['audio.name'];               //音频名称
        this.audio_tags = data['audio.tags'];               //音频标签
        this.audio_des = data['audio.des'];                 //音频描述
        this.audio_filename = data['audio.filename'];       //音频文件名

        this.audio_id = null;                               //音频ID,上传成功后设置
        this.file_id = null;                                //音频文件ID,校检后设置

        this.submit_status = 0;                             //提交状态 0/1/2 未提交/提交中/已提交
        this.submit_result = false;                         //提交结果 false/true 失败/成功
        this.submit_feedback = ''                           //提交反馈

        this.errors = {};                                   //错误 key:mes
       
        this.setTags(this.audio_tags);
    };
    
    /**
     * 设置文件
     * @param {array} files
     * @param {bool} manual     手动设置，如用户从下拉选择后设置
     * @returns {void}
     */
    AudioData.prototype.setFile = function (files, manual) {
        var _self = this;
        files = files || [];
        manual = !!manual;
        //手动或者未设置情况
        if (manual || !_self.file_id) {
            if (files.length > 0) {
                //存在多个同名音频文件
                if (files.length > 1 && !_self.file_id) {
                    _self.errors['file_id'] = '存在多个同名音频文件！【' + _self.audio_filename + ' 】';
                } else {
                    _self.file_id = files[0].id;
                    delete _self.errors['file_id'];
                }
            } else if (!_self.file_id) {
                // _self.file_id = null;
                _self.errors['file_id'] = "找不到【" + _self.audio_filename + "】" + ',音频文件不能为空！';
            }
        }
        this.sentChangeEvent();
    };

    /**
     * 设置标签
     * @param {string} tags 
     * @returns {void}
     */
    AudioData.prototype.setTags = function (tags) {
        var _self = this;
        tags = tags || '';
        tags = tags.replace(/、|，/g, ',');  //替换全角 “、”,“，” 为 半角英文“,”;
        var arr = $.grep(tags.split(','), function (x) { return $.trim(x).length > 0; });   //删除空值
        _self.audio_tags = arr.join();
        //标签个数少于5个
        
        if (arr.length < 5) {
            _self.errors['audio_tags'] = '标签至少5个！';
        }else{
            delete _self.errors['audio_tags'];
        }
        this.sentChangeEvent();
    };

    /**
     * 发送更改事件
     * @returns {undefined}
     */
    AudioData.prototype.sentChangeEvent = function(){
        $(this).trigger('change');
    };

    /**
     * 获取错误汇总
     * @returns {string}
     */
    AudioData.prototype.getErrorSummary = function () {
        var _self = this;
        var errors = [];
        $.each(_self.errors, function (key, value) {
            errors.push(value);
        });
        return errors.join('\n');
    };
    
    /**
     * 验证所有必须属性
     * @returns {Boolean}
     */
    AudioData.prototype.validate = function(){
        return this.getErrorSummary() == "";
    };
    
    /**
     * 获取上传所需要格式
     * @returns {Object}
     */
    AudioData.prototype.getPostData = function(){
        return {
            /* 音频基本信息 */
            Audio : {
                name : this.audio_name,
                des : this.audio_des,
            },
            /* 标签信息 */
            audio_tags : this.audio_tags,
            /* 音频文件 */
            audio_file : this.file_id
        };
    };
    
    /**
     * 设置提交结果
     * @param {int} status
     * @param {bool} result
     * @param {string} feedback
     * @param {object} dbdata
     * @returns {void}
     */
    AudioData.prototype.setSubmitResult = function(status,result,feedback,dbdata){
        this.submit_status = status;
        this.submit_result = result;
        this.submit_feedback = feedback;
        this.audio_id = result ? dbdata.id : null;
        this.sentChangeEvent();
    };




    //================================================================================================
    //
    // AudioBatchUpload class
    //
    //================================================================================================
    /**
     * 音频批量导入控制器
     * @param {type} config
     * @returns {audio-batch-uploadL#7.AudioBatchUpload}
     */
    function AudioBatchUpload(config) {
        this.config = $.extend({
            add_audio_url : '/build_course/audio-import/add-audio',  //添加音频             
            submit_force: false,                            //已提交的强制提交
            submit_common_params: {},                    //提交公共参数，如_scrf，catgory_id
            
            audioinfo: '.audio-info',                       //音频信息容器
            audiofile: '.audio-file'                        //音频文件容器
        }, config);
        //dom
        this.audioinfo = $(this.config['audioinfo']);
        this.audiofile = $(this.config['audiofile']);
        //model
        this.audios = [];           //音频信息数据
        this.files = [];            //音频文件数据
        //vars
        this.is_submiting = false;  //是否提交中
        this.submit_index = -1;     //当前提交索引
        this.clipboard;             //剪贴板
        
    }

    //--------------------------------------------------------------------------
    //
    // private
    //
    //--------------------------------------------------------------------------
    /**
     * 准备/校检数据
     * @returns {void}
     */
    AudioBatchUpload.prototype.__prepareData = function () {
        
    };

    /**
     * 校检音频数据
     * @param {AudioData} item
     * @returns {void}
     */
    AudioBatchUpload.prototype.__verificationAudio = function (audioData) {
        
    };
    
    //------------------------------------------------------
    // 视图 创建/更新
    //------------------------------------------------------
    /**
     * 创建音频列表
     * @returns {void}
     */
    AudioBatchUpload.prototype.__createAudioList = function(){
        var _self = this;
        $table = this.audioinfo.find('table');
        $.each(_self.audios,function(index,audioData){
            $audio_tr = _self.audioinfo.find('tr[data-vid='+audioData.id+']');
            $(Wskeee.StringUtil.renderDOM(_self.config['audio_data_tr_dom'], audioData)).appendTo($table);
        });
        //添加提示组件
        //this.__createTeacherDom();
        this.__createTagsDom();
        //this.__createFileDom();
    }
       
    /**
     * 创建标签组件
     * @returns {void}
     */
    AudioBatchUpload.prototype.__createTagsDom = function(){
        var _self = this;
        this.audioinfo.find('input[data-role=tagsinput]').tagsinput();  //创建标签组件
        this.audioinfo.find('input[data-role=tagsinput]').on('change',function(){
            var $tr = $(this).parents('tr');                            //找到父级 tr 
            var vd = _self.__getAudiodataById($tr.attr('data-vid'));    //通过data-vid 找到audiodata
            vd.setTags($(this).val());                                  //手动更新
        });
    };
    
    /**
     * 创建音频文件下拉组件
     * @returns {void}
     */
    AudioBatchUpload.prototype.__createFileDom = function(){
        var _self = this;
        var reg = /^http:\/\//;
        var format = function(data) {
            return data.id ? Wskeee.StringUtil.renderDOM(_self.config['file_select_dom'], data) : data.text;
        }
        //已经成功的不用刷新
        this.audioinfo.find('.file-select:not([disabled])').html('<option></option>');
        var select2 = this.audioinfo.find('.file-select:not([disabled])').select2({
            placeholder: "请选择对应音频",
            data:_self.files,
            width:'100%',
            dropdownParent:_self.audioinfo,
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function(m) { return m; }
        });
        
        /* 侦听更改事件，更新选择的文件 */
        select2.on('select2:select', function (e) {
            var $tr = $(this).parents('tr');                            //找到父级 tr 
            var vd = _self.__getAudiodataById($tr.attr('data-vid'));    //通过data-vid 找到audiodata
            vd.setFile([{id: $(this).val()}], true);                    //手动更新
        });
    }
    
    /**
     * 刷新整个表
     * @returns {void}
     */
    AudioBatchUpload.prototype.__reflash = function () {
        var _self = this;
        $.each(_self.audios,function(index,audioData){
            _self.__delayReflashAudioItem(audioData);
        });
    };

    /**
     * 更新/创建单行显示
     * @param {AudioData} audioData 
     * @returns {void}
     */
    AudioBatchUpload.prototype.__reflashAudioItem = function (audioData) {
        clearTimeout(delayReflashAudioItemIDs[audioData.id]);
        
        $audio_tr = this.audioinfo.find('tr[data-vid='+audioData.id+']');
        //更新下拉显示
        if(audioData.submit_status == 2 && audioData.submit_result){
            $audio_tr.find('.file-select').prop("disabled", 'disabled');
            $audio_tr.find('input[data-role=tagsinput]').prop("disabled", 'disabled');
        }else{
            $audio_tr.find('.file-select').val(audioData.file_id).trigger("change");
        }
        //删除所有错误
        $audio_tr.find('td .c-box').removeClass('border-error');
        /* 显示提示 */
        $.each(audioData.errors, function (key, mes) {
            $audio_tr.find('td[data-id='+key+'] .c-box').addClass("border-error").popover({
                trigger : 'hover',
                placement : 'auto top',
                content : mes
            });
        });
        //销毁没用提示
        $audio_tr.find('td .c-box:not(.border-error)').popover('hide').popover('destroy');
        
        /* 渲染状态/操作栏 */
        $audio_tr.find('.btn').hide().removeClass('btn-danger');
        /* 状态按钮提示，每次更新选隐藏 */
        $audio_tr.find('.btn').popover({
                    trigger : 'hover',
                    placement : 'auto top',
                    delay: { "hide": 1000 },
                    html : true,
                    content : function(){
                        return audioData.submit_status ? audioData.submit_feedback : audioData.getErrorSummary();
                    }
                });
        
        if(audioData.submit_status){
            if(audioData.submit_status == 1){
                //提交中
                $audio_tr.find('.info-mes').html('提交中...').removeClass('error success');
                $audio_tr.find('.btn').show().html($('<i class="loading"></i>'));
            }else if(audioData.submit_result){
                //提交成功
                $audio_tr.find('.info-mes').html('成功').removeClass('error').addClass('success');
                $audio_tr.find('.btn').show().attr('data-clipboard-text', audioData.audio_id).html('复制ID');
                this.__addCopyAct('.btn[data-clipboard-text]');
            }else{
                //提交失败
                $audio_tr.find('.info-mes').html('失败').removeClass('success').addClass('error');
                $audio_tr.find('.btn').show().html('详情').addClass('btn-danger');
            }
        }else if(audioData.validate()){
            //未提交，验证通过
            $audio_tr.find('.info-mes').html('待提交').removeClass('error success');
        }else{
            //未提交，验证未通过
            $audio_tr.find('.info-mes').html('验证未通过').removeClass('success').addClass('error');
            $audio_tr.find('.btn').show().html('详情').addClass('btn-danger');
        }
    };
    
    /**
     * 延迟刷新单行
     * @param {AudioData} audioData
     * @returns {void}
     */
    var delayReflashAudioItemIDs = {};
    AudioBatchUpload.prototype.__delayReflashAudioItem = function(audioData){
        var _self = this;
        clearTimeout(delayReflashAudioItemIDs[audioData.id]);
        delayReflashAudioItemIDs[audioData.id] = setTimeout(function(){
            _self.__reflashAudioItem(audioData);
        },100);
    }
    
    //------------------------------------------------------
    // 提交数据
    //------------------------------------------------------
    /**
     * 提交下一个任务
     * @returns {void}
     */
    AudioBatchUpload.prototype.__submitNext = function () {
        var index = this.submit_index;
        if (index >= this.audios.length - 1) {
            //完成
            this.is_submiting = false;
            $(this).trigger('submitFinished');
        } else {
            this.submit_index = ++index;
            this.__submitAudioData(index, this.config['submit_force']);
        }
    }

    /**
     * 上传音频数据，创建音频
     * 
     * @param {int} index       需要上传的索引
     * @param {bool} force      已完成的是否需要强制提交 默认false
     * @returns {void}
     */
    AudioBatchUpload.prototype.__submitAudioData = function (index, force) {
        force = !!force;
        var _self = this;
        var vd = this.audios[index];
        if (!vd || (vd.submit_status == 2 && vd.submit_result)) {
            //找不到数据或者已经创建成功的 跳过
            this.__submitNext();
        } else {
            var postData = vd.getPostData();
            if (vd.validate()) {
                var submit_common_params = this.config['submit_common_params'];
                postData = $.extend(postData, submit_common_params);
                vd.setSubmitResult(1);  //设置提交中
                $.post(this.config['add_audio_url'], postData, function (response) {
                    try{
                        var feedback = "";
                        if (response.data.code !== '0') {
                            feedback = response.data.msg;     //其它错误显示
                        }
                        //code 不为0即为失败
                        vd.setSubmitResult(2, response.data.code == "0", feedback , response.data.data);
                    }catch(e){
                        if(console){
                             console.error(e);
                        }
                        vd.setSubmitResult(2, false,  '未知错误');
                    }

                    $(_self).trigger('submitCompleted', vd);         //发送单个音频上传完成
                    _self.__submitNext();
                });
            } else {
                this.__submitNext();
            }
        }
    };
    
    
    

    //--------------------------------------------------------------------------
    //
    // public
    //
    //--------------------------------------------------------------------------
    /**
     * 初始上传组件，准备所有数据，也可以后面再补其它数据
     * @param {array} audios            音频信息数据
     * @param {array} files             音频文件数据
     * @returns {void}  
     */
    AudioBatchUpload.prototype.init = function (audios, files) {
        audios = audios || [];
        this.files = files || [];
        
        var _self = this,
            audioData;
        //array to AudioData
        $.each(audios, function (index, audio) {
            audioData = new AudioData(index + 1, audio);
            /* 侦听属性更改事件 */
            $(audioData).on('change',function(){
                _self.__delayReflashAudioItem(this);
            });
            _self.audios.push(audioData);
        });
        
        this.__createAudioList();       //创建列表
        this.setFiles(this.files);
        //this.__reflash();               //刷新
    };
    
    /**
     * 设置音频文件数据
     * @param {array} files
     * @returns {void}
     */
    AudioBatchUpload.prototype.setFiles = function (files) {
        var _self = this;
        var fileIds = [];
        var fileNameMap = {};
        this.files = files;
        this.__createFileDom();
        
        $.each(this.files,function(){
            fileIds.push(this.id);
        });
        $.each(this.audios, function(){
            if(!fileNameMap[this.audio_filename]){
                fileNameMap[this.audio_filename] = 1;
            }else{
                fileNameMap[this.audio_filename]++;
            }
        });
        $.each(this.audios,function(){
            /* 不在文件列表里将设置为null */
            if(!this.submit_result && $.inArray(this.file_id,fileIds) == -1){
                this.file_id = null;
            }
            /* AudioData */
            this.setFile(fileNameMap[this.audio_filename] > 1 ? [0, 0] : _self.__getFileByName(this.audio_filename));
        });
    };
   
    /**
     * 提交数据，已经提交的不再提交
     * @param {object} submit_common_params     设置上传公共参数
     * @param {boole} force                     强制提交默认为false
     * @returns {void}
     */
    AudioBatchUpload.prototype.submit = function(submit_common_params, force){
        force = !!force;
        this.submit_index = -1;
        this.config['submit_common_params'] = $.extend(this.config['submit_common_params'],submit_common_params);
        this.config['submit_force'] = force;
        this.__submitNext();
    };

    //--------------------------------------------------------------------------
    //
    // utils
    //
    //--------------------------------------------------------------------------
    
    /**
     * 查找同名音频文件
     * @param {string} name
     * @returns {array}
     */
    AudioBatchUpload.prototype.__getFileByName = function (name){
        var arr = [];
        $.each(this.files, function (index, item) {
            //考虑外链情况 使用text匹配，非外链 name = text
            if (name === item.text) {
                arr.push(item);
            }
        });
        return arr;
    };
    
    /**
     * 通过ID查找AudioDdata
     * @param {string} id
     * @returns {AudioData}
     */
    AudioBatchUpload.prototype.__getAudiodataById = function (id) {
        var target = null;
        $.each(this.audios, function (index, audiodata) {
            if (id == audiodata.id) {
                target = audiodata;
            }
        });
        return target;
    };
    
    /**
     * 点击复制音频id
     * @param {obj} target   目标对象  
     */
    AudioBatchUpload.prototype.__addCopyAct = function (target){ 
        if (this.clipboard) {
            this.clipboard.destroy();
        }
        this.clipboard = new ClipboardJS(target);
        this.clipboard.on('success', function (e) {
            $.notify({message: '复制成功',}, {type: "success",});
        });
        this.clipboard.on('error', function (e) {
            $.notify({message: '复制失败',}, {type: "danger",});
        });              
    }

    //--------------------------------------------------------------------------
    //
    // get & set
    //
    //--------------------------------------------------------------------------

    

    win.youxueba = win.youxueba || {};
    win.youxueba.AudioBatchUpload = AudioBatchUpload;

})(window, jQuery);