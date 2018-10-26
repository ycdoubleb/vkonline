/**
 * 视频批量上传
 * @param {Window} win
 * @param {jQuery} $
 * @returns {undefined}
 */
(function (win, $) {
    //================================================================================================
    //
    // DocumentData class
    //
    //================================================================================================
    /**
     * 文档信息模型
     * @param {int} id 
     * @param {array} data
     * @returns {document-batch-uploadL#7.DocumentData}
     */
    var DocumentData = function (id,data) {
        var _self = this;

        this.id = id;                                       //ID
        this.document_name = data['document.name'];               //文档名称
        this.document_tags = data['document.tags'];               //文档标签
        this.document_des = data['document.des'];                 //文档描述
        this.document_filename = data['document.filename'];       //文档文件名

        this.document_id = null;                               //文档ID,上传成功后设置
        this.file_id = null;                                //视频文件ID,校检后设置

        this.submit_status = 0;                             //提交状态 0/1/2 未提交/提交中/已提交
        this.submit_result = false;                         //提交结果 false/true 失败/成功
        this.submit_feedback = ''                           //提交反馈

        this.errors = {};                                   //错误 key:mes
       
        this.setTags(this.document_tags);
    };
    
    /**
     * 设置文件
     * @param {array} files
     * @param {bool} manual     手动设置，如用户从下拉选择后设置
     * @returns {void}
     */
    DocumentData.prototype.setFile = function (files, manual) {
        var _self = this;
        files = files || [];
        manual = !!manual;
        //手动或者未设置情况
        if (manual || !_self.file_id) {
            if (files.length > 0) {
                //存在多个同名文档文件
                if (files.length > 1 && !_self.file_id) {
                    _self.errors['file_id'] = '存在多个同名文档文件！【' + _self.document_filename + ' 】';
                } else {
                    _self.file_id = files[0].id;
                    delete _self.errors['file_id'];
                }
            } else if (!_self.file_id) {
                // _self.file_id = null;
                _self.errors['file_id'] = "找不到【" + _self.document_filename + "】" + ',文档文件不能为空！';
            }
        }
        this.sentChangeEvent();
    };

    /**
     * 设置标签
     * @param {string} tags 
     * @returns {void}
     */
    DocumentData.prototype.setTags = function (tags) {
        var _self = this;
        tags = tags || '';
        tags = tags.replace(/、|，/g, ',');  //替换全角 “、”,“，” 为 半角英文“,”;
        var arr = $.grep(tags.split(','), function (x) { return $.trim(x).length > 0; });   //删除空值
        _self.document_tags = arr.join();
        //标签个数少于5个
        
        if (arr.length < 5) {
            _self.errors['document_tags'] = '标签至少5个！';
        }else{
            delete _self.errors['document_tags'];
        }
        this.sentChangeEvent();
    };

    /**
     * 发送更改事件
     * @returns {undefined}
     */
    DocumentData.prototype.sentChangeEvent = function(){
        $(this).trigger('change');
    };

    /**
     * 获取错误汇总
     * @returns {string}
     */
    DocumentData.prototype.getErrorSummary = function () {
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
    DocumentData.prototype.validate = function(){
        return this.getErrorSummary() == "";
    };
    
    /**
     * 获取上传所需要格式
     * @returns {Object}
     */
    DocumentData.prototype.getPostData = function(){
        return {
            /* 文档基本信息 */
            Document : {
                name : this.document_name,
                des : this.document_des,
            },
            /* 标签信息 */
            document_tags : this.document_tags,
            /* 文档文件 */
            document_file : this.file_id
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
    DocumentData.prototype.setSubmitResult = function(status,result,feedback,dbdata){
        this.submit_status = status;
        this.submit_result = result;
        this.submit_feedback = feedback;
        this.document_id = result ? dbdata.id : null;
        this.sentChangeEvent();
    };




    //================================================================================================
    //
    // DocumentBatchUpload class
    //
    //================================================================================================
    /**
     * 视频批量导入控制器
     * @param {type} config
     * @returns {video-batch-uploadL#7.DocumentBatchUpload}
     */
    function DocumentBatchUpload(config) {
        this.config = $.extend({
            add_document_url : '/build_course/document-import/add-document',  //添加文档             
            submit_force: false,                            //已提交的强制提交
            submit_common_params: {},                    //提交公共参数，如_scrf，catgory_id
            
            documentinfo: '.document-info',                       //文档信息容器
            documentfile: '.document-file'                        //文档文件容器
        }, config);
        //dom
        this.documentinfo = $(this.config['documentinfo']);
        this.documentfile = $(this.config['documentfile']);
        //model
        this.documents = [];           //文档信息数据
        this.files = [];            //文档文件数据
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
    DocumentBatchUpload.prototype.__prepareData = function () {
        
    };

    /**
     * 校检文档数据
     * @param {DocumentData} item
     * @returns {void}
     */
    DocumentBatchUpload.prototype.__verificationDocument = function (documentData) {
        
    };
    
    //------------------------------------------------------
    // 视图 创建/更新
    //------------------------------------------------------
    /**
     * 创建文档列表
     * @returns {void}
     */
    DocumentBatchUpload.prototype.__createDocumentList = function(){
        var _self = this;
        $table = this.documentinfo.find('table');
        $.each(_self.documents,function(index,documentData){
            $document_tr = _self.documentinfo.find('tr[data-vid='+documentData.id+']');
            $(Wskeee.StringUtil.renderDOM(_self.config['document_data_tr_dom'], documentData)).appendTo($table);
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
    DocumentBatchUpload.prototype.__createTagsDom = function(){
        var _self = this;
        this.documentinfo.find('input[data-role=tagsinput]').tagsinput();  //创建标签组件
        this.documentinfo.find('input[data-role=tagsinput]').on('change',function(){
            var $tr = $(this).parents('tr');                            //找到父级 tr 
            var vd = _self.__getDocumentdataById($tr.attr('data-vid'));    //通过data-vid 找到videodata
            vd.setTags($(this).val());                                  //手动更新
        });
    };
    
    /**
     * 创建文档文件下拉组件
     * @returns {void}
     */
    DocumentBatchUpload.prototype.__createFileDom = function(){
        var _self = this;
        var reg = /^http:\/\//;
        var format = function(data) {
            return data.id ? Wskeee.StringUtil.renderDOM(_self.config['file_select_dom'], data) : data.text;
        }
        //已经成功的不用刷新
        this.documentinfo.find('.file-select:not([disabled])').html('<option></option>');
        var select2 = this.documentinfo.find('.file-select:not([disabled])').select2({
            placeholder: "请选择对应视频",
            data:_self.files,
            width:'100%',
            dropdownParent:_self.documentinfo,
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function(m) { return m; }
        });
        
        /* 侦听更改事件，更新选择的文件 */
        select2.on('select2:select', function (e) {
            var $tr = $(this).parents('tr');                            //找到父级 tr 
            var vd = _self.__getDocumentdataById($tr.attr('data-vid'));    //通过data-vid 找到videodata
            vd.setFile([{id: $(this).val()}], true);                    //手动更新
        });
    }
    
    /**
     * 刷新整个表
     * @returns {void}
     */
    DocumentBatchUpload.prototype.__reflash = function () {
        var _self = this;
        $.each(_self.documents,function(index,documentData){
            _self.__delayReflashDocumentItem(documentData);
        });
    };

    /**
     * 更新/创建单行显示
     * @param {DocumentData} documentData 
     * @returns {void}
     */
    DocumentBatchUpload.prototype.__reflashDocumentItem = function (documentData) {
        clearTimeout(delayReflashDocumentItemIDs[documentData.id]);
        
        $document_tr = this.documentinfo.find('tr[data-vid='+documentData.id+']');
        //更新下拉显示
        if(documentData.submit_status == 2 && documentData.submit_result){
            $document_tr.find('.file-select').prop("disabled", 'disabled');
            $document_tr.find('input[data-role=tagsinput]').prop("disabled", 'disabled');
        }else{
            $document_tr.find('.file-select').val(documentData.file_id).trigger("change");
        }
        //删除所有错误
        $document_tr.find('td .c-box').removeClass('border-error');
        /* 显示提示 */
        $.each(documentData.errors, function (key, mes) {
            $document_tr.find('td[data-id='+key+'] .c-box').addClass("border-error").popover({
                trigger : 'hover',
                placement : 'auto top',
                content : mes
            });
        });
        //销毁没用提示
        $document_tr.find('td .c-box:not(.border-error)').popover('hide').popover('destroy');
        
        /* 渲染状态/操作栏 */
        $document_tr.find('.btn').hide().removeClass('btn-danger');
        /* 状态按钮提示，每次更新选隐藏 */
        $document_tr.find('.btn').popover({
                    trigger : 'hover',
                    placement : 'auto top',
                    delay: { "hide": 1000 },
                    html : true,
                    content : function(){
                        return documentData.submit_status ? documentData.submit_feedback : documentData.getErrorSummary();
                    }
                });
        
        if(documentData.submit_status){
            if(documentData.submit_status == 1){
                //提交中
                $document_tr.find('.info-mes').html('提交中...').removeClass('error success');
                $document_tr.find('.btn').show().html($('<i class="loading"></i>'));
            }else if(documentData.submit_result){
                //提交成功
                $document_tr.find('.info-mes').html('成功').removeClass('error').addClass('success');
                $document_tr.find('.btn').show().attr('data-clipboard-text', documentData.document_id).html('复制ID');
                this.__addCopyAct('.btn[data-clipboard-text]');
            }else{
                //提交失败
                $document_tr.find('.info-mes').html('失败').removeClass('success').addClass('error');
                $document_tr.find('.btn').show().html('详情').addClass('btn-danger');
            }
        }else if(documentData.validate()){
            //未提交，验证通过
            $document_tr.find('.info-mes').html('待提交').removeClass('error success');
        }else{
            //未提交，验证未通过
            $document_tr.find('.info-mes').html('验证未通过').removeClass('success').addClass('error');
            $document_tr.find('.btn').show().html('详情').addClass('btn-danger');
        }
    };
    
    /**
     * 延迟刷新单行
     * @param {DocumentData} documentData
     * @returns {void}
     */
    var delayReflashDocumentItemIDs = {};
    DocumentBatchUpload.prototype.__delayReflashDocumentItem = function(documentData){
        var _self = this;
        clearTimeout(delayReflashDocumentItemIDs[documentData.id]);
        delayReflashDocumentItemIDs[documentData.id] = setTimeout(function(){
            _self.__reflashDocumentItem(documentData);
        },100);
    }
    
    //------------------------------------------------------
    // 提交数据
    //------------------------------------------------------
    /**
     * 提交下一个任务
     * @returns {void}
     */
    DocumentBatchUpload.prototype.__submitNext = function () {
        var index = this.submit_index;
        if (index >= this.documents.length - 1) {
            //完成
            this.is_submiting = false;
            $(this).trigger('submitFinished');
        } else {
            this.submit_index = ++index;
            this.__submitDocumentData(index, this.config['submit_force']);
        }
    }

    /**
     * 上传视频数据，创建视频
     * 
     * @param {int} index       需要上传的索引
     * @param {bool} force      已完成的是否需要强制提交 默认false
     * @returns {void}
     */
    DocumentBatchUpload.prototype.__submitDocumentData = function (index, force) {
        force = !!force;
        var _self = this;
        var vd = this.documents[index];
        if (!vd || (vd.submit_status == 2 && vd.submit_result)) {
            //找不到数据或者已经创建成功的 跳过
            this.__submitNext();
        } else {
            var postData = vd.getPostData();
            if (vd.validate()) {
                var submit_common_params = this.config['submit_common_params'];
                postData = $.extend(postData, submit_common_params);
                vd.setSubmitResult(1);  //设置提交中
                $.post(this.config['add_document_url'], postData, function (response) {
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
     * @param {array} documents            文档信息数据
     * @param {array} files             文档文件数据
     * @returns {void}  
     */
    DocumentBatchUpload.prototype.init = function (documents, files) {
        documents = documents || [];
        this.files = files || [];
        
        var _self = this,
            documentData;
        //array to DocumentData
        $.each(documents, function (index, document) {
            documentData = new DocumentData(index + 1, document);
            /* 侦听属性更改事件 */
            $(documentData).on('change',function(){
                _self.__delayReflashDocumentItem(this);
            });
            _self.documents.push(documentData);
        });
        
        this.__createDocumentList();       //创建列表
        this.setFiles(this.files);
        //this.__reflash();               //刷新
    };
    
    /**
     * 设置文档文件数据
     * @param {array} files
     * @returns {void}
     */
    DocumentBatchUpload.prototype.setFiles = function (files) {
        var _self = this;
        var fileIds = [];
        this.files = files;
        this.__createFileDom();
        
        $.each(this.files,function(){
            fileIds.push(this.id);
        });
        $.each(this.documents,function(){
            /* 不在文件列表里将设置为null */
            if(!this.submit_result && $.inArray(this.file_id,fileIds) == -1){
                this.file_id = null;
            }
            /* DocumentData */
            this.setFile(_self.__getFileByName(this.document_filename));
        });
    };
   
    /**
     * 提交数据，已经提交的不再提交
     * @param {object} submit_common_params     设置上传公共参数
     * @param {boole} force                     强制提交默认为false
     * @returns {void}
     */
    DocumentBatchUpload.prototype.submit = function(submit_common_params, force){
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
     * 查找同名视频文件
     * @param {string} name
     * @returns {array}
     */
    DocumentBatchUpload.prototype.__getFileByName = function (name){
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
     * 通过ID查找DocumentDdata
     * @param {string} id
     * @returns {DocumentData}
     */
    DocumentBatchUpload.prototype.__getDocumentdataById = function (id) {
        var target = null;
        $.each(this.documents, function (index, documentdata) {
            if (id == documentdata.id) {
                target = documentdata;
            }
        });
        return target;
    };
    
    /**
     * 点击复制文档id
     * @param {obj} target   目标对象  
     */
    DocumentBatchUpload.prototype.__addCopyAct = function (target){ 
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
    win.youxueba.DocumentBatchUpload = DocumentBatchUpload;

})(window, jQuery);