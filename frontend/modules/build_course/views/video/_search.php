<?php

use common\models\vk\UserCategory;
use common\utils\StringUtil;
use common\widgets\depdropdown\DepDropdown;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\web\View;
use yii\widgets\ActiveForm;

//组装获取老师的下拉的格式对应数据
$teacherFormat = [];
foreach ($teacherMap as $teacher) {
    $teacherFormat[$teacher->id] = [
        'avatar' => StringUtil::completeFilePath($teacher->avatar), 
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
        '</div>';
    } 
        
SCRIPT;
$escape = new JsExpression("function(m) { return m; }");
$this->registerJs($format, View::POS_HEAD);

?>

<div class="video-search vk-form set-spacing"> 

    <?php
    $form = ActiveForm::begin([
                'action' => ['result'],
                'method' => 'get',
                'options' => [
                    'id' => 'build-course-form',
                    'class' => 'form-horizontal',
                ],
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-5 col-md-5\">{input}</div>\n",
                    'labelOptions' => [
                        'class' => 'col-lg-1 col-md-1 control-label form-label',
                    ],
                ],
    ]);
    ?>

    <div class="col-lg-12 col-md-12">

        <!--所属目录-->
        <div class="form-group field-videosearch-user_cat_id">
            <?= Html::label(Yii::t('app', '{The}{Catalog}', [
                'The' => Yii::t('app', 'The'), 
                'Catalog' => Yii::t('app', 'Catalog')]) . '：', 'videosearch-user_cat_id', [
                    'class' => 'col-lg-1 col-md-1 control-label form-label'
            ]) ?>
            <div class="col-lg-11 col-md-11">
                <ul class="breadcrumb">
                    <?php 
                        $userCatId = ArrayHelper::getValue($filters, 'user_cat_id', null);  //用户分类id
                        if(isset($pathMap[$userCatId]) && count($pathMap[$userCatId]) > 0){
                            $endPath = end($pathMap[$userCatId]);
                            echo '<li>' . Html::a('根目录', ['index', 'user_cat_id' => null]) . '<span class="set-route">›</span></li>';
                            foreach ($pathMap[$userCatId] as $path) {
                                echo '<li>';
                                echo Html::a($path['name'], array_merge(['index'], array_merge($filters, ['user_cat_id' => $path['id']])));
                                if($path['id'] != $endPath['id']){
                                    echo '<span class="set-route">›</span>';
                                }
                                echo '</li>';
                            }
                            echo Html::hiddenInput('user_cat_id', ArrayHelper::getValue($filters, 'user_cat_id'));
                        }else{
                            echo '<li>目录位置...</li>';
                        }
                    ?>
                </ul>
            </div>
        </div>
       
        <!--视频名称-->
        <?=
        $form->field($searchModel, 'name', [
            'template' => "{label}\n<div class=\"col-lg-5 col-md-5\">{input}</div>" .
                "<div class=\"operate\">" .
                    "<a id=\"op_search\" data-toggle=\"collapse\" data-target=\"#collapse\" aria-expanded=\"false\" aria-controls=\"collapse\">" .
                       "高级搜索<span class=\"arrow\">↓</span>" .
                    "</a>" .
                "</div>\n",
        ])->textInput([
            'placeholder' => '请输入...', 'maxlength' => true,
            'onchange' => 'submitForm();',
        ])->label(Yii::t('app', '{Video}{Name}：', [
                    'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')
        ]))
        ?>
        
        <div id="collapse" class="collapse">
            <!--主讲老师-->
            <?=
            $form->field($searchModel, 'teacher_id')->widget(Select2::class, [
                'data' => ArrayHelper::map($teacherMap, 'id', 'name'),
                'options' => ['placeholder' => '请选择...',],
                'pluginOptions' => [
                    'templateResult' => new JsExpression('format'), //设置选项格式
                    'escapeMarkup' => $escape,
                    'allowClear' => true
                ],
                'pluginEvents' => [
                    'change' => 'function(){ submitForm(); }'
                ]
            ])->label(Yii::t('app', '{mainSpeak}{Teacher}：', [
                        'mainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')
            ]))
            ?>

            <!--查看权限-->
            <?=
            $form->field($searchModel, 'level')->radioList(['' => '全部', 0 => '私有', 2 => '公开', 1 => '仅集团用户'], [
                'value' => ArrayHelper::getValue($filters, 'VideoListSearch.level', ''),
                'itemOptions' => [
                    'onclick' => 'submitForm();',
                    'labelOptions' => [
                        'style' => [
                            'margin' => '5px 29px 10px 0px',
                            'color' => '#666666',
                            'font-weight' => 'normal',
                        ]
                    ]
                ],
            ])->label(Yii::t('app', '{View}{Privilege}：', [
                        'View' => Yii::t('app', 'View'), 'Privilege' => Yii::t('app', 'Privilege')
            ]))
            ?>
            
            <!--转码状态-->
            <?=
            $form->field($searchModel, 'mts_status')->radioList(['' => '全部', 2 => '已转码', '[0, 1, 5]' => '未转码'], [
                'value' => ArrayHelper::getValue($filters, 'VideoListSearch.mts_status', ''),
                'itemOptions' => [
                    'onclick' => 'submitForm();',
                    'labelOptions' => [
                        'style' => [
                            'margin' => '5px 29px 10px 0px',
                            'color' => '#666666',
                            'font-weight' => 'normal',
                        ]
                    ]
                ],
            ])->label(Yii::t('app', 'Mts Status') . '：')
            ?>
        </div>
    </div>

    <!--标记搜索方式-->
    <?= Html::hiddenInput('sign', 1); ?>
    
    <?php ActiveForm::end(); ?>

</div>

<?php
$sign = ArrayHelper::getValue($filters, 'sign', 0);
$js = <<<JS
    //标记是否为高级搜索
    if($sign){
        $('#collapse').addClass('in');
        $('#op_search').find('span.arrow').html('↑');
        $('#op_search').attr('aria-expanded', true);
    }    
        
    //单击伸收高级搜索    
    $('#op_search').click(function(){
        if($(this).attr('aria-expanded') == 'false'){
            $(this).find('span.arrow').html('↑');
        }else{
            $(this).find('span.arrow').html('↓');
        }
    });
    
    /**
     * 提交表单
     */
    window.submitForm = function(){
        $('#build-course-form').submit();
    }
JS;
    $this->registerJs($js,  View::POS_READY);
?>