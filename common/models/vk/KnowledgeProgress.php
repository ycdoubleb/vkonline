<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%knowledge_progress}}".
 *
 * @property string $id
 * @property string $course_id 课程ID
 * @property string $knowledge_id 视频ID
 * @property string $user_id 用户ID
 * @property string $percent 最后看到的时间点
 * @property string $data 知识学习数据，如最后学习时间、分数等
 * @property int $is_finish 是否已经完成：0否 1是
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class KnowledgeProgress extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%knowledge_progress}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['percent'], 'number'],
            [['is_finish', 'start_time', 'end_time', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'knowledge_id', 'user_id'], 'string', 'max' => 32],
            [['data'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'course_id' => Yii::t('app', 'Course ID'),
            'knowledge_id' => Yii::t('app', 'Knowledge ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'percent' => Yii::t('app', 'Percent'),
            'data' => Yii::t('app', 'Data'),
            'is_finish' => Yii::t('app', 'Is Finish'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
