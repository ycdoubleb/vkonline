<?php
namespace backend\components;

use kartik\base\AnimateAsset;
use kartik\growl\GrowlAsset;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\View;

/**
 * 
 * 在列表页更新模型字段
 * checkbox｜input
 * 
 * @author Administrator
 */
class GridViewChangeSelfColumn extends DataColumn {

    /**
     * 选项属性
     * @var array 
     */
    public $plugOptions = [];

    public function init() {
        $this->plugOptions = array_merge([
            //按钮显示，值为0 否，1 是
            'labels' => ['否', '是'],
            'url' => Url::to(['change-value'], true),
            'type' => 'checkbox',], $this->plugOptions);
        $this->format = 'raw';
        $this->registerAssets();
    }

    protected function renderDataCellContent($model, $key, $index) {
        $value = ArrayHelper::getValue($model, $this->attribute);
        $labels = $this->plugOptions['labels'];
        if ($this->plugOptions['type'] == 'checkbox') {
            return Html::tag('span', $value == 1 ? "<i class='fa fa-check-circle'></i>" . $labels[1] : "<i class='fa fa-ban'></i>" . $labels[0], [
                        'class' => $value == 1 ? 'yes' : 'no',
                        'onclick' => "GridViewChangeSelfColumn_ChangeVal('$key','$this->attribute',this)",
            ]);
        } else if($this->plugOptions['type'] == 'input'){
            return Html::tag('input', '', [
                        'class' => 'form-control',
                        'value' => $value,
                        'onchange' => "GridViewChangeSelfColumn_ChangeVal('$key','$this->attribute',this)",
            ]);
        }
    }

    /**
     * 注册资源
     */
    protected function registerAssets() {
        $labels = Json::encode($this->plugOptions['labels']);
        $url = $this->plugOptions['url'];
        $js = <<<JS
            
            /**
            * 更新字段值
            * @param {int|string} id       目标ID
            * @param {string} fieldName    字段名称
            * @param {type} obj            dom
            * @returns {void}
            */
           function GridViewChangeSelfColumn_ChangeVal(id,fieldName,obj)
           {	
                var labels = $labels;
                var value;
                if($(obj).hasClass('no')) // 图片点击是否操作
                {          
                    $(obj).removeClass('no').addClass('yes');
                    $(obj).html("<i class='fa fa-check-circle'></i>"+labels[1]);
                    value = 1;
                }else if($(obj).hasClass('yes')){ // 图片点击是否操作                     
                    $(obj).removeClass('yes').addClass('no');
                    $(obj).html("<i class='fa fa-ban'></i>"+labels[0]);
                    value = 0;
                }else{ // 其他输入框操作7
                    value = $(obj).val();
                }

                $.ajax({
                    url:"$url",
                    data:{id:id,fieldName:fieldName,value:value},
                    success: function(data){
                        if(data['result'] == 0){
                            $.notify({
                                message: data['message'],
                            },{type: 'danger'}); 
                        }
                    }
                });		
           }
JS;
        $this->grid->view->registerJs($js,  View::POS_HEAD);
        //弹出提示窗资源
        GrowlAsset::register($this->grid->view);
        AnimateAsset::register($this->grid->view);
        $css = <<<CSS
            table td .yes{
                color: #1BBC9D;
                cursor: pointer;
            }
            table td .no{
                color: #9ea3a7;
                cursor: pointer;
            }
CSS;
        $this->grid->view->registerCss($css);
    }

}
