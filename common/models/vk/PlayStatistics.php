<?php

namespace common\models\vk;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%play_statistics}}".
 *
 * @property string $id
 * @property string $year 年份
 * @property string $month 月份
 * @property string $course_id 课程ID
 * @property string $video_id 视频ID
 * @property string $play_count 播放次数
 */
class PlayStatistics extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%play_statistics}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year', 'month', 'play_count'], 'integer'],
            [['course_id', 'video_id'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'year' => Yii::t('app', 'Year'),
            'month' => Yii::t('app', 'Month'),
            'course_id' => Yii::t('app', 'Course ID'),
            'video_id' => Yii::t('app', 'Video ID'),
            'play_count' => Yii::t('app', 'Play Count'),
        ];
    }
}
