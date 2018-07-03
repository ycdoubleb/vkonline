<?php

namespace common\widgets\grid;

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

    const PLUGIN_NAME = 'GridViewChangeSelfColumn';
    /**
     * 选项属性
     * @var array [labels,values,url,type]<br/>
     * labels   Array   触发式按钮显示,默认['否','是']，eg:['禁用','启用']<br/>
     * values   Array   触发式按钮值,默认[0,1],eg:[0,10]   <br/>
     * url      Url|string  改变时调用方法<br/>
     * type     string      设置组件类型，checkbox 对错型触发式按钮，input 输入框更新 <br/>
     */
    public $plugOptions = [];

    /**
     * 设置禁用启用
     * @var bool|Function 
     * disabled = true|false|0|1|Closure <br/>
     * 
     * eg: disabled = function($model,$key,$index){<br/>
     *      return $model->is_disabled == true;<br/>
     * }<br/>
     */
    public $disabled = null;

    public function init() {
        $this->plugOptions = array_merge([
            'plugin_id' => self::PLUGIN_NAME.'_'. rand(1, 99999),
            //按钮显示，值为0 否，1 是
            'labels' => ['否', '是'],
            'values' => [0, 1],
            'url' => Url::to(['change-value'], true),
            'type' => 'checkbox',], $this->plugOptions);
        $this->format = 'raw';
        $this->registerAssets();
    }

    protected function renderDataCellContent($model, $key, $index) {
        $value = ArrayHelper::getValue($model, $this->attribute);

        if ($this->disabled instanceof \Closure) {
            $disabled = call_user_func($this->disabled, $model, $key, $index);
        } else {
            $disabled = $this->disabled == true || $this->disabled == 1;
        }
        if (!$disabled) {
            //添加交互事件
            $acts = [
                'checkbox' => 'onclick',
                'input' => 'onchange',
            ];
            $inputOptions = [
                'plugin-id' => $this->plugOptions['plugin_id'],
                $acts[$this->plugOptions['type']] => "GridViewChangeSelfColumn_ChangeVal('$key','$this->attribute',this)"
            ];
        } else {
            $inputOptions = ['disabled' => true,'style' => ['opacity' => 0.5]];
        }

        $labels = $this->plugOptions['labels'];
        $values = $this->plugOptions['values'];
        if ($this->plugOptions['type'] == 'checkbox') {
            return Html::tag('span', $value == $values[1] ? "<i class='fa fa-check-circle'></i>" . $labels[1] : "<i class='fa fa-ban'></i>" . $labels[0], array_merge($inputOptions, [
                        'class' => $value == $values[1] ? 'yes' : 'no',
            ]));
        } else if ($this->plugOptions['type'] == 'input') {
            return Html::tag('input', '', array_merge($inputOptions, [
                        'class' => 'form-control',
                        'value' => $value,
            ]));
        }
    }

    /**
     * 注册资源
     */
    protected function registerAssets() {
        //当前组件交换显示的文字及值
        $labels = Json::encode($this->plugOptions['labels']);
        $values = Json::encode($this->plugOptions['values']);
        //当前组件ID
        $plugin_id = $this->plugOptions['plugin_id'];
        //当前组件数据更改时调用的联接
        $url = $this->plugOptions['url'];
        $plugin_name = self::PLUGIN_NAME;
        
        $js = <<<JS
            //创建组件数据中心，保存各个组件的labels和values
            window.$plugin_name = window.$plugin_name || {};
            //添加当前组件数据到数据中心
            window.$plugin_name ['$plugin_id'] = {
                'labels':$labels,
                'values':$values
            };
                
            /**
            * 更新字段值
            * @param {int|string} id       目标ID
            * @param {string} fieldName    字段名称
            * @param {type} obj            dom
            * @returns {void}
            */
           function GridViewChangeSelfColumn_ChangeVal(id,fieldName,obj)
           {	
                var plugin_id = $(obj).attr('plugin-id');
                var plugin_data = window.$plugin_name [plugin_id];
                var labels = plugin_data['labels'];
                var values = plugin_data['values'];;
                var value;
                if($(obj).hasClass('no')) // 图片点击是否操作
                {          
                    $(obj).removeClass('no').addClass('yes');
                    $(obj).html("<i class='fa fa-check-circle'></i>"+labels[1]);
                    value = values[1];
                }else if($(obj).hasClass('yes')){ // 图片点击是否操作                     
                    $(obj).removeClass('yes').addClass('no');
                    $(obj).html("<i class='fa fa-ban'></i>"+labels[0]);
                    value = values[0];
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
        $this->grid->view->registerJs($js, View::POS_HEAD);
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
