<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_tool_user}}".
 *
 * @property string $id
 * @property string $user_id 用户ID
 * @property string $type 工具名
 * @property string $open_id 工具账号ID
 * @property string $access_token 验证口令
 * @property int $is_del 是否删除：0否 1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CourseToolUser extends ActiveRecord
{
    /* CourseMaker */
    const COURSEMAKER = 'CourseMaker';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%course_tool_user}}';
    }
    
    public function behaviors() {
        return [TimestampBehavior::class];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_del', 'created_at', 'updated_at'], 'integer'],
            [['user_id'], 'string', 'max' => 32],
            [['type', 'open_id', 'access_token'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', '用户ID'),
            'type' => Yii::t('app', '工具名'),
            'open_id' => Yii::t('app', '工具账号ID'),
            'access_token' => Yii::t('app', '验证口令'),
            'is_del' => Yii::t('app', '是否删除：0否 1是'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
        ];
    }
}
