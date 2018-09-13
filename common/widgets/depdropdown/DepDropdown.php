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
    public $pluginOptions = [];
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
        if ($this->hasModel()) {
            $this->input = Html::activeInput('text', $this->model, $this->attribute, ['style' => ['display' => 'none']]);
            $this->input_id = Html::getInputId($this->model, $this->attribute);
        } else {
            $this->input = Html::input('text', $this->name, $this->value, ['id' => $this->name,'style' => ['display' => 'none']]);
            $this->input_id = $this->name;
        }


        $this->pluginOptions = array_merge([
            'plug_id' => 'DepDropdown_' . rand(1000, 9999),
            'max_level' => 4,
            'url' => '',
            'type' => '',
            'prompt' => Yii::t('app', 'Select Placeholder'),
            'name' => $this->input_id,
            'value' => $this->value,], $this->pluginOptions);

        $this->itemOptions = array_merge([
            'class' => 'form-control',
            'prompt' => $this->pluginOptions['prompt'],
                ], $this->itemOptions);
    }

    //put your code here
    public function run() {
        parent::run();
        //级数最小一级
        $level = count($this->items);
        if ($level > $this->pluginOptions['max_level']) {
            $level = $this->pluginOptions['max_level'];
        }
        /**
         * 初始已选
         */
        $html = '';
        for ($i = 0; $i < $level; $i++) {
            $item = Html::dropDownList(null, 
                    isset($this->values[$i]) ? $this->values[$i] : null, 
                    isset($this->items[$i]) ? $this->items[$i] : [], 
                    array_merge($this->itemOptions, ['data-level' => $i, 'data-name' => $this->input_id]));

            $html .= $item . ' ';
        }
        $html .= $this->input;
        $this->registerAssets();
        return Html::tag('div', $html, ['id' => $this->pluginOptions['plug_id'], 'class' => 'dep-dropdown']);
    }

    public function registerAssets() {
        $view = $this->getView();
        DepDropdownAssets::register($view);
        //设置组件配置
        $this->pluginOptions['itemOptions'] = Html::renderTagAttributes($this->itemOptions);
        $config = Json::encode($this->pluginOptions);
        $js = <<< JS
            new  window.{$this->pluginName}({$config});
JS;
        $view->registerJs($js, View::POS_READY);
    }

}
