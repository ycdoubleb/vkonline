/**
 * 图像批量上传
 * @param {Window} win
 * @param {jQuery} $
 * @returns {undefined}
 */
(function (win, $) {
    //================================================================================================
    //
    // ImageData class
    //
    //================================================================================================
    /**
     * 图像信息模型
     * @param {int} id 
     * @param {array} data
     * @returns {image-batch-uploadL#7.ImageData}
     */
    var ImageData = function (id,data) {
        var _self = this;

        this.id = id;                                       //ID
        this.image_name = data['image.name'];               //图像名称
        this.image_tags = data['image.tags'];               //图像标签
        this.image_des = data['image.des'];                 //图像描述
        this.image_filename = data['image.filename'];       //图像文件名

        this.image_id = null;                               //图像ID,上传成功后设置
        this.file_id = null;                                //图像文件ID,校检后设置

        this.submit_status = 0;                             //提交状态 0/1/2 未提交/提交中/已提交
        this.submit_result = false;                         //提交结果 false/true 失败/成功
        this.submit_feedback = ''                           //提交反馈

        this.errors = {};                                   //错误 key:mes
       
        this.setTags(this.image_tags);
    };
    
    /**
     * 设置文件
     * @param {array} files
     * @param {bool} manual     手动设置，如用户从下拉选择后设置
     * @returns {void}
     */
    ImageData.prototype.setFile = function (files, manual) {
        var _self = this;
        files = files || [];
        manual = !!manual;
        //手动或者未设置情况
        if (manual || !_self.file_id) {
            if (files.length > 0) {
                //存在多个同名图像文件
                if (files.length > 1 && !_self.file_id) {
                    _self.errors['file_id'] = '存在多个同名图像文件！【' + _self.image_filename + ' 】';
                } else {
                    _self.file_id = files[0].id;
                    delete _self.errors['file_id'];
                }
            } else if (!_self.file_id) {
                // _self.file_id = null;
                _self.errors['file_id'] = "找不到【" + _self.image_filename + "】" + ',图像文件不能为空！';
            }
        }
        this.sentChangeEvent();
    };

    /**
     * 设置标签
     * @param {string} tags 
     * @returns {void}
     */
    ImageData.prototype.setTags = function (tags) {
        var _self = this;
        tags = tags || '';
        tags = tags.replace(/、|，/g, ',');  //替换全角 “、”,“，” 为 半角英文“,”;
        var arr = $.grep(tags.split(','), function (x) { return $.trim(x).length > 0; });   //删除空值
        _self.image_tags = arr.join();
        //标签个数少于5个
        
        if (arr.length < 5) {
            _self.errors['image_tags'] = '标签至少5个！';
        }else{
            delete _self.errors['image_tags'];
        }
        this.sentChangeEvent();
    };

    /**
     * 发送更改事件
     * @returns {undefined}
     */
    ImageData.prototype.sentChangeEvent = function(){
        $(this).trigger('change');
    };

    /**
     * 获取错误汇总
     * @returns {string}
     */
    ImageData.prototype.getErrorSummary = function () {
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
    ImageData.prototype.validate = function(){
        return this.getErrorSummary() == "";
    };
    
    /**
     * 获取上传所需要格式
     * @returns {Object}
     */
    ImageData.prototype.getPostData = function(){
        return {
            /* 图像基本信息 */
            Image : {
                name : this.image_name,
                des : this.image_des,
            },
            /* 标签信息 */
            image_tags : this.image_tags,
            /* 图像文件 */
            image_file : this.file_id
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
    ImageData.prototype.setSubmitResult = function(status,result,feedback,dbdata){
        this.submit_status = status;
        this.submit_result = result;
        this.submit_feedback = feedback;
        this.image_id = result ? dbdata.id : null;
        this.sentChangeEvent();
    };




    //================================================================================================
    //
    // ImageBatchUpload class
    //
    //================================================================================================
    /**
     * 图像批量导入控制器
     * @param {type} config
     * @returns {video-batch-uploadL#7.ImageBatchUpload}
     */
    function ImageBatchUpload(config) {
        this.config = $.extend({
            add_image_url : '/build_course/image-import/add-image',  //添加图像             
            submit_force: false,                            //已提交的强制提交
            submit_common_params: {},                    //提交公共参数，如_scrf，catgory_id
            
            imageinfo: '.image-info',                       //图像信息容器
            imagefile: '.image-file'                        //图像文件容器
        }, config);
        //dom
        this.imageinfo = $(this.config['imageinfo']);
        this.imagefile = $(this.config['imagefile']);
        //model
        this.images = [];           //图像信息数据
        this.files = [];            //图像文件数据
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
    ImageBatchUpload.prototype.__prepareData = function () {
        
    };

    /**
     * 校检图像数据
     * @param {ImageData} item
     * @returns {void}
     */
    ImageBatchUpload.prototype.__verificationImage = function (imageData) {
        
    };
    
    //------------------------------------------------------
    // 视图 创建/更新
    //------------------------------------------------------
    /**
     * 创建图像列表
     * @returns {void}
     */
    ImageBatchUpload.prototype.__createImageList = function(){
        var _self = this;
        $table = this.imageinfo.find('table');
        $.each(_self.images,function(index,imageData){
            $image_tr = _self.imageinfo.find('tr[data-vid='+imageData.id+']');
            $(Wskeee.StringUtil.renderDOM(_self.config['image_data_tr_dom'], imageData)).appendTo($table);
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
    ImageBatchUpload.prototype.__createTagsDom = function(){
        var _self = this;
        this.imageinfo.find('input[data-role=tagsinput]').tagsinput();  //创建标签组件
        this.imageinfo.find('input[data-role=tagsinput]').on('change',function(){
            var $tr = $(this).parents('tr');                            //找到父级 tr 
            var vd = _self.__getImagedataById($tr.attr('data-vid'));    //通过data-vid 找到videodata
            vd.setTags($(this).val());                                  //手动更新
        });
    };
    
    /**
     * 创建图像文件下拉组件
     * @returns {void}
     */
    ImageBatchUpload.prototype.__createFileDom = function(){
        var _self = this;
        var reg = /^http:\/\//;
        var format = function(data) {
            return data.id ? Wskeee.StringUtil.renderDOM(_self.config['file_select_dom'], data) : data.text;
        }
        //已经成功的不用刷新
        this.imageinfo.find('.file-select:not([disabled])').html('<option></option>');
        var select2 = this.imageinfo.find('.file-select:not([disabled])').select2({
            placeholder: "请选择对应图像",
            data:_self.files,
            width:'100%',
            dropdownParent:_self.imageinfo,
            templateResult: format,
            templateSelection: format,
            escapeMarkup: function(m) { return m; }
        });
        
        /* 侦听更改事件，更新选择的文件 */
        select2.on('select2:select', function (e) {
            var $tr = $(this).parents('tr');                            //找到父级 tr 
            var vd = _self.__getImagedataById($tr.attr('data-vid'));    //通过data-vid 找到videodata
            vd.setFile([{id: $(this).val()}], true);                    //手动更新
        });
    }
    
    /**
     * 刷新整个表
     * @returns {void}
     */
    ImageBatchUpload.prototype.__reflash = function () {
        var _self = this;
        $.each(_self.images,function(index,imageData){
            _self.__delayReflashImageItem(imageData);
        });
    };

    /**
     * 更新/创建单行显示
     * @param {ImageData} imageData 
     * @returns {void}
     */
    ImageBatchUpload.prototype.__reflashImageItem = function (imageData) {
        clearTimeout(delayReflashImageItemIDs[imageData.id]);
        
        $image_tr = this.imageinfo.find('tr[data-vid='+imageData.id+']');
        //更新下拉显示
        if(imageData.submit_status == 2 && imageData.submit_result){
            $image_tr.find('.file-select').prop("disabled", 'disabled');
            $image_tr.find('input[data-role=tagsinput]').prop("disabled", 'disabled');
        }else{
            $image_tr.find('.file-select').val(imageData.file_id).trigger("change");
        }
        //删除所有错误
        $image_tr.find('td .c-box').removeClass('border-error');
        /* 显示提示 */
        $.each(imageData.errors, function (key, mes) {
            $image_tr.find('td[data-id='+key+'] .c-box').addClass("border-error").popover({
                trigger : 'hover',
                placement : 'auto top',
                content : mes
            });
        });
        //销毁没用提示
        $image_tr.find('td .c-box:not(.border-error)').popover('hide').popover('destroy');
        
        /* 渲染状态/操作栏 */
        $image_tr.find('.btn').hide().removeClass('btn-danger');
        /* 状态按钮提示，每次更新选隐藏 */
        $image_tr.find('.btn').popover({
                    trigger : 'hover',
                    placement : 'auto top',
                    delay: { "hide": 1000 },
                    html : true,
                    content : function(){
                        return imageData.submit_status ? imageData.submit_feedback : imageData.getErrorSummary();
                    }
                });
        
        if(imageData.submit_status){
            if(imageData.submit_status == 1){
                //提交中
                $image_tr.find('.info-mes').html('提交中...').removeClass('error success');
                $image_tr.find('.btn').show().html($('<i class="loading"></i>'));
            }else if(imageData.submit_result){
                //提交成功
                $image_tr.find('.info-mes').html('成功').removeClass('error').addClass('success');
                $image_tr.find('.btn').show().attr('data-clipboard-text', imageData.image_id).html('复制ID');
                this.__addCopyAct('.btn[data-clipboard-text]');
            }else{
                //提交失败
                $image_tr.find('.info-mes').html('失败').removeClass('success').addClass('error');
                $image_tr.find('.btn').show().html('详情').addClass('btn-danger');
            }
        }else if(imageData.validate()){
            //未提交，验证通过
            $image_tr.find('.info-mes').html('待提交').removeClass('error success');
        }else{
            //未提交，验证未通过
            $image_tr.find('.info-mes').html('验证未通过').removeClass('success').addClass('error');
            $image_tr.find('.btn').show().html('详情').addClass('btn-danger');
        }
    };
    
    /**
     * 延迟刷新单行
     * @param {ImageData} imageData
     * @returns {void}
     */
    var delayReflashImageItemIDs = {};
    ImageBatchUpload.prototype.__delayReflashImageItem = function(imageData){
        var _self = this;
        clearTimeout(delayReflashImageItemIDs[imageData.id]);
        delayReflashImageItemIDs[imageData.id] = setTimeout(function(){
            _self.__reflashImageItem(imageData);
        },100);
    }
    
    //------------------------------------------------------
    // 提交数据
    //------------------------------------------------------
    /**
     * 提交下一个任务
     * @returns {void}
     */
    ImageBatchUpload.prototype.__submitNext = function () {
        var index = this.submit_index;
        if (index >= this.images.length - 1) {
            //完成
            this.is_submiting = false;
            $(this).trigger('submitFinished');
        } else {
            this.submit_index = ++index;
            this.__submitImageData(index, this.config['submit_force']);
        }
    }

    /**
     * 上传图像数据，创建图像
     * 
     * @param {int} index       需要上传的索引
     * @param {bool} force      已完成的是否需要强制提交 默认false
     * @returns {void}
     */
    ImageBatchUpload.prototype.__submitImageData = function (index, force) {
        force = !!force;
        var _self = this;
        var vd = this.images[index];
        if (!vd || (vd.submit_status == 2 && vd.submit_result)) {
            //找不到数据或者已经创建成功的 跳过
            this.__submitNext();
        } else {
            var postData = vd.getPostData();
            if (vd.validate()) {
                var submit_common_params = this.config['submit_common_params'];
                postData = $.extend(postData, submit_common_params);
                vd.setSubmitResult(1);  //设置提交中
                $.post(this.config['add_image_url'], postData, function (response) {
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

                    $(_self).trigger('submitCompleted', vd);         //发送单个图像上传完成
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
     * @param {array} images            图像信息数据
     * @param {array} files             图像文件数据
     * @returns {void}  
     */
    ImageBatchUpload.prototype.init = function (images, files) {
        images = images || [];
        this.files = files || [];
        
        var _self = this,
            imageData;
        //array to ImageData
        $.each(images, function (index, image) {
            imageData = new ImageData(index + 1, image);
            /* 侦听属性更改事件 */
            $(imageData).on('change',function(){
                _self.__delayReflashImageItem(this);
            });
            _self.images.push(imageData);
        });
        
        this.__createImageList();       //创建列表
        this.setFiles(this.files);
        //this.__reflash();               //刷新
    };
    
    /**
     * 设置图像文件数据
     * @param {array} files
     * @returns {void}
     */
    ImageBatchUpload.prototype.setFiles = function (files) {
        var _self = this;
        var fileIds = [];
        var fileNameMap = {};
        this.files = files;
        this.__createFileDom();
        
        $.each(this.files,function(){
            fileIds.push(this.id);
        });
        $.each(this.images, function(){
            if(!fileNameMap[this.image_filename]){
                fileNameMap[this.image_filename] = 1;
            }else{
                fileNameMap[this.image_filename]++;
            }
        });
        console.log(fileNameMap);
        $.each(this.images,function(){
            /* 不在文件列表里将设置为null */
            if(!this.submit_result && $.inArray(this.file_id,fileIds) == -1){
                this.file_id = null;
            }
            /* ImageData */
            this.setFile(fileNameMap[this.image_filename] > 1 ? [0, 0] : _self.__getFileByName(this.image_filename));
        });
    };
   
    /**
     * 提交数据，已经提交的不再提交
     * @param {object} submit_common_params     设置上传公共参数
     * @param {boole} force                     强制提交默认为false
     * @returns {void}
     */
    ImageBatchUpload.prototype.submit = function(submit_common_params, force){
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
     * 查找同名图像文件
     * @param {string} name
     * @returns {array}
     */
    ImageBatchUpload.prototype.__getFileByName = function (name){
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
     * 通过ID查找ImageDdata
     * @param {string} id
     * @returns {ImageData}
     */
    ImageBatchUpload.prototype.__getImagedataById = function (id) {
        var target = null;
        $.each(this.images, function (index, imagedata) {
            if (id == imagedata.id) {
                target = imagedata;
            }
        });
        return target;
    };
    
    /**
     * 点击复制图像id
     * @param {obj} target   目标对象  
     */
    ImageBatchUpload.prototype.__addCopyAct = function (target){ 
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
    win.youxueba.ImageBatchUpload = ImageBatchUpload;

})(window, jQuery);