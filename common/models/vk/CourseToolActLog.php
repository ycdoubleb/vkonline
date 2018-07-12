<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_tool_act_log}}".
 *
 * @property string $id
 * @property string $tool_user_id 制作工具用户ID
 * @property string $act 动作类型
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CourseToolActLog extends ActiveRecord
{
    /* 登录 */
    const ACT_LOGIN = 'Login';
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%course_tool_act_log}}';
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
            [['tool_user_id', 'created_at', 'updated_at'], 'integer'],
            [['act'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'tool_user_id' => Yii::t('app', '制作工具用户ID'),
            'act' => Yii::t('app', '动作类型'),
            'created_at' => Yii::t('app', '创建时间'),
            'updated_at' => Yii::t('app', '更新时间'),
        ];
    }
}
