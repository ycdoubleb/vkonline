<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\UserCategory;
use common\widgets\depdropdown\DepDropdown;
use common\widgets\tagsinput\TagsInputAsset;
use common\widgets\watermark\WatermarkAsset;
use common\widgets\webuploader\WebUploaderAsset;
use frontend\assets\ClipboardAssets;
use frontend\modules\build_course\assets\DocumentImportAssets;
use kartik\growl\GrowlAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
$this->title = Yii::t('app', '{Batch}{Import}{Document}', [
    'Batch' => Yii::t('app', 'Batch'),  'Import' => Yii::t('app', 'Import'),  'Document' => Yii::t('app', 'Document')
]);

GrowlAsset::register($this);
WatermarkAsset::register($this);
ClipboardAssets::register($this);
DocumentImportAssets::register($this);
TagsInputAsset::register($this);

//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
$csrfToken = Yii::$app->request->csrfToken;
//加载 DOM 模板
$file_select_dom = str_replace("\n", ' ', $this->render('____file_select_dom'));
$document_data_tr_dom = str_replace("\n", ' ', $this->render('____document_data_tr_dom'));

?>

<div class="document-import container">
    
    <div class="panel">
        <div class="panel-head"><?= $this->title ?></div>
        <div class="panel-body">
            <!--警告框-->
            <div class="alert alert-danger alert-dismissible" style="margin-bottom: 0px" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <p>1、批量导入<?= Html::a('（表格模板下载）', Aliyun::absolutePath('static/doc/template/document_import_template.xlsx?rand='. rand(0, 9999)), ['class' => 'alert-link']) ?></p>
                <p>2、导入步骤：（1）选择存放目录 > （2）上传文档信息 > （3）导入文档文件 > （4）提交</p>
            </div>
            
            <!-- 公共设置 -->
            <div class="setting">
                <div class="title">公共设置</div>
                <hr>
                <div class="setting-box">
                    <div class="setting-item">
                        <div class="title">存放目录：</div>
                        <div class="document-dir-box"><?=
                            DepDropdown::widget([
                                'name' => 'user_cat_id',
                                'value' => $user_cat_id,
                                'pluginOptions' => [
                                    'url' => Url::to('/build_course/user-category/search-children', false),
                                    'max_level' => 10,
                                    'onChangeEvent' => new JsExpression('function(value){  }')
                                ],
                                'items' => UserCategory::getSameLevelCats($user_cat_id, true, true),
                                'values' => $user_cat_id == 0 ? [] : array_values(array_filter(explode(',', UserCategory::getCatById($user_cat_id)->path))),
                                'itemOptions' => [
                                    'style' => 'width: 150px; display: inline-block;',
                                ],
                            ])
                            ?></div>
                    </div>
                </div>
            </div>
            
            <!-- 文档信息 -->
            <hr/>
            <div class="document-info">
                <div class="title">上传音频信息
                    <!--文件上传-->
                    <div class="pull-right">
                        <?php $form = ActiveForm::begin([
                            'options'=>[
                                'id' => 'documentinfo-upload-form',
                                'class'=>'form-horizontal',
                                'enctype' => 'multipart/form-data',
                                'method' => 'post',
                            ],
                        ]); ?>

                        <div class="vk-uploader">
                            <div class="btn btn-pick">选择文件</div>
                            <div class="file-box">
                                <input type="file" id="importfile" name="importfile" class="file-input" accept=".xlsx,.xls,.xlm,.xlt,.xlc,.xml" onchange="uploadDocumentInfo()">
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
                            <th style="width: 200px;">文档名称</th>
                            <th style="width: 320px;">文档标签</th>
                            <th style="width: 300px;">文档文件</th>
                            <th style="width: 100px;">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                    </tbody>
                </table>
            </div>
            
            <!-- 文档文件 -->
            <div class="aduio-file">
                <div class="title">上传文档文件</div>
                <div class="uploader-box">
                    <div id="uploader-container" class="clear-padding"></div>
                </div>
            </div>
        </div>
        <!-- 提交 -->
        <div class="panel-foot submit-box">
            <a id="document-submit" class="btn btn-highlight btn-flat">提交</a>
            <span id="submit-result"></span>
        </div>
    </div>
    
    <!-- 模态框 -->
    <div id="pop-modal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" style="width: 1200px;" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">预览文档</h4>
                </div>
                <div class="modal-body">
                    <iframe id="media-player" width="100%" height="600" style="border: none"></iframe>
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
    //音频数据
    var php_documentdatas = <?= json_encode($documents) ?>;
    //音频文件下拉dom
    var php_file_select_dom = '<?= $file_select_dom ?>';
    //音频 tr dom
    var php_document_data_tr_dom = '<?= $document_data_tr_dom ?>';
    //csrf key
    var php_csrf_param = "<?= Yii::$app->getRequest()->csrfParam ?>";
    //csrf value
    var php_csrf_value = "<?= Yii::$app->getRequest()->csrfToken ?>";

    //批量上传控制器
    var documentBatchUpload;
    //音频文件上传组件
    var uploader;
    //上传工具的音频
    var uploaderDouments = [];
    
    /**
     * html 加载完成后初始化所有组件
     * @returns {void}
     */
    window.onload = function(){
        initDocumentInfo();        //初始文档信息
        initEuploader();        //初始音频文件上传
        initSubmit();           //初始提交
    }
    
    /************************************************************************************
    *
    * 音频信息上传
    *
    ************************************************************************************/
    function initDocumentInfo(){
        // 初始并且初始化指量上传控制器
        documentBatchUpload = new youxueba.DocumentBatchUpload({
            file_select_dom : php_file_select_dom,
            document_data_tr_dom : php_document_data_tr_dom,
        });
        documentBatchUpload.init(php_documentdatas);
        /**
         * 上传完成
         */
        $(documentBatchUpload).on('submitFinished',function(){
            var max_num = this.documents.length;
            var completed_num = 0;
            $.each(this.documents,function(){
                if(this.submit_result){
                    completed_num++;
                }
            });
            $('#submit-result').html("共有 "+max_num+" 个文档需要上传，其中 "+completed_num+" 个成功， "+(max_num - completed_num)+" 个失败！");
            $.notify({message: '提交完成'}, {type: "success"});
        });
        
        
        /* 弹出视频模态框 */
        $('#pop-modal').on('shown.bs.modal',function(){
            
        });
        $('#pop-modal').on('hide.bs.modal',function(){
            $('#media-player').get(0).src = '';
        });
    }
    /* 提交表数据 */
    function uploadDocumentInfo(){
        var cps = getSubmitCommonParams();
        var cps_str = "user_cat_id="+cps['user_cat_id'];
        //添加传参数
        $('#documentinfo-upload-form').attr("action", "/build_course/document-import?"+cps_str);
        $('#documentinfo-upload-form').submit();
        return false;
    }
    
    /**
     * 弹出文档
     * @param {JQueryDom} $dom
     * @returns {void}
     */
    function popDocument($dom){
        $('#media-player').get(0).src = 'http://eezxyl.gzedu.com/?furl=' + $dom.attr('data-path');
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
                    title: 'Text',
                    extensions: 'doc,docx,txt,xls,xlsx,ppt,pptx',
                    mimeTypes: '.doc,.docx,.txt,.xls,.xlsx,.ppt,.pptx',
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
                updateFiles();  //同步到 documentBatchUpload
            });
            
            $(uploader).on('fileDequeued',function(event,data){
                var file_id = data.dbFile.id;
                updateFiles();  //同步到 documentBatchUpload
            });
        });
    }
    
    /**
     * 更新视频文件到批量控制器
     * @returns {void}
     */
    function updateFiles(){
        //所有上传完成的文件
        uploaderDocuments = [];
        $.each(uploader.uploader.getFiles('complete'),function(){
            uploaderDocuments.push(covertFileData(this.dbFile,this.name));
        });
        //设置新的文档文件
        documentBatchUpload.setFiles(uploaderDocuments);
    }
    
    /**
     * dbfile_data 转换为指定格式object
     * @param {object} dbdata
     * @param {string} name     指定名称
     * @returns {object}    {id,name,text,oss_key,thumb_path,size}
     */
    function covertFileData(db_file,name){
        var data = {
            id : db_file.id,
            name : name || db_file.name,
            text : name || db_file.nam,     //为seelct2组件设置 text 属性，可用于显示名称、过滤功能
            oss_key : absolutePath(db_file.oss_key),        //转换成阿里路径
            thumb_path : Wskeee.StringUtil.completeFilePath('/imgs/build_course/images/' + Wskeee.StringUtil.getFileSuffixName(absolutePath(db_file.oss_key))  + '.png'),
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
        $('#document-submit').on('click',function(){
            documentBatchUpload.submit(getSubmitCommonParams());
        });
    }
    
    /**
     * 获取公共配置
     * @returns {object}
     */
    function getSubmitCommonParams(){
        /* 设置公共上传参数 */
        var submit_common_params = {
            user_cat_id : $('#user_cat_id').val(),
        };
        submit_common_params[php_csrf_param] = php_csrf_value;
        return submit_common_params;
    }
</script>