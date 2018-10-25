<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\UserCategory;
use common\widgets\depdropdown\DepDropdown;
use common\widgets\tagsinput\TagsInputAsset;
use common\widgets\watermark\WatermarkAsset;
use common\widgets\webuploader\WebUploaderAsset;
use frontend\assets\ClipboardAssets;
use frontend\modules\build_course\assets\VideoImportAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
$this->title = Yii::t('app', '{Batch}{Import}{Video}', [
    'Batch' => Yii::t('app', 'Batch'),  'Import' => Yii::t('app', 'Import'),  'Video' => Yii::t('app', 'Video')
]);

GrowlAsset::register($this);
WatermarkAsset::register($this);
ClipboardAssets::register($this);
VideoImportAssets::register($this);
TagsInputAsset::register($this);

//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
$csrfToken = Yii::$app->request->csrfToken;
//加载 WATERMARK_DOM 模板
$watermark_dom = str_replace("\n", ' ', $this->render('____watermark_dom'));
$teacher_select_dom = str_replace("\n", ' ', $this->render('____teacher_select_dom'));
$file_select_dom = str_replace("\n", ' ', $this->render('____file_select_dom'));
$video_data_tr_dom = str_replace("\n", ' ', $this->render('____video_data_tr_dom'));
$video_use_more_dom = str_replace("\n", ' ', $this->render('____video_use_more_then_one'));

?>

<div class="video-import container">
    
    <div class="panel">
        <div class="panel-head"><?= $this->title ?></div>
        <div class="panel-body">
            <!--警告框-->
            <div class="alert alert-danger alert-dismissible" style="margin-bottom: 0px" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <p>1、务必先建师资<a href="/build_course/teacher/import" class="alert-link" target="_black">（导入师资）</a>再导入视频，否则会丢老师信息</p>
                <p>2、批量导入<?= Html::a('（表格模板下载）', Aliyun::absolutePath('static/doc/template/video_import_template.xlsx?rand='. rand(0, 9999)), ['class' => 'alert-link']) ?></p>
                <p>3、导入步骤：（1）选择存放目录 > （2）选择视频水印 >（3）上传视频信息 >（4）导入视频文件 > （5）提交</p>
            </div>
            
            <!-- 公共设置 -->
            <div class="setting">
                <div class="title">公共设置</div>
                <hr>
                <div class="setting-box">
                    <div class="setting-item">
                        <div class="title">存放目录：</div>
                        <div class="video-dir-box"><?=
                            DepDropdown::widget([
                                'name' => 'user_cat_id',
                                'value' => $user_cat_id,
                                'pluginOptions' => [
                                    'url' => Url::to('/build_course/user-category/search-children', false),
                                    'max_level' => 10,
                                    'onChangeEvent' => new JsExpression('function(value){  }')
                                ],
                                'items' => UserCategory::getSameLevelCats($model->user_cat_id, true, true),
                                'values' => $user_cat_id == 0 ? [] : array_values(array_filter(explode(',', UserCategory::getCatById($user_cat_id)->path))),
                                'itemOptions' => [
                                    'style' => 'width: 150px; display: inline-block;',
                                ],
                            ])
                            ?></div>
                    </div>
                    <div class="setting-item">
                        <div class="title">视频水印：</div>
                        <div class="watermark-box">
                            <!-- 水印 -->
                            <div id="video-mts_watermark_ids" class="watermark_ids">找不到水印</div>
                            <!-- 预览 -->
                            <div id="preview" class="preview"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- 视频信息 -->
            <hr/>
            <div class="video-info">
                <div class="title">上传视频信息
                    <!--文件上传-->
                    <div class="pull-right">
                        <?php $form = ActiveForm::begin([
                            'options'=>[
                                'id' => 'videoinfo-upload-form',
                                'class'=>'form-horizontal',
                                'enctype' => 'multipart/form-data',
                                'method' => 'post',
                            ],
                        ]); ?>

                        <div class="vk-uploader">
                            <div class="btn btn-pick">选择文件</div>
                            <div class="file-box">
                                <input type="file" id="importfile" name="importfile" class="file-input" accept=".xlsx,.xls,.xlm,.xlt,.xlc,.xml" onchange="uploadVideoInfo()">
                            </div>
                        </div>

                        <?php ActiveForm::end(); ?>
                    </div>
                </div>
                
                <!-- 结果列表 -->
                <table class="table table-bordered vk-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th style="width: 200px;">视频名称</th>
                            <th style="width: 180px;">老师</th>
                            <th style="width: 320px;">视频标签</th>
                            <th style="width: 300px;">视频文件</th>
                            <th style="width: 100px;">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
            
            <!-- 视频文件 -->
            <div class="video-file">
                <div class="title">上传视频文件</div>
                <div class="uploader-box">
                    <div id="uploader-container" class="clear-padding"></div>
                </div>
            </div>
        </div>
        <!-- 提交 -->
        <div class="panel-foot submit-box">
            <a id="video-submit" class="btn btn-highlight btn-flat">提交</a>
            <span id="submit-result"></span>
        </div>
    </div>
    
    <!-- 模态框 -->
    <div id="pop-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">预览视频</h4>
                </div>
                <div class="modal-body">
                    <video id="media-player" width="100%" height="100%" autoplay="true" controls="controls"></video>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
