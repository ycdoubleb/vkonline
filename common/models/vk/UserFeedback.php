<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%user_feedback}}".
 *
 * @property string $id
 * @property string $user_id 反馈人ID
 * @property string $customer_id 客户ID
 * @property int $type 问题类型：1课程建议 2功能建议 3程序错误 9其他问题
 * @property string $content 反馈内容
 * @property string $contact 联系方式：邮箱地址、电话等
 * @property int $is_process 是否已处理：0否 1是
 * @property string $processer 处理人ID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class UserFeedback extends \yii\db\ActiveRecord
{
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
    public function rules()
    {
        return [
            [['content'], 'string'],
            [['type', 'is_process',  'created_at', 'updated_at'], 'integer'],
            [['user_id', 'customer_id', 'processer'], 'string', 'max' => 32],
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
            'user_id' => Yii::t('app', 'User ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'type' => Yii::t('app', 'Type'),
            'content' => Yii::t('app', 'Content'),
            'contact' => Yii::t('app', 'Contact'),
            'is_process' => Yii::t('app', 'Is Process'),
            'processer' => Yii::t('app', 'Processer'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
