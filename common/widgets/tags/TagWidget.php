<?php
namespace common\widgets\tags;
/**
 * 标签云组件
 */
use Yii;
use yii\widgets\InputWidget;
use yii\web\View;
use yii\helpers\Html;
use common\widgets\tags\assets\TagAsset;

class TagWidget extends InputWidget
{
    public $data = [];
    public $value = '';
    
    public function init()
    {
        
    }
    
    public function run()
    {
        $this->registerClientScript();
        
//        if($this->hasModel()){
//            $data['inputName'] = Html::getInputName($this->model, $this->attribute);
//            $data['inputValue'] = Html::getAttributeValue($this->model, $this->attribute);
//        }else{
//            $data['inputName'] = $this->name;
//            $data['inputValue'] = $this->value;
//            $data['inputData'] = $this->data;
//        }
        //  var_dump($data);exit;
        return $this->render('index', ['tags' => $this]);
    }
    
    /**
     * 注册Js
     */
    protected function registerClientScript()
    {
         TagAsset::register($this->view);
         $script = '$(function() {$("#select6").select2({ tags: true });});';
         $this->view->registerJs($script, View::POS_END);
    }
}