</div>

<script type="text/javascript">
    var php_swfpath = '<?= $swfpath ?>';
    //阿里云host 如：http://file.studying8.com
    var php_aliyun_host = '<?= Aliyun::getOssHost() ?>';
    //是否为导入中
    var php_isImport = <?= $isImport ? 1 : 0 ?>;
    //已选水印ID
    var php_watermark_ids = <?= json_encode(explode(',', $mts_watermark_ids)) ?>;
    //品牌水印数据
    var php_watermarks = <?= $watermarks ?>;
    //老师数据
    var php_teachers = <?= json_encode($teachers) ?>;
    //视频数据
    var php_videodatas = <?= json_encode($videos) ?>;
    //水印dom
    var php_watermark_dom = '<?= $watermark_dom ?>';
    //老师下拉dom
    var php_teacher_select_dom = '<?= $teacher_select_dom ?>';
    //视频文件下拉dom
    var php_file_select_dom = '<?= $file_select_dom ?>';
    //视频 tr dom
    var php_video_data_tr_dom = '<?= $video_data_tr_dom ?>';
    //同一视频多次使用提示 dom
    var php_video_use_more_dom = '<?= $video_use_more_dom ?>';
    //csrf key
    var php_csrf_param = "<?= Yii::$app->getRequest()->csrfParam ?>";
    //csrf value
    var php_csrf_value = "<?= Yii::$app->getRequest()->csrfToken ?>";
    
    //水印组件
    var watermark;
    //批量上传控制器
    var videoBatchUpload;
    //视频文件上传组件
    var uploader;
    //上传工具的视频
    var uploaderVideos = [];
    //外链视频
    var linkVideos = [];
    
    /**
     * html 加载完成后初始化所有组件
     * @returns {void}
     */
    window.onload = function(){
        initWatermark();        //初始水印
        initVideoInfo();        //初始视频信息
        initEuploader();        //初始视频文件上传
        initSubmit();           //初始提交
    }
    /************************************************************************************
     *
     * 水印
     *
     ************************************************************************************/
    /**
     * 初始化水印组件
     **/
    function initWatermark(){
        watermark = new youxueba.Watermark({container: '#preview'});
        if(!$.isEmptyObject(php_watermarks)){
            $('#video-mts_watermark_ids').empty();
        }
        $.each(php_watermarks, function(){
            var is_selected = php_isImport ? $.inArray(this.id, php_watermark_ids) !=-1 : this.is_selected;
            var $watermark_item = $(Wskeee.StringUtil.renderDOM(php_watermark_dom, this)).appendTo($('#video-mts_watermark_ids'));
            //显示默认选中
            $watermark_item.find('input[name="video_watermark"]').attr('name', 'video_watermarks[]').prop('checked', is_selected);
            //check 更改后通知preview显示更改
            $watermark_item.find('input').on('change',function(){
                checkedWatermark($(this));
            });
            //如果是默认选中，则在预览图上添加该选中的水印
            if(is_selected){
                watermark.addWatermark('vkcw' + this.id, this);
            }
        });
    }
 
    /**
     * 选中水印图
     * @param object _this
     */
    function checkedWatermark(_this){
        /* 判断用户是否有选中水印图，如果选中，则添加水印，否则删除水印 */
        if($(_this).is(":checked")){
            watermark.addWatermark('vkcw' + _this.val(), php_watermarks[_this.val()]);
        }else{
            watermark.removeWatermark('vkcw' + $(_this).val());
        }
    }
        
    /************************************************************************************
    *
    * 视频信息上传
    *
    ************************************************************************************/
    function initVideoInfo(){
        // 初始并且初始化指量上传控制器
        videoBatchUpload = new youxueba.VideoBatchUpload({
            teacher_select_dom : php_teacher_select_dom,
            file_select_dom : php_file_select_dom,
            video_data_tr_dom : php_video_data_tr_dom,
            video_use_more_dom : php_video_use_more_dom
        });
        videoBatchUpload.init( php_videodatas, php_teachers );
        
        /**
         * 上传完成
         */
        $(videoBatchUpload).on('submitFinished',function(){
            var max_num = this.videos.length;
            var completed_num = 0;
            $.each(this.videos,function(){
                if(this.submit_result){
                    completed_num++;
                }
            });
            $('#submit-result').html("共有 "+max_num+" 个视频需要上传，其中 "+completed_num+" 个成功， "+(max_num - completed_num)+" 个失败！");
            $.notify({message: '提交完成'}, {type: "success"});
        });
        
        
        /* 弹出视频模态框 */
        $('#pop-modal').on('shown.bs.modal',function(){
            $('#media-player').get(0).play();
        });
        $('#pop-modal').on('hide.bs.modal',function(){
            $('#media-player').get(0).pause();
        });
    }
    /* 提交表数据 */
    function uploadVideoInfo(){
        var cps = getSubmitCommonParams();
        var cps_str = "user_cat_id="+cps['user_cat_id']+"&mts_watermark_ids="+cps['mts_watermark_ids'];
        //添加传参数
        $('#videoinfo-upload-form').attr("action", "/build_course/video-import?"+cps_str);
        $('#videoinfo-upload-form').submit();
        return false;
    }
    
    /**
     * 弹出视频
     * @param {JQueryDom} $dom
     * @returns {void}
     */
    function popVideo($dom){
        $('#media-player').get(0).src = $dom.attr('data-path');
        $('#pop-modal').modal('show');
    }
        
        
    /************************************************************************************
     *
     * 视频文件上传
     *
     ************************************************************************************/    
    function initEuploader(){
        require(['euploader'], function (euploader) {
            //公共配置
            var config = {
                swf: php_swfpath + "/Uploader.swf",
                //文件接收服务端。
                server: '/webuploader/default/upload',
                //检查文件是否存在
                checkFile: '/webuploader/default/check-file',
                //分片合并
                mergeChunks: '/webuploader/default/merge-chunks',
                //自动上传
                auto: true,
                //开起分片上传
                chunked: true,
                // 上传容器
                container: '#uploader-container',
                //指定接受哪些类型的文件
                accept: {
                    title: 'Mp4',
                    extensions: 'mp4',
                    mimeTypes: 'video/mp4',
                },
                formData: {
                    _csrf: "$csrfToken",
                    //指定文件上传到的应用
                    app_id: "",
                    //同时创建缩略图
                    makeThumb: 1
                }

            };
            uploader = new euploader.Uploader(config, euploader.FilelistView);
            /* 上传完成、文件移除 */
            $(uploader).on('uploadFinished',function(event){
                updateFiles();  //同步到 videoBatchUpload
            });
            
            $(uploader).on('fileDequeued',function(event,data){
                //外链data = file.id
                var is_link = typeof data == 'string';
                var file_id = is_link ? data : data.dbFile.id;
                //文件移除有两种情况，一种是移除外链（删除外链file），另一种是移除用户手动上传的（）
                if(is_link){
                    removeLinkVideo(file_id);
                }
                updateFiles();  //同步到 videoBatchUpload
            });
            
            /* 初始完成先加载外链视频 */
            loadLinkVideo();
        });
    }
    
    /**
     * 加载外链视频
     * @returns {void}
     */
    function loadLinkVideo(){
        var reg = /^http:\/\//;
        var data;
        var wait_num = 0;
        $.each(php_videodatas,function(){
            if(reg.test(this['video.filename'])){
                wait_num++;
                $.get("/webuploader/default/upload-link?video_path=" + this['video.filename'], function(rel){
                    if(rel['success'] && rel['data']['code'] == '0'){
                        //添加到上传组件显示
                        uploader.addCompleteFiles([rel['data']['data']]);
                        //设置 name = oss_key，否则与表格对应不上
                        //rel['data']['data']['name'] = rel['data']['data']['oss_key'];
                        linkVideos.push(covertFileData(rel['data']['data']));  //添加到外链视频集合
                    }
                    wait_num--;
                    if(wait_num<=0){
                        updateFiles();      //同步到 videoBatchUpload
                    }
                });
            }
        });
    }
    
    /**
     * 删除外链视频
     * @argument {string} file_id 
     * @returns {void}
     */
    function removeLinkVideo(file_id){
        linkVideos=$.grep(linkVideos,function(file_item,i){
            return file_item.id != file_id;
        });
    }
    /**
     * 更新视频文件到批量控制器
     * @returns {void}
     */
    function updateFiles(){
        //所有上传完成的文件
        uploaderVideos = [];
        $.each(uploader.uploader.getFiles('complete'),function(){
            uploaderVideos.push(covertFileData(this.dbFile,this.name));
        });
        //设置新的视频文件
        videoBatchUpload.setFiles(uploaderVideos.concat(linkVideos));
    }
    
    /**
     * dbfile_data 转换为指定格式object
     * @param {object} dbdata
     * @param {string} name     指定名称
     * @returns {object}    {id,name,text,oss_key,thumb_path,size}
     */
    function covertFileData(db_file,name){
        var text = name || (db_file.is_link ? db_file.oss_key : db_file.name);         //为seelct2组件设置 text 属性，可用于显示名称、过滤功能，外链情况下使用oss_key
        var data = {
                    id : db_file.id,
                    name : name || db_file.name,
                    text : text,
                    oss_key : absolutePath(db_file.oss_key),        //转换成阿里路径
                    thumb_path : absolutePath(db_file.thumb_path == '' ? 'static/imgs/notfound.png' : db_file.thumb_path),  //转换成阿里路径
                    size : Wskeee.StringUtil.formatBytes(db_file.size)
                };
        return data;
    }
   
    /**
     * 转换为阿里路径
     * @param {string} path
     * @returns {string}
     */
    function absolutePath(path){
        if (path.indexOf('http://') === -1) {
            path = php_aliyun_host+"/" + path;
        }
        return path;
    }
    
    /************************************************************************************
     *
     * 提交
     *
     ************************************************************************************/ 
    /**
     * 初始提交
     * @returns {void}
     */
    function initSubmit(){
        $('#video-submit').on('click',function(){
            videoBatchUpload.submit(getSubmitCommonParams());
        });
    }
    
    /**
     * 获取公共配置
     * @returns {object}
     */
    function getSubmitCommonParams(){
        var watermark_ids = [];
        $('#video-mts_watermark_ids :checkbox:checked').each(function (index, item) {
            watermark_ids.push($(this).val());
        });
        /* 设置公共上传参数 */
        var submit_common_params = {
            user_cat_id : $('#user_cat_id').val(),
            mts_watermark_ids : watermark_ids.join(',')
        };
        submit_common_params[php_csrf_param] = php_csrf_value;
        return submit_common_params;
    }
</script>