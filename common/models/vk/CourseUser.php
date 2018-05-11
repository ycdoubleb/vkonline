<?php

namespace common\models\vk;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_user}}".
 *
 * @property int $id
 * @property string $course_id 课程ID
 * @property string $user_id 用户ID
 * @property int $privilege 权限：1只读，2编辑，10所有权
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property Course $course 获取课程
 * @property User $user 获取用户
 */
class CourseUser extends ActiveRecord
{
    /** 只读权限 */
    const READONLY = 1;
    /** 编辑权限 */
    const EDIT = 2;
    /** 全部权限 */
    const ALL = 10;
    
    /**
     * 权限名称
     * @var  array
     */
    public static $privilegeMap = [
        self::READONLY => '只读',
        self::EDIT => '编辑',
        self::ALL => '全部',
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_user}}';
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
            //[['id'], 'required'],
            [['user_id'], 'required', 'message' => Yii::t('app', "{helpMan}{Can't be empty}", [
                'helpMan' => Yii::t('app', 'Help Man'), "Can't be empty" => \Yii::t('app', "Can't be empty.")
            ])],
            [['privilege', 'created_at', 'updated_at'], 'integer'],
            [['course_id'], 'string', 'max' => 32],
            //[['user_id'], 'array'],
            //[['privilege'], 'string', 'max' => 2],
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
            'course_id' => Yii::t('app', 'Course ID'),
            'user_id' => Yii::t('app', '{User}{ID}', ['User' => \Yii::t('app', 'User'), 'ID' => \Yii::t('app', 'ID')]),
            'privilege' => Yii::t('app', 'Privilege'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
