<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_brand}}".
 *
 * @property string $id
 * @property string $user_id    用户ID
 * @property string $brand_id   用户
 * @property int $is_del        是否已经删除 0否 1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property Customer $customer 客户
 */
class UserBrand extends ActiveRecord {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%user_brand}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['is_del', 'created_at', 'updated_at'], 'integer'],
            [['user_id', 'brand_id'], 'string', 'max' => 32],
            [['user_id', 'brand_id'], 'unique', 'targetAttribute' => ['user_id', 'brand_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'brand_id' => Yii::t('app', 'Brand ID'),
            'is_del' => Yii::t('app', 'Is Del'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * 关联获取所属客户
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'brand_id']);
    }
    
    /**
     * 保存or删除关联品牌
     * @param string $user_id
     * @param string $customer_id
     * @param integer $is_add
     * @return type
     */
    public static function userBingding($user_id, $customer_id, $is_add = true) {
        //是否已保存关联
        $userBrand = UserBrand::findOne(['user_id' => $user_id, 'brand_id' => $customer_id]);

        //如果未发现关联品牌 并且是要增加的情况
        if ($userBrand == null && $is_add) {
            $userBrand = new UserBrand(['user_id' => $user_id, 'brand_id' => $customer_id]);
        }

        if ($is_add) {
            $userBrand->is_del = 0;
        } elseif ($userBrand != null) {
            $userBrand->is_del = 1;
        }

        return $userBrand == null ? true : $userBrand->save();
    }

}
