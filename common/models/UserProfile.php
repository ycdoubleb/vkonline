<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_profile}}".
 *
 * @property int $profile_id
 * @property string $user_id 用户ID
 * @property string $province 省
 * @property string $city 市
 * @property string $district 区
 * @property string $twon 值
 * @property string $address 详细地址
 * @property string $job_title 职称
 * @property string $level 等级
 * @property string $company 公司
 * @property string $sign 签名
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class UserProfile extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_profile}}';
    }
    
    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['profile_id', 'province', 'city', 'district', 'twon', 'level', 'created_at', 'updated_at'], 'integer'],
            [['user_id'], 'string', 'max' => 32],
            [['address', 'job_title', 'company', 'sign'], 'string', 'max' => 255],
            [['profile_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'profile_id' => Yii::t('app', 'Profile ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'province' => Yii::t('app', 'Province'),
            'city' => Yii::t('app', 'City'),
            'district' => Yii::t('app', 'District'),
            'twon' => Yii::t('app', 'Twon'),
            'address' => Yii::t('app', 'Address'),
            'job_title' => Yii::t('app', 'Job Title'),
            'level' => Yii::t('app', 'Level'),
            'company' => Yii::t('app', 'Company'),
            'sign' => Yii::t('app', 'Sign'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
