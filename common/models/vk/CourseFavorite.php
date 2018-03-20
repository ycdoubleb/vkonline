<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_favorite}}".
 *
 * @property string $id
 * @property string $course_id 课程ID
 * @property string $user_id 用户ID
 * @property string $group 分组
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CourseFavorite extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_favorite}}';
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
            [['created_at', 'updated_at'], 'integer'],
            [['course_id', 'user_id'], 'string', 'max' => 32],
            [['group'], 'string', 'max' => 50],
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
            'group' => Yii::t('app', 'Group'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * 查询课程关注
     * @param array $condition  (Favorite.name => value)
     * @return Query
     */
    public static function findCourseFavorite($condition)
    {
        $query = self::find()->select(['COUNT(Favorite.id) AS fav_num'])
            ->addSelect(implode(",", array_keys($condition)))
            ->from(['Favorite' => self::tableName()]);
        
        $query->where($condition);
        
        $query->groupBy(array_keys($condition));
        
        return $query;
    }
}
