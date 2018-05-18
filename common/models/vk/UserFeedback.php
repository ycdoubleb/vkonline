<?php

namespace common\models\vk;

use common\models\AdminUser;
use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_feedback}}".
 *
 * @property string $id
 * @property string $user_id        反馈人ID
 * @property string $customer_id    客户ID
 * @property int $type              问题类型：1课程建议 2功能建议 3程序错误 9其他问题
 * @property string $content        反馈内容
 * @property string $contact        联系方式：邮箱地址、电话等
 * @property int $is_process        是否已处理：0否 1是
 * @property string $processer_id   处理人ID
 * @property string $created_at     创建时间
 * @property string $updated_at     更新时间
 * 
 * @property Customer $customer     获取客户
 * @property User $user             获取反馈人
 * @property AdminUser $processer   获取处理人
 */
class UserFeedback extends ActiveRecord
{
    /** 反馈类型 课程建议 */
    const TYPE_ONE = 1;
    /** 反馈类型 功能建议 */
    const TYPE_TWO = 2;
    /** 反馈类型 程序错误 */
    const TYPE_THREE = 3;
    /** 反馈类型 其他问题 */
    const TYPE_NINE = 9;
    
    /**
     * 反馈类型
     * @var array 
     */
    public static $feedbackType = [
        self::TYPE_ONE => '课程建议',
        self::TYPE_TWO => '功能建议',
        self::TYPE_THREE => '程序错误',
        self::TYPE_NINE => '其他问题',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_feedback}}';
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors() 
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'content'], 'required'],
            [['content'], 'string'],
            [['type', 'is_process',  'created_at', 'updated_at'], 'integer'],
            [['user_id', 'customer_id', 'processer_id'], 'string', 'max' => 32],
            [['contact'], 'string', 'max' => 50],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'Feedbacker'),
            'customer_id' => Yii::t('app', 'Customer'),
            'type' => Yii::t('app', 'Type'),
            'content' => Yii::t('app', 'Content'),
            'contact' => Yii::t('app', 'Contact'),
            'is_process' => Yii::t('app', '{Is}{Solve}',['Is' => Yii::t('app', 'Is'), 'Solve' => Yii::t('app', 'Solve')]),
            'processer_id' => Yii::t('app', 'Processer'),
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
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
    
    /**
     * 关联获取反馈人
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
    
    /**
     * 关联获取处理人
     * @return ActiveQuery
     */
    public function getProcesser()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'processer_id']);
    }
}
