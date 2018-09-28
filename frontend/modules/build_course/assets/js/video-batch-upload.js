/**
 * 视频批量上传
 * @param {Window} win
 * @param {jQuery} $
 * @returns {undefined}
 */
(function (win, $) {
    //================================================================================================
    //
    // VideoData class
    //
    //================================================================================================
    /**
     * 视频信息模型
     * @param {int} id 
     * @param {array} data
     * @returns {video-batch-uploadL#7.VideoData}
     */
    var VideoData = function (id,data) {
        var _self = this;

        this.id = id;                                       //ID
        this.video_name = data['video.name'];               //视频名称
        this.video_tags = data['video.tags'];               //视频标签
        this.video_des = data['video.des'];                 //视频描述
        this.video_filename = data['video.filename'];       //视频文件名
        this.teacher_name = data['teacher.name'];           //老师名称

        this.video_id = null;                               //视频ID,上传成功后设置
        this.teacher_id = null;                             //老师ID,校检后设置
        this.file_id = null;                                //视频文件ID,校检后设置

        this.submit_status = 0;                             //提交状态 0/1/2 未提交/提交中/已提交
        this.submit_result = false;                         //提交结果 false/true 失败/成功
        this.submit_feedback = ''                           //提交反馈

        this.errors = {};                                   //错误 key:mes
        
        this.setTags(this.video_tags);
    };
    
    /**
     * 设置老师
     * @param {array} teachers
     * @param {bool} manual     手动设置，如用户从下拉选择后设置
     * @returns {void}
     */
    VideoData.prototype.setTeacher = function (teachers,manual) {
        var _self = this;
        teachers = teachers || [];
        manual = !!manual;
         //手动或者未设置情况
        if (manual || !_self.file_id) {
            if (teachers.length > 0) {
                //存在多个同名老师
                if (teachers.length > 1 && !_self.teacher_id) {
                    _self.errors['teacher_id'] = '存在多个同名老师！【' + _self.teacher_name + '】';
                } else {
                    _self.teacher_id = teachers[0].id;
                    delete _self.errors['teacher_id'];
                }
            } else if(!_self.teacher_id){
                //_self.teacher_id = null;
                _self.errors['teacher_id'] = "找不到【" + _self.teacher_name + "】" + ',老师不能为空！';
            }
        }
        
        this.sentChangeEvent();
    };

    /**
     * 设置文件
     * @param {array} files
     * @param {bool} manual     手动设置，如用户从下拉选择后设置
     * @returns {void}
     */
    VideoData.prototype.setFile = function (files, manual) {
        var _self = this;
        files = files || [];
        manual = !!manual;
        //手动或者未设置情况
        if (manual || !_self.file_id) {
            if (files.length > 0) {
                //存在多个同名老师
                if (files.length > 1 && !_self.file_id) {
                    _self.errors['file_id'] = '存在多个同名视频文件！【' + _self.video_filename + ' 】';
                } else {
                    _self.file_id = files[0].id;
                    delete _self.errors['file_id'];
                }
            } else if (!_self.file_id) {
                // _self.file_id = null;
                _self.errors['file_id'] = "找不到【" + _self.video_filename + "】" + ',视频文件不能为空！';
            }
        }
        this.sentChangeEvent();
    };

    /**
     * 设置标签
     * @param {string} tags 
     * @returns {void}
     */
    VideoData.prototype.setTags = function (tags) {
        var _self = this;
        tags = tags || '';
        tags = tags.replace(/、|，/g, ',');  //替换全角 “、”,“，” 为 半角英文“,”;
        var arr = $.grep(tags.split(','), function (x) { return $.trim(x).length > 0; });   //删除空值
        _self.video_tags = arr.join();
        //标签个数少于5个
        
        if (arr.length < 5) {
            _self.errors['video_tags'] = '标签至少5个！';
        }else{
            delete _self.errors['video_tags'];
        }
        this.sentChangeEvent();
    };

    /**
     * 发送更改事件
     * @returns {undefined}
     */
    VideoData.prototype.sentChangeEvent = function(){
        $(this).trigger('change');
    };

    /**
     * 获取错误汇总
     * @returns {string}
     */
    VideoData.prototype.getErrorSummary = function () {
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
    VideoData.prototype.validate = function(){
        return this.getErrorSummary() == "";
    };
    
    /**
     * 获取上传所需要格式
     * @returns {Object}
     */
    VideoData.prototype.getPostData = function(){
        return {
            /* 视频基本信息 */
            Video : {
                name : this.video_name,
                des : this.video_des,
                teacher_id : this.teacher_id
            },
            /* 标签信息 */
            video_tags : this.video_tags,
            /* 视频文件 */
            video_file : this.file_id
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
    VideoData.prototype.setSubmitResult = function(status,result,feedback,dbdata){
        this.submit_status = status;
        this.submit_result = result;
        this.submit_feedback = feedback;
        this.video_id = result ? dbdata.id : null;
        this.sentChangeEvent();
    };




    //================================================================================================
    //
    // VideoBatchUpload class
    //
    //================================================================================================
    /**
     * 视频批量导入控制器
     * @param {type} config
     * @returns {video-batch-uploadL#7.VideoBatchUpload}
     */
    function VideoBatchUpload(config) {
        this.config = $.extend({
            add_video_url : '/build_course/video-import/add-video',  //添加视频             
            submit_force: false,                            //已提交的强制提交
            submit_common_params: {},                    //提交公共参数，如_scrf，wartermaker，catgory_id
            
            videoinfo: '.video-info',                       //视频信息容器
            videofile: '.video-file'                        //视频文件容器
        }, config);
        //dom
        this.videoinfo = $(this.config['videoinfo']);
        this.videofile = $(this.config['videofile']);
        //model
        this.videos = [];           //视频信息数据
        this.teachers = [];         //老师数据
        this.files = [];            //视频文件数据
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
    VideoBatchUpload.prototype.__prepareData = function () {
        
    };

    /**
     * 校检视频数据
     * @param {VideoData} item
     * @returns {void}
     */
    VideoBatchUpload.prototype.__verificationVideo = function (videoData) {
        
    };
    
    //------------------------------------------------------
    // 视图 创建/更新
    //------------------------------------------------------
    /**
     * 创建视频列表
     * @returns {void}
     */
    VideoBatchUpload.prototype.__createVideoList = function(){
        var _self = this;
        $table = this.videoinfo.find('table');
        $.each(_self.videos,function(index,videoData){
            $video_tr = _self.videoinfo.find('tr[data-vid='+videoData.id+']');
            $(Wskeee.StringUtil.renderDOM(_self.config['video_data_tr_dom'], videoData)).appendTo($table);
        });
        //添加提示组件
        //this.__createTeacherDom();
        this.__createTagsDom();
        //this.__createFileDom();
    }
   
    /**
     * 创建老师下拉组件
     * @returns {void}
     */
    VideoBatchUpload.prototype.__createTeacherDom = function(){
        var _self = this;
        /* 老师下拉 */
        var format = function(data) {
            if(data.id){
                data.style = data.is_certificate == 1 ? '' : 'display:none';
            }
            return data.id ? Wskeee.StringUtil.renderDOM(_self.config['teacher_select_dom'], data) : data.text;
        };
        //已经成功的不用刷新
        this.videoinfo.find('.teacher-select:not([disabled])').html('<option></option>');
        var select2 = this.videoinfo.find('.teacher-select:not([disabled])').select2({
            placeholder: "请选择对应老师",
            data:_self.teachers,
            width:'100%',
            dropdownParent:_self.videoinfo,
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function(m) { return m; }
        });
        
        /* 侦听更改事件，更新选择的老师 */
        select2.on('select2:select', function (e) {
            var $tr = $(this).parents('tr');                            //找到父级 tr 
            var vd = _self.__getVideodataById($tr.attr('data-vid'));    //通过data-vid 找到videodata
            vd.setTeacher([{id: $(this).val()}],true);                  //手动更新
        });
    };
    
    /**
     * 创建标签组件
     * @returns {void}
     */
    VideoBatchUpload.prototype.__createTagsDom = function(){
        var _self = this;
        this.videoinfo.find('input[data-role=tagsinput]').tagsinput();  //创建标签组件
        this.videoinfo.find('input[data-role=tagsinput]').on('change',function(){
            var $tr = $(this).parents('tr');                            //找到父级 tr 
            var vd = _self.__getVideodataById($tr.attr('data-vid'));    //通过data-vid 找到videodata
            vd.setTags($(this).val());                                  //手动更新
        });
    };
    
    /**
     * 创建视频文件下拉组件
     * @returns {void}
     */
    VideoBatchUpload.prototype.__createFileDom = function(){
        var _self = this;
        var reg = /^http:\/\//;
        var format = function(data) {
            return data.id ? Wskeee.StringUtil.renderDOM(_self.config['file_select_dom'], data) : data.text;
        }
        //已经成功的不用刷新
        this.videoinfo.find('.file-select:not([disabled])').html('<option></option>');
        var select2 = this.videoinfo.find('.file-select:not([disabled])').select2({
            placeholder: "请选择对应视频",
            data:_self.files,
            width:'100%',
            dropdownParent:_self.videoinfo,
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function(m) { return m; }
        });
        
        /* 侦听更改事件，更新选择的老师 */
        select2.on('select2:select', function (e) {
            var $tr = $(this).parents('tr');                            //找到父级 tr 
            var vd = _self.__getVideodataById($tr.attr('data-vid'));    //通过data-vid 找到videodata
            vd.setFile([{id: $(this).val()}], true);                    //手动更新
        });
    }
    
    /**
     * 刷新整个表
     * @returns {void}
     */
    VideoBatchUpload.prototype.__reflash = function () {
        var _self = this;
        $.each(_self.videos,function(index,videoData){
            _self.__delayReflashVideoItem(videoData);
        });
    };

    /**
     * 更新/创建单行显示
     * @param {VideoData} videoData 
     * @returns {void}
     */
    VideoBatchUpload.prototype.__reflashVideoItem = function (videoData) {
        clearTimeout(delayReflashVideoItemIDs[videoData.id]);
        
        $video_tr = this.videoinfo.find('tr[data-vid='+videoData.id+']');
        //更新下拉显示
        if(videoData.submit_status == 2 && videoData.submit_result){
            $video_tr.find('.teacher-select').prop("disabled", 'disabled');
            $video_tr.find('.file-select').prop("disabled", 'disabled');
            $video_tr.find('input[data-role=tagsinput]').prop("disabled", 'disabled');
        }else{
            $video_tr.find('.teacher-select').val(videoData.teacher_id).trigger("change");
            $video_tr.find('.file-select').val(videoData.file_id).trigger("change");
        }
        //删除所有错误
        $video_tr.find('td .c-box').removeClass('border-error');
        /* 显示提示 */
        $.each(videoData.errors, function (key, mes) {
            $video_tr.find('td[data-id='+key+'] .c-box').addClass("border-error").popover({
                trigger : 'hover',
                placement : 'auto top',
                content : mes
            });
        });
        //销毁没用提示
        $video_tr.find('td .c-box:not(.border-error)').popover('hide').popover('destroy');
        
        /* 渲染状态/操作栏 */
        $video_tr.find('.btn').hide().removeClass('btn-danger');
        /* 状态按钮提示，每次更新选隐藏 */
        $video_tr.find('.btn').popover({
                    trigger : 'hover',
                    placement : 'auto top',
                    delay: { "hide": 1000 },
                    html : true,
                    content : function(){
                        return videoData.submit_status ? videoData.submit_feedback : videoData.getErrorSummary();
                    }
                });
        
        if(videoData.submit_status){
            if(videoData.submit_status == 1){
                //提交中
                $video_tr.find('.info-mes').html('提交中...').removeClass('error success');
                $video_tr.find('.btn').show().html($('<i class="loading"></i>'));
            }else if(videoData.submit_result){
                //提交成功
                $video_tr.find('.info-mes').html('成功').removeClass('error').addClass('success');
                $video_tr.find('.btn').show().attr('data-clipboard-text', videoData.video_id).html('复制ID');
                this.__addCopyAct('.btn[data-clipboard-text]');
            }else{
                //提交失败
                $video_tr.find('.info-mes').html('失败').removeClass('success').addClass('error');
                $video_tr.find('.btn').show().html('详情').addClass('btn-danger');
            }
        }else if(videoData.validate()){
            //未提交，验证通过
            $video_tr.find('.info-mes').html('待提交').removeClass('error success');
        }else{
            //未提交，验证未通过
            $video_tr.find('.info-mes').html('验证未通过').removeClass('success').addClass('error');
            $video_tr.find('.btn').show().html('详情').addClass('btn-danger');
        }
    };
    
    /**
     * 延迟刷新单行
     * @param {VideoData} videoData
     * @returns {void}
     */
    var delayReflashVideoItemIDs = {};
    VideoBatchUpload.prototype.__delayReflashVideoItem = function(videoData){
        var _self = this;
        clearTimeout(delayReflashVideoItemIDs[videoData.id]);
        delayReflashVideoItemIDs[videoData.id] = setTimeout(function(){
            _self.__reflashVideoItem(videoData);
        },100);
    }
    
    //------------------------------------------------------
    // 提交数据
    //------------------------------------------------------
    /**
     * 提交下一个任务
     * @returns {void}
     */
    VideoBatchUpload.prototype.__submitNext = function () {
        var index = this.submit_index;
        if (index >= this.videos.length - 1) {
            //完成
            this.is_submiting = false;
            $(this).trigger('submitFinished');
        } else {
            this.submit_index = ++index;
            this.__submitVideoData(index, this.config['submit_force']);
        }
    }

    /**
     * 上传视频数据，创建视频
     * 
     * @param {int} index       需要上传的索引
     * @param {bool} force      已完成的是否需要强制提交 默认false
     * @returns {void}
     */
    VideoBatchUpload.prototype.__submitVideoData = function (index, force) {
        force = !!force;
        var _self = this;
        var vd = this.videos[index];
        if (!vd || (vd.submit_status == 2 && vd.submit_result)) {
            //找不到数据或者已经创建成功的 跳过
            this.__submitNext();
        } else {
            var postData = vd.getPostData();
            if (vd.validate()) {
                var submit_common_params = this.config['submit_common_params'];
                postData = $.extend(postData, submit_common_params);
                vd.setSubmitResult(1);  //设置提交中
                $.post(this.config['add_video_url'], postData, function (response) {
                    try{
                        var feedback = "";
                        if (response.data.code == 'FILE_REPEAT') {
                            feedback = Wskeee.StringUtil.renderDOM(_self.config['video_use_more_dom'], response.data.data);   //显示视频多次使用提示
                        } else {
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

                    $(_self).trigger('submitCompleted', vd);         //发送单个视频上传完成
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
     * @param {array} videos            视频信息数据
     * @param {array} teachers          老师数据
     * @param {array} files             视频文件数据
     * @returns {void}  
     */
    VideoBatchUpload.prototype.init = function (videos, teachers, files) {
        videos = videos || [];
        this.teachers = teachers || [];
        this.files = files || [];

        var _self = this,
            videoData;
        //array to VideoData
        $.each(videos, function (index, video) {
            videoData = new VideoData(index + 1, video);
            /* 侦听属性更改事件 */
            $(videoData).on('change',function(){
                _self.__delayReflashVideoItem(this);
            });
            _self.videos.push(videoData);
        });

        this.__createVideoList();       //创建列表
        this.setTeachers(this.teachers);
        this.setFiles(this.files);
        //this.__reflash();               //刷新
    };
    
    /**
     * 设置老师数据
     * @param {array} teachers  
     * @returns {void}
     */
    VideoBatchUpload.prototype.setTeachers = function (teachers) {
        var _self = this;
        this.teachers = teachers || [];
        this.__createTeacherDom();
        $.each(this.videos,function(){
            /* VideoData */
            this.setTeacher(_self.__getTeacherByName(this.teacher_name));
        });
    };
    
    /**
     * 设置视频文件数据
     * @param {array} files
     * @returns {void}
     */
    VideoBatchUpload.prototype.setFiles = function (files) {
        var _self = this;
        var fileIds = [];
        this.files = files;
        this.__createFileDom();
        
        $.each(this.files,function(){
            fileIds.push(this.id);
        });
        $.each(this.videos,function(){
            /* 不在文件列表里将设置为null */
            if(!this.submit_result && $.inArray(this.file_id,fileIds) == -1){
                this.file_id = null;
            }
            /* VideoData */
            this.setFile(_self.__getFileByName(this.video_filename));
        });
    };
   
    /**
     * 提交数据，已经提交的不再提交
     * @param {object} submit_common_params     设置上传公共参数
     * @param {boole} force                     强制提交默认为false
     * @returns {void}
     */
    VideoBatchUpload.prototype.submit = function(submit_common_params, force){
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
     * 通过名称查找老师
     * @param {string} name
     * @returns {Array}
     */
    VideoBatchUpload.prototype.__getTeacherByName = function (name) {
        var arr = [];
        $.each(this.teachers, function (index, item) {
            if (name === item.name) {
                arr.push(item);
            }
        });
        return arr;
    };
    
    /**
     * 查找同名视频文件
     * @param {string} name
     * @returns {array}
     */
    VideoBatchUpload.prototype.__getFileByName = function (name){
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
     * 通过ID查找VideoDdata
     * @param {string} id
     * @returns {VideoData}
     */
    VideoBatchUpload.prototype.__getVideodataById = function (id) {
        var target = null;
        $.each(this.videos, function (index, videodata) {
            if (id == videodata.id) {
                target = videodata;
            }
        });
        return target;
    };
    
    /**
     * 点击复制视频id
     * @param {obj} target   目标对象  
     */
    VideoBatchUpload.prototype.__addCopyAct = function (target){ 
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
    win.youxueba.VideoBatchUpload = VideoBatchUpload;

})(window, jQuery);