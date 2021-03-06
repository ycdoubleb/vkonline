<?php

use common\components\aliyuncs\Aliyun;
use common\models\vk\Category;
use common\models\vk\Course;
use common\utils\StringUtil;
use common\widgets\depdropdown\DepDropdown;
use common\widgets\tagsinput\TagsInputAsset;
use common\widgets\ueditor\UeditorAsset;
use frontend\modules\build_course\assets\ModuleAssets;
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

ModuleAssets::register($this);
UeditorAsset::register($this);
TagsInputAsset::register($this);

//组装获取老师的下拉的格式对应数据
$teacherFormat = [];
foreach ($teacherMap as $teacher) {
    $teacherFormat[$teacher->id] = [
        'avatar' => $teacher->avatar, 
        'is_certificate' => $teacher->is_certificate ? 'show' : 'hidden',
        'sex' => $teacher->sex == 1 ? '男' : '女',
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
        //访问名师堂的链接
        var links = '/teacher/default/view?id=' + $.trim(state.id);
        //返回结果（html）
        return '<div class="vk-select2-results single-clamp">' +
            '<a class="icon-vimeo"><i class="fa fa-vimeo ' + formats[state.id]['is_certificate'] + '"></i></a>' + 
            '<img class="avatars img-circle" src="' + formats[state.id]['avatar'].toLowerCase() + '" width="32" height="32"/>' +  state.text + 
            '（' + formats[state.id]['sex'] + '<span class="job-title">' + formats[state.id]['job_title'] + '</span>）' + 
            '<a href="' + links.replace(/\s/g,"") + '" class="links" target="_blank" onmouseup=";event.cancelBubble = true;"><i class="fa fa-eye"></i></a>' +
        '</div>';
    } 
        
SCRIPT;
$escape = new JsExpression("function(m) { return m; }");
$this->registerJs($format, View::POS_HEAD);
?>

<div class="course-form vk-form set-bottom">

    <?php $form = ActiveForm::begin([
        'options'=>[
            'id' => 'build-course-form',
            'class'=>'form-horizontal',
            'enctype' => 'multipart/form-data',
        ],
        'fieldConfig' => [  
            'template' => "{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-7 col-md-7\">{error}</div>",  
            'labelOptions' => [
                'class' => 'col-lg-1 col-md-1 control-label form-label',
            ],  
        ], 
    ]); ?>
    
    <!--课程分类-->
    <?= $form->field($model, 'category_id',[
        'template' => "<span class=\"form-must text-danger\">*</span>{label}\n<div class=\"col-lg-9 col-md-9\">{input}</div>\n<div class=\"col-lg-9 col-md-9\">{error}</div>", 
    ])->widget(DepDropdown::class, [
        'pluginOptions' => [
            'url' => Url::to('/admin_center/category/search-children', false),
            'max_level' => 4,
            'onChangeEvent' => new JsExpression('function(value){ getAttr(value); }')
        ],
        'items' => Category::getSameLevelCats($model->category_id, true),
        'values' => $model->category_id == 0 ? [] : array_values(array_filter(explode(',', Category::getCatById($model->category_id)->path))),
        'itemOptions' => [
            'style' => 'width: 150px; display: inline-block;',
        ],
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
    <?= $form->field($model, 'name', [
        'template' => "<span class=\"form-must text-danger\">*</span>{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>",  
    ])->textInput([
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
            'template' => "<span class=\"form-must text-danger\">*</span>{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>"  . 
                "<div class=\"operate\" class=\"col-lg-4 col-md-4\">" .
                    "<div class=\"pull-left\" style=\"width: 50px;padding: 3px\">{$refresh}</div>" . 
                    "<div class=\"pull-left\" style=\"width: 70px;padding: 3px\">{$newAdd}</div>" . 
                    "<div class=\"pull-left\" style=\"width: 170px; padding: 10px 0;\">{$prompt}</div>" . 
                "</div>\n" .
            "<div class=\"col-lg-6 col-md-6\">{error}</div>",
        ])->widget(Select2::class,[
            'data' => ArrayHelper::map($teacherMap, 'id', 'name'), 
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
    <?= $form->field($model, 'cover_img', [
        'template' => "<span class=\"form-must text-danger\">*</span>{label}\n<div class=\"col-lg-6 col-md-6\">{input}</div>\n<div class=\"col-lg-6 col-md-6\">{error}</div>",
    ])->widget(FileInput::class, [
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
                $model->isNewRecord || empty($model->cover_img) ?
                        Html::img(Aliyun::absolutePath('static/imgs/notfound.png'), ['class' => 'file-preview-image', 'width' => '215', 'height' => '140']) :
                        Html::img($model->cover_img, ['class' => 'file-preview-image', 'width' => '215', 'height' => '140']),
            ],
            'overwriteInitial' => true,
        ],
    ]);?>
    
    <!--标签-->
    <div class="form-group field-tagref-tag_id required">
        <span class="form-must text-danger" style="left: 43px;">*</span>
        <?= Html::label(Yii::t('app', 'Tag'), 'tagref-tag_id', ['class' => 'col-lg-1 col-md-1 control-label form-label']) ?>
        <div class="col-lg-11 col-md-11">
            <?= Html::textInput('TagRef[tag_id]', !$model->isNewRecord ? implode(',', $tagsSelected) : null, [
                'id' => 'obj_taginput', 'class' => 'form-control',  'data-role' => 'tagsinput'
            ]) ?>
        </div>
        <div class="col-lg-11 col-md-11"><div class="help-block"></div></div>
    </div>
    
    <!--课程简介-->
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
//加载ITEM_DOM模板
$item_dom = json_encode(str_replace(array("\r\n", "\r", "\n"), " ", 
    $this->renderFile('@frontend/modules/build_course/views/course/_attr.php')));
$js = <<<JS
    /**
     * 初始化百度编辑器
     */
    UE.getEditor('course-content', {
        initialFrameHeight: 500,
        maximumWords: 100000,
        toolbars:[
            [
                'fullscreen', 'source', '|', 
                'paragraph', 'fontfamily', 'fontsize', '|',
                'forecolor', 'backcolor', '|',
                'bold', 'italic', 'underline','fontborder', 'strikethrough', 'removeformat', 'formatmatch', '|', 
                'justifyleft', 'justifyright' , 'justifycenter', 'justifyjustify', '|',
                'insertorderedlist', 'insertunorderedlist', 'simpleupload', 'horizontal', '|',
                'selectall', 'cleardoc', 
                'undo', 'redo',  
            ]
        ]
    });
        
    /**
     * 选择分类加载其对应的属性
     * @param int value 指定分类id  
     */
    function getAttr(value){
        var item_dom = $item_dom;
        var options = [];
        $("#courseattribute").html("");
        $.post("../course/attr-search?cate_id=" + value, function(rel){
            var data = rel.data;
            for(var name in data){
                //组装课程属性下拉选项
                $.each(data[name].values, function(index, text){
                    var val = data[name].id + "_" + data[name].sort_order + "_" + text;
                    options[name] += "<option value=" + val + ">" + text + "</option>";
                });
                $(Wskeee.StringUtil.renderDOM(item_dom, {name: name, option: options[name].replace("undefined", "")})).appendTo($("#courseattribute"));
            }
        });
    }
        
    /**
     * 单击刷新按钮重新加载老师下拉列表
     */
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
JS;
    $this->registerJs($js,  View::POS_READY);
?>