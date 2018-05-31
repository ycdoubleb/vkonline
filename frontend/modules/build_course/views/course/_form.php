<?php

use common\models\vk\Category;
use common\models\vk\Course;
use common\widgets\depdropdown\DepDropdown;
use common\widgets\webuploader\WebUploaderAsset;
use kartik\widgets\FileInput;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

/* @var $this View */
/* @var $model Course */
/* @var $form ActiveForm */

//组装获取老师的下拉的格式对应数据
$teacherFormat = [];
foreach ($allTeacher as $teacher) {
    $teacherFormat[$teacher->id] = [
        'avatar' => $teacher->avatar, 
        'is_certificate' => $teacher->is_certificate,
        'sex' => $teacher->sex,
        'job_title' => $teacher->job_title,
    ];
}
$formats = json_encode($teacherFormat);
$format = <<< SCRIPT
    window.formats = $formats;
    function format(state) {
        //如果非数组id，返回选项组
        if (!state.id){
            return state.text
        };
        //图片位置
        var src = formats[state.id]['avatar'].toLowerCase();
        //为否显示认证图标
        var isShow = formats[state.id]['is_certificate'] ? '<i class="fa fa-vimeo icon-vimeo"></i>' : '<i class="fa icon-vimeo"></i>';
        var sex = formats[state.id]['sex'] == 1 ? '男' : '女';
        var links = '/teacher/default/view?id=' + $.trim(state.id);
        //返回结果（html）
        return isShow + 
            '<div class="avatars">' + 
                '<img class="img-circle" src="' + src + '" width="32" height="32"/>' + 
            '</div>' 
            + state.text + '（' + sex + '<span class="job-title">' + formats[state.id]['job_title'] + '</span>）' + 
            '<a href="' + links.replace(/\s/g,"") + '" class="links" target="_blank" onmouseup=";event.cancelBubble = true;">' + 
                '<i class="fa fa-eye"></i>' + 
            '</a>';
    } 
        
SCRIPT;
$escape = new JsExpression("function(m) { return m; }");
$this->registerJs($format, View::POS_HEAD);
?>

