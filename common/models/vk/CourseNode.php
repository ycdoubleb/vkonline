<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_node}}".
 *
 * @property string $id
 * @property string $course_id 所属课程ID
 * @property string $parent_id 父级引用ID
 * @property int $level 等级：0顶级 1级~3级
 * @property string $name 环节名称
 * @property string $des 描述
 * @property int $is_del 是否删除：0否 1是
 * @property int $sort_order 排序
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CourseNode extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_node}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() 
    {
        return [
            TimestampBehavior::className()
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['id', 'course_id', 'parent_id'], 'string', 'max' => 32],
            [['level', 'is_del'], 'string', 'max' => 1],
            [['name'], 'string', 'max' => 50],
            [['des'], 'string', 'max' => 255],
            [['sort_order'], 'string', 'max' => 2],
            [['id'], 'unique'],
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
            'parent_id' => Yii::t('app', 'Parent ID'),
            'level' => Yii::t('app', 'Level'),
            'name' => Yii::t('app', 'Name'),
            'des' => Yii::t('app', 'Des'),
            'is_del' => Yii::t('app', 'Is Del'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
