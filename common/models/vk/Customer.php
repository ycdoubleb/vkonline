<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%customer}}".
 *
 * @property string $id
 * @property string $name 名称
 * @property string $domain 域名，不带http
 * @property string $logo logo
 * @property int $status 状态：0停用 1试用 10 正常
 * @property string $des 描述
 * @property string $expire_time 到期时间
 * @property string $renew_time 上次续费时间
 * @property string $good_id 套餐ID
 * @property string $invite_code 邀请码
 * @property string $province 省
 * @property string $city 市
 * @property string $district 区
 * @property string $twon 镇
 * @property string $address 详细地址
 * @property string $location 位置
 * @property string $created_by 创建人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Customer extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
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
            [['expire_time', 'renew_time', 'good_id', 'province', 'city', 'district', 'twon', 'address', 'created_at', 'updated_at'], 'integer'],
            [['location'], 'string'],
            [['id', 'created_by'], 'string', 'max' => 32],
            [['name', 'domain', 'logo', 'des'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 1],
            [['invite_code'], 'string', 'max' => 6],
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
            'domain' => Yii::t('app', 'Domain'),
            'logo' => Yii::t('app', 'Logo'),
            'status' => Yii::t('app', 'Status'),
            'des' => Yii::t('app', 'Des'),
            'expire_time' => Yii::t('app', 'Expire Time'),
            'renew_time' => Yii::t('app', 'Renew Time'),
            'good_id' => Yii::t('app', 'Good ID'),
            'invite_code' => Yii::t('app', 'Invite Code'),
            'province' => Yii::t('app', 'Province'),
            'city' => Yii::t('app', 'City'),
            'district' => Yii::t('app', 'District'),
            'twon' => Yii::t('app', 'Twon'),
            'address' => Yii::t('app', 'Address'),
            'location' => Yii::t('app', 'Location'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
