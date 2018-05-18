<?php

namespace common\models\vk;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_attr}}".
 *
 * @property string $id id
 * @property string $course_id 课程id
 * @property int $attr_id 属性id
 * @property string $value 属性值,用','分隔项
 * @property int $sort_order 排序
 * @property int $is_del 是否删除：0否 1是
 * 
 * @property CourseAttribute $courseAttribute  获取课程属性
 */
class CourseAttr extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_attr}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['attr_id','sort_order', 'is_del'], 'integer'],
            [['course_id'], 'string', 'max' => 32],
            [['value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'course_id' => Yii::t('app', 'Course ID'),
            'attr_id' => Yii::t('app', 'Attr ID'),
            'value' => Yii::t('app', 'Value'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'is_del' => Yii::t('app', 'Is Del'),
        ];
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCourseAttribute()
    {
        return $this->hasOne(CourseAttribute::class, ['id' => 'attr_id']);
    }
}
