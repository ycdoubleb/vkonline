<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_user}}".
 *
 * @property int $id
 * @property string $course_id 课程ID
 * @property string $user_id 用户ID
 * @property int $privilege 权限：1只读，2编辑，10所有权
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CourseUser extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_user}}';
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
            [['id'], 'required'],
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'user_id'], 'string', 'max' => 32],
            [['privilege'], 'string', 'max' => 2],
            [['id'], 'unique'],
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
            'privilege' => Yii::t('app', 'Privilege'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
