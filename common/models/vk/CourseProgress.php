<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_progress}}".
 *
 * @property string $id
 * @property string $course_id 课程ID
 * @property string $user_id 用户ID
 * @property string $last_video 上次学习的视频
 * @property int $is_finish 是否完成：0否 1是
 * @property string $start_time 开始学习时间
 * @property string $end_time 结束学习时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CourseProgress extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_progress}}';
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
            [['is_finish', 'start_time', 'end_time', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'user_id', 'last_video'], 'string', 'max' => 32],
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
            'user_id' => Yii::t('app', 'User ID'),
            'last_video' => Yii::t('app', 'Last Video'),
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
     * 获取课程下的进度
     * @param string $courseId  课程id
     * @param boolean $default  默认返回查询参与课程的在学人数
     * @return Query
     */
    public static function getCourseProgressByCourseId($courseId, $default = true)
    {
        //查询课程的进度
        $progress = self::find()->select(['Progress.course_id'])
            ->from(['Progress' => CourseProgress::tableName()]);
        //必要条件查询
        $progress->where(['Progress.course_id' => $courseId]);
        //以课程id为分组
        $progress->groupBy('Progress.course_id');
        //以id为排序
        $progress->orderBy('Progress.id');
        
        if($default){
            //查询参与课程的在学人数
            $progress->addSelect(['COUNT(Progress.user_id) AS people_num']);
        }
        
        return $progress;
    }
}
