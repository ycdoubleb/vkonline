<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%teacher_certificate}}".
 *
 * @property int $id
 * @property string $teacher_id 认证老师ID
 * @property string $proposer_id 申请人ID
 * @property string $verifier_id 审核人
 * @property string $verifier_at 审核时间
 * @property int $is_pass 是否通过认证：0否 1是
 * @property string $feedback 反馈信息
 * @property int $is_dispose 是否已处理：0否 1
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class TeacherCertificate extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%teacher_certificate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'is_pass', 'is_dispose', 'verifier_at', 'created_at', 'updated_at'], 'integer'],
            [['teacher_id', 'proposer_id', 'verifier_id'], 'string', 'max' => 32],
            [['feedback'], 'string', 'max' => 255],
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
            'teacher_id' => Yii::t('app', 'Teacher ID'),
            'proposer_id' => Yii::t('app', 'Proposer ID'),
            'verifier_id' => Yii::t('app', 'Verifier ID'),
            'verifier_at' => Yii::t('app', 'Verifier At'),
            'is_pass' => Yii::t('app', 'Is Pass'),
            'feedback' => Yii::t('app', 'Feedback'),
            'is_dispose' => Yii::t('app', 'Is Dispose'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
