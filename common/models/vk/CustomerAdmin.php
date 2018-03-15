<?php

namespace common\models\vk;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%customer_admin}}".
 *
 * @property string $id
 * @property string $customer_id 客户ID
 * @property string $user_id 用户id
 * @property int $level 等级：1主 2副
 * @property string $created_by 创建人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property User $user         关联用户
 */
class CustomerAdmin extends ActiveRecord
{
    /** 主管理员 */
    const MAIN = 1;
    /** 副管理员 */
    const VICE = 2;
    
    /**
     * 管理员等级
     * @var  array
     */
    public static $levelName = [
        self::MAIN => '主管理员',
        self::VICE => '副管理员',
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_admin}}';
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
            [['created_at', 'updated_at'], 'integer'],
            [['customer_id', 'user_id', 'created_by'], 'string', 'max' => 32],
            [['level'], 'string', 'max' => 1],
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
            'user_id' => Yii::t('app', 'User ID'),
            'level' => Yii::t('app', 'Level'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
}
