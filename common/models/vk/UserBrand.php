<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%user_brand}}".
 *
 * @property string $id
 * @property string $user_id 用户ID
 * @property string $brand_id 用户
 * @property int $is_del 是否已经删除 0否 1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class UserBrand extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_brand}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['is_del', 'created_at', 'updated_at'], 'integer'],
            [['user_id', 'brand_id'], 'string', 'max' => 32],
            [['user_id', 'brand_id'], 'unique', 'targetAttribute' => ['user_id', 'brand_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'brand_id' => Yii::t('app', 'Brand ID'),
            'is_del' => Yii::t('app', 'Is Del'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
