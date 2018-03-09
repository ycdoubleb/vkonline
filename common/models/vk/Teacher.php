<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%teacher}}".
 *
 * @property string $id
 * @property string $name 名称
 * @property int $sex 姓别：0保密 1男 2女
 * @property string $avatar 头像
 * @property int $level 老师等级：0~9
 * @property string $customer_id 所属客户
 * @property string $des ''
 * @property string $created_by 创建人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Teacher extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%teacher}}';
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
            [['des'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['id', 'customer_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['sex'], 'string', 'max' => 1],
            [['avatar'], 'string', 'max' => 255],
            [['level'], 'string', 'max' => 3],
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
            'name' => Yii::t('app', 'Name'),
            'sex' => Yii::t('app', 'Sex'),
            'avatar' => Yii::t('app', 'Avatar'),
            'level' => Yii::t('app', 'Level'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'des' => Yii::t('app', 'Des'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
