<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%recent_contacts}}".
 *
 * @property int $id
 * @property string $user_id 用户ID
 * @property string $contacts_id 联系人ID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class RecentContacts extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%recent_contacts}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['user_id', 'contacts_id'], 'string', 'max' => 32],
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
            'user_id' => Yii::t('app', 'User ID'),
            'contacts_id' => Yii::t('app', 'Contacts ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
