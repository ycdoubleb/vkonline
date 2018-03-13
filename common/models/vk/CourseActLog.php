<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%course_act_log}}".
 *
 * @property string $id
 * @property string $action 动作
 * @property string $title 标题
 * @property string $content 内容
 * @property string $related_id 相关环节
 * @property string $course_id 课程ID
 * @property string $created_by 操作者
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CourseActLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_act_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'integer'],
            [['action', 'title'], 'string', 'max' => 50],
            [['content'], 'string', 'max' => 500],
            [['related_id', 'course_id', 'created_by'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'action' => Yii::t('app', 'Action'),
            'title' => Yii::t('app', 'Title'),
            'content' => Yii::t('app', 'Content'),
            'related_id' => Yii::t('app', 'Related ID'),
            'course_id' => Yii::t('app', 'Course ID'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
