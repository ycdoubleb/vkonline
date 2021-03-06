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
 * @property string $knowledge_id 知识点ID
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
            [['course_id', 'knowledge_id'], 'string', 'max' => 32],
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
            'knowledge_id' => Yii::t('app', 'Knowledge ID'),
            'play_count' => Yii::t('app', 'Play Count'),
        ];
    }
    
    /**
     * 获取对象的播放统计
     * @param string|array $condition   查询条件
     * @param string $objectIdName  对象id名称
     * @param boolean $default  默认查询播放量
     * @return Query
     */
    public static function getObjectPlayStatistics($condition, $objectIdName = '', $default = true)
    {
        //查询对象的播放统计
        $play = self::find()->select(['Play.id'])
            ->from(['Play' => PlayStatistics::tableName()]);
        //条件查询
        $play->where($condition);
        //对象id非空的情况下
        if($objectIdName != ''){
            $play->groupBy("Play.{$objectIdName}");     //以objectId为分组
            $play->select(["Play.{$objectIdName}"]);
        }
        //默认情况下
        if($default){
            $play->addSelect(['SUM(Play.play_count) AS play_num']); //查询播放量
        }
        
        return $play;
    }
}
