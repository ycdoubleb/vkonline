<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_comment}}".
 *
 * @property string $id
 * @property string $course_id 课程ID
 * @property string $user_id 用户ID
 * @property string $content 评论内容
 * @property int $star 星级1~5
 * @property string $zan_count 有用数
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CourseComment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_comment}}';
    }
    
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['zan_count', 'created_at', 'updated_at', 'star'], 'integer'],
            [['course_id', 'user_id'], 'string', 'max' => 32],
            [['content'], 'string', 'max' => 255],
            [['course_id', 'user_id'], 'unique', 'targetAttribute' => ['course_id', 'user_id']],
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
            'user_id' => Yii::t('app', 'User ID'),
            'content' => Yii::t('app', 'Content'),
            'star' => Yii::t('app', 'Star'),
            'zan_count' => Yii::t('app', 'Zan Count'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
