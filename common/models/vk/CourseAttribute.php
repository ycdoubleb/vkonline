<?php

namespace common\models\vk;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_attribute}}".
 *
 * @property string $id             课程属性名
 * @property string $name           属性名
 * @property string $category_id    所属分类id
 * @property int $type              0唯一属性 1单选属性 2复选属性
 * @property int $input_type        0手工输入 1多行输入 2列表选择
 * @property int $sort_order        排序索引
 * @property int $index_type        0不检索 1关键字检索 2范围检索
 * @property string $values         列表候选值，每行一项
 * @property int $is_del            标识逻辑删除 0未删除，1已删除
 */
class CourseAttribute extends ActiveRecord
{
    //输入类型：手工输入 多行输入 列表选择
    const INPUT_TYPE_SINGLE = 0;
    const INPUT_TYPE_MULTILINE = 1;
    const INPUT_TYPE_LIST = 2;
    
    //属性类型：唯一属性 单选属性 复选属性'
    const TYPE_UNIQUE = 0;
    const TYPE_SINGLE = 1;
    const TYPE_MULTILINE = 2;
    
    //属性类型
    public static $type_keys = ['唯一属性','单选属性','复选属性'];
    //输入类型
    public static $input_type_keys = ['手工输入','多行输入','列表选择'];
    //检索类型
    public static $index_type_keys = ['否','是','范围检索'];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_attribute}}';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id','type', 'input_type', 'index_type', 'is_del' , 'sort_order'], 'integer'],
            [['values'], 'string'],
            [['name'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'category_id' => Yii::t('app', 'Category'),
            'type' => Yii::t('app', 'Type'),
            'input_type' => Yii::t('app', '{Input}{Type}',['Input' => Yii::t('app', 'Input'),'Type' => Yii::t('app', 'Type'),]),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'index_type' => Yii::t('app', '{Is}{Screen}',['Is' => Yii::t('app', 'Is'),'Screen' => Yii::t('app', 'Screen'),]),
            'values' => Yii::t('app', 'Values'),
            'is_del' => Yii::t('app', '{Is}{Delete}',['Is' => Yii::t('app', 'Is'),'Delete' => Yii::t('app', 'Delete'),]),
        ];
    }
}
