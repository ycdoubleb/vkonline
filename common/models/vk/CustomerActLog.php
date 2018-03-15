<?php

namespace common\models\vk;

use common\models\AdminUser;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%customer_act_log}}".
 *
 * @property string $id
 * @property string $customer_id 客户ID
 * @property string $title 标题
 * @property string $good_id 套餐id
 * @property string $content ''
 * @property string $start_time 开始时间
 * @property string $end_time 到期时间
 * @property string $created_by 操作人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property Good $good             关联套餐
 * @property User $user             关联用户
 * @property Customer $customer     关联客户
 */
class CustomerActLog extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_act_log}}';
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
            [['good_id', 'start_time', 'end_time', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string'],
            [['created_by'], 'required'],
            [['customer_id', 'created_by'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'title' => Yii::t('app', 'Title'),
            'good_id' => Yii::t('app', 'Good ID'),
            'content' => Yii::t('app', 'Content'),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * @return ActiveQuery
     */
    public function getGood()
    {
//        return $this->hasOne(Good::class, ['id' => 'good_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getAdminUser()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'created_by']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}