<div class="course-form form set-margin set-bottom">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form',
            'class'=>'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
    <!--课程分类-->
    <?= $form->field($model, 'category_id',[
        'template' => "{label}\n<div class=\"col-lg-9 col-md-9\">{input}</div>\n<div class=\"col-lg-9 col-md-9\">{error}</div>", 
    ])->widget(DepDropdown::class,[
        'plugOptions' => [
            'url' => Url::to('/admin_center/category/search-children', false),
            'level' => 3,
        ],
        'items' => Category::getSameLevelCats($model->category_id),
        'values' => $model->category_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($model->category_id)->path))),
    ])->label(Yii::t('app', '{Course}{Category}', [
        'Course' => Yii::t('app', 'Course'), 'Category' => Yii::t('app', 'Category')
    ])) ?>
    <!--属性-->
    <div id="courseattribute">
        <?php if(!$model->isNewRecord): ?>
            <?php $attrs = [];
                foreach($allAttrs as $name => $attr): 
                foreach ($attr['values'] as $val) {
                    $key = $attr['id'] . '_' . $attr['sort_order'] . '_' . $val;
                    $attrs[$name][$key] = $val;
                }
            ?>
            <div class="form-group">
                <?= Html::label($name, null, ['class' => 'col-lg-1 col-md-1 control-label form-label', 'style' => 'color: #ccc']) ?>
                <div class="col-lg-3 col-md-3">
                    <?= Html::dropDownList('CourseAttribute[]', $attrsSelected, $attrs[$name], [
                        'class' => 'form-control', 'placeholder'=>'请选择...']) ?>
                </div>
            </div>
            <?php endforeach;?>
        <?php endif;?>
    </div>
    <!--课程名称-->
    <?= $form->field($model, 'name')->textInput([
        'placeholder' => '请输入...', 'maxlength' => true
    ])->label(Yii::t('app', '{Course}{Name}', [
        'Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')
    ])) ?>
    <!--主讲老师-->
    <?php
        $refresh = Html::a('<i class="glyphicon glyphicon-refresh"></i>', ['teacher/refresh'], [
            'id' => 'refresh',  'class' => 'btn btn-primary'
        ]);
        $newAdd = Html::a('新增', ['teacher/create'], ['class' => 'btn btn-primary', 'target' => '_blank']);
        $prompt = Html::tag('span', '（新增完成后请刷新列表）', ['style' => 'color: #999']);
        echo  $form->field($model, 'teacher_id', [
            'template' => "{label}\n<div class=\"col-lg-7 col-md-7\">{input}</div>"  . 
                "<div class=\"col-lg-1 col-md-1\" style=\"width: 50px;padding: 3px\">{$refresh}</div>" . 
                "<div class=\"col-lg-1 col-md-1\" style=\"width: 70px;padding: 3px\">{$newAdd}</div>" . 
                "<div class=\"col-lg-1 col-md-1\" style=\"width: 170px; padding: 10px 0;\">{$prompt}</div>\n" .
            "<div class=\"col-lg-7 col-md-7\">{error}</div>",
        ])->widget(Select2::class,[
            'data' => ArrayHelper::map($allTeacher, 'id', 'name'), 
            'options' => ['placeholder'=>'请选择...',],
            'pluginOptions' => [
                'templateResult' => new JsExpression('format'),     //设置选项格式
                'escapeMarkup' => $escape,
                'allowClear' => true
            ],
        ])->label(Yii::t('app', '{mainSpeak}{Teacher}', [
            'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
        ]));
    ?>
    <!--封面图片-->
    <?= $form->field($model, 'cover_img')->widget(FileInput::class, [
        'options' => [
            'accept' => 'image/*',
            'multiple' => false,
        ],
        'pluginOptions' => [
            'resizeImages' => true,
            'showCaption' => false,
            'showRemove' => false,
            'showUpload' => false,
            'browseClass' => 'btn btn-primary btn-block',
            'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
            'browseLabel' => '选择上传图像...',
            'initialPreview' => [
                $model->isNewRecord || empty($model->cover_img)?
                        Html::img(['/upload/course/default.png'], ['class' => 'file-preview-image', 'width' => '215', 'height' => '140']) :
                        Html::img([$model->cover_img], ['class' => 'file-preview-image', 'width' => '215', 'height' => '140']),
            ],
            'overwriteInitial' => true,
        ],
    ]);?>
    <!--标签-->
    <div class="form-group field-tagref-tag_id required">
        <?= Html::label(Yii::t('app', 'Tag'), 'tagref-tag_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?=  Select2::widget([
                'id' => 'tagref-tag_id',
                'name' => 'TagRef[tag_id]',
                'data' => $allTags,
                'value' => !$model->isNewRecord ? $tagsSelected : null, 
                'showToggleAll' => false,
                'options' => [
                    'class' => 'form-control',
                    'style' => 'height: auto',
                    'multiple' => true,
                    'placeholder' => '请选择至少5个标签...'
                ],
                'pluginOptions' => [
                    'tags' => true,
                    'allowClear' => false,
                    'tokenSeparators' => [','],
                ],
            ]) ?>
        </div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    <!--描述-->
    <?= $form->field($model, 'des', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea(['value' => $model->isNewRecord ? '无' : $model->des, 'rows' => 6, 'placeholder' => '请输入...']) ?>
    <!--课程资源-->
    <div class="form-group field-courseattachment-file_id">
        <?= Html::label(Yii::t('app', '{Course}{Resources}', [
            'Course' => Yii::t('app', 'Course'), 'Resources' => Yii::t('app', 'Resources')
        ]), 'courseattachment-file_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div id="uploader-container" class="col-lg-11 col-md-11"></div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    
    <!--描述-->
    <?= $form->field($model, 'content', [
        'template' => "{label}\n<div class=\"col-lg-11 col-md-11\">{input}</div>\n<div class=\"col-lg-11 col-md-11\">{error}</div>"
    ])->textarea()->label(Yii::t('app', '{Course}{Synopsis}', [
        'Course' => Yii::t('app', 'Course'), 'Synopsis' => Yii::t('app', 'Synopsis')
    ])) ?>
    
    <div class="form-group">
        <?= Html::label(null, null, ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?= Html::button(Yii::t('app', 'Submit'), ['id' => 'submitsave', 'class' => 'btn btn-success btn-flat']) ?>
        </div> 
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$domes = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/course/_attr.php')));
//获取flash上传组件路径
$swfpath = $this->assetManager->getPublishedUrl(WebUploaderAsset::register($this)->sourcePath);
//获取已上传文件
$attFiles = json_encode($attFiles);
$csrfToken = Yii::$app->request->csrfToken;
$app_id = Yii::$app->id ;
$js = 
<<<JS
    //初始化百度编辑器
    UE.getEditor('course-content', {
        initialFrameHeight: 500,
        maximumWords: 100000,
    });     
        
    //选择二级分类加载其对应的属性
    $('select[data-name="course-category_id"]').eq(2).change(function(){
        var items = $domes;
        var dome = "";
        var options = [];
        $.post("../course/attr-search?cate_id=" + $(this).val(), function(rel){
            var data = rel['data'];
            for(var name in data){
                $.each(data[name].values, function(index, text){
                    var val = data[name].id + "_" + data[name].sort_order + "_" + text;
                    options[name] += "<option value=" + val + ">" + text + "</option>";
                });
                dome += Wskeee.StringUtil.renderDOM(items, {name: name, option: options[name].replace("undefined", "")});
            }
            $("#courseattribute").append(dome);
        });
    });
        
    //单击刷新按钮重新加载老师下拉列表
    $("#refresh").click(function(){
        $('#course-teacher_id').html("");
        $.get($(this).attr("href"),function(rel){
            if(rel['code'] == '200'){
                window.formats = rel['data']['format'];
                $('<option/>').val('').text('请选择...').appendTo($('#course-teacher_id'));
                $.each(rel['data']['dataMap'], function(id, name){
                    $('<option>').val(id).text(name).appendTo($('#course-teacher_id'));
                });
            }
        })
        return false;
    });
        
    window.uploader;
    //加载文件上传  
    require(['euploader'], function (euploader) {
        //公共配置
        var config = {
            swf: "$swfpath" + "/Uploader.swf",
            // 文件接收服务端。
            server: '/webuploader/default/upload',
            //检查文件是否存在
            checkFile: '/webuploader/default/check-file',
            //分片合并
            mergeChunks: '/webuploader/default/merge-chunks',
            //自动上传
            auto: false,
            //开起分片上传
            chunked: true,
            // 上传容器
            container: '#uploader-container',
            formData: {
                _csrf: "$csrfToken",
                //指定文件上传到的应用
                app_id: "$app_id",
                //同时创建缩略图
                makeThumb: 1
            }

        };
        window.uploader = new euploader.Uploader(config, euploader.FilelistView);
        window.uploader.addCompleteFiles($attFiles);
    });
    /**
    * 上传文件完成才可以提交
    * @return {uploader.isFinish}
    */
    function tijiao() {
       return window.uploader.isFinish() //是否已经完成所有上传;
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>