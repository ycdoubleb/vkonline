<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_message}}".
 *
 * @property string $id
 * @property int $type 信息类型：1课程留言 2视频留言
 * @property string $course_id 课程ID
 * @property string $video_id 视频ID
 * @property string $user_id 用户ID
 * @property string $reply 回复ID
 * @property string $content 内容
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CourseMessage extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_message}}';
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
            [['reply', 'created_at', 'updated_at'], 'integer'],
            [['type'], 'string', 'max' => 1],
            [['course_id', 'video_id', 'user_id'], 'string', 'max' => 32],
            [['content'], 'string', 'max' => 500],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'course_id' => Yii::t('app', 'Course ID'),
            'video_id' => Yii::t('app', 'Video ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'reply' => Yii::t('app', 'Reply'),
            'content' => Yii::t('app', 'Content'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
