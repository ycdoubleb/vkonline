<?php

namespace common\models\vk;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%video_progress}}".
 *
 * @property string $id
 * @property string $course_id 课程ID
 * @property string $video_id 视频ID
 * @property string $user_id 用户ID
 * @property string $last_time 最后看到的时间点
 * @property string $finish_time 已经完成的时间
 * @property int $is_finish 是否已经完成：0否 1是
 * @property string $start_time 开始时间
 * @property string $end_time 结束时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property Course $course 获取课程
 * @property Video $video 获取课程
 * @property User $user 获取用户
 */
class VideoProgress extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%video_progress}}';
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
            [['last_time', 'finish_time', 'is_finish', 'start_time', 'end_time', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'video_id', 'user_id'], 'string', 'max' => 32],
            //[['is_finish'], 'string', 'max' => 1],
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
            'video_id' => Yii::t('app', 'Video ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'last_time' => Yii::t('app', 'Last Time'),
            'finish_time' => Yii::t('app', 'Finish Time'),
            'is_finish' => Yii::t('app', 'Is Finish'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    public function beforeSave($insert) 
    {
        if (parent::beforeSave($insert)) {
            if($this->isNewRecord){
                $this->start_time = time();
            }
            return true;
        }
        
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
