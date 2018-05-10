<?php

namespace common\models\vk;

use common\models\AdminUser;
use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%teacher_certificate}}".
 *
 * @property int $id
 * @property string $teacher_id     认证老师ID
 * @property string $proposer_id    申请人ID
 * @property string $verifier_id    审核人
 * @property string $verifier_at    审核时间
 * @property int $is_pass           是否通过认证：0否 1是
 * @property string $feedback       反馈信息
 * @property int $is_dispose        是否已处理：0否 1
 * @property string $created_at     创建时间
 * @property string $updated_at     更新时间
 * 
 * @property Teacher $teacher       老师信息
 * @property User $proposer         申请人信息
 * @property AdminUser $verifier    审核人信息
 */
class TeacherCertificate extends ActiveRecord
{
    /** 是否通过-未通过 */
    const NO_PASS = 0;
    /** 是否通过-通过 */
    const YES_PPASS = 1;
    
    /**
     * 是否通过
     * @var array 
     */
    public static $passStatus = [
        self::NO_PASS => '未通过',
        self::YES_PPASS => '通过',
    ];
    
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
    
    /**
     * 关联查询老师
     * @return ActiveQuery
     */
    public function getTeacher()
    {
        return $this->hasOne(Teacher::class, ['id' => 'teacher_id']);
    }
    
    /**
     * 关联查询申请人
     * @return ActiveQuery
     */
    public function getProposer()
    {
        return $this->hasOne(User::class, ['id' => 'proposer_id']);
    }
    
    /**
     * 关联查询审核人
     * @return ActiveQuery
     */
    public function getVerifier()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'verifier_id']);
    }
}
