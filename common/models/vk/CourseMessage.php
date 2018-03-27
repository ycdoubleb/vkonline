<?php

namespace common\models\vk;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
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
 * 
 * @property Course $course 获取课程
 * @property Video $video 获取视频
 * @property User $user 获取用户
 */
class CourseMessage extends ActiveRecord
{
    /** 课程留言 */
    const COURSE_TYPE = 1;
    /** 视频留言 */
    const VIDEO_TYPE = 2;

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
            [['type', 'reply', 'created_at', 'updated_at'], 'integer'],
            //[['type'], 'string', 'max' => 1],
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
    
    /**
     * 
     * @param type $insert 
     */
    public function beforeSave($insert) 
    {
        if(parent::beforeSave($insert))
        {
            if($this->isNewRecord){
                $this->user_id = Yii::$app->user->id;
            }
            
            return true;
        }else
            return false;
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getVideo()
    {
        return $this->hasOne(Video::class, ['id' => 'video_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
