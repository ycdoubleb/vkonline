<?php

namespace common\widgets\depdropdown;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;

/**
 * 依赖上一级数据的多级下拉组件.
 * 最终只保存最后一个选择
 *
 * @author wskeee
 */
class DepDropdown extends InputWidget {

    //input
    private $input;
    //input_id
    private $input_id;

    /**
     * 插件选项
     * level    1~3 级级
     * url      获取子级URL路径
     * type     类型
     * @var array 
     */
    public $plugOptions = [];
    //插件名
    public $pluginName = 'ewidegets.DepDropdown';
    //所有数据=[[1=>'A',2=>'B'],[],...]
    public $items = [];
    //所有值=[1,2,3,4]
    public $values = [];
    //每个下拉选项
    public $itemOptions = [];
    //每个下拉select选项
    public $itemInputOptions = [];

    public function init() {
        parent::init();
        $this->input = Html::activeInput('text', $this->model, $this->attribute, ['style' => ['display' => 'none']]);
        $this->input_id = Html::getInputId($this->model, $this->attribute);

        $this->plugOptions = array_merge([
            'level' => 2,
            'url' => '',
            'type' => '',
            'name' => $this->input_id,
            'value' => $this->value,], $this->plugOptions);

        $this->itemOptions = array_merge([
            'style' => [
                'width' => '200px',
                'display' => 'inline-block',
            ]], $this->itemOptions);
        
        $this->itemInputOptions = array_merge(
            ['class' => 'form-control',
                'prompt' => Yii::t('app', 'Select Placeholder')], $this->itemInputOptions);
    }

    //put your code here
    public function run() {
        parent::run();
        $html = '';
        for ($i = 0; $i < $this->plugOptions['level']; $i++) {
            $content = Html::dropDownList(null, 
                    isset($this->values[$i]) ? $this->values[$i] : null, 
                    isset($this->items[$i]) ? $this->items[$i] : [], 
                    array_merge($this->itemInputOptions, ['data-level' => $i,'data-name'=> $this->input_id]));
            
            $item = Html::tag('div', $content, $this->itemOptions);   
            
            $html .= $item.' ';
        }
        $html .= $this->input;
        $this->registerAssets();
        return Html::tag('div',$html);
    }

    public function registerAssets() {
        $view = $this->getView();
        DepDropdownAssets::register($view);
        $config = Json::encode($this->plugOptions);
        $js = <<< JS
            new  window.{$this->pluginName}({$config});
JS;
        $view->registerJs($js, View::POS_READY);
    }

}
