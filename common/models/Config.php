<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%config}}".
 *
 * @property string $id
 * @property string $config_name        配置名
 * @property string $config_value       配置值
 * @property string $des                描述
 * @property string $created_at         创建时间
 * @property string $updated_at         更新时间
 */
class Config extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%config}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['config_name', 'config_value'], 'required'],
            [['config_value', 'des'], 'string'],
            [['created_at', 'updated_at'], 'integer'],
            [['config_name'], 'string', 'max' => 255],
            [['config_name'], 'unique'],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'config_name' => Yii::t('app', 'Config Name'),
            'config_value' => Yii::t('app', 'Config Value'),
            'des' => Yii::t('app', 'Des'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
