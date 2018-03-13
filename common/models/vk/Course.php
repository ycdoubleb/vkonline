<?php

namespace common\models\vk;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;


/**
 * This is the model class for table "{{%course}}".
 *
 * @property string $id 课程ID
 * @property string $customer_id 客户ID
 * @property string $category_id 所属分类
 * @property string $teacher_id 主讲老师
 * @property string $name 课程名称
 * @property int $level 级别：0私有 1内网 2公开
 * @property string $des 课程简介
 * @property string $cover_img 封面
 * @property int $is_recommend 是否推荐：0否 1是
 * @property int $is_publish 是否发布：0否 1是
 * @property string $zan_count 赞
 * @property string $favorite_count 收藏数
 * @property string $created_by 创建人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property Customer $customer 获取客户
 * @property Category $category 获取分类
 * @property User $createdBy 获取创建者
 * @property Teacher $teacher 获取老师
 */
class Course extends ActiveRecord
{
    /** 可见范围-公开 */
    const PUBLIC_LEVEL = 2;
    /** 可见范围-内网 */
    const INTRANET_LEVEL = 1;
    /** 可见范围-私有 */
    const PRIVATE_LEVEL = 0;
    
    /**
     * 可见范围
     * @var array 
     */
    public static $levelMap = [
        self::PUBLIC_LEVEL => '公开',
        self::INTRANET_LEVEL => '内网',
        self::PRIVATE_LEVEL => '私有',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course}}';
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
            [['category_id', 'name', 'teacher_id'], 'required'],
            [['category_id', 'level', 'is_recommend', 'is_publish', 'zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
            [['id', 'customer_id', 'teacher_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            //[['level', 'is_recommend', 'is_publish'], 'string', 'max' => 1],
            [['des'], 'string', 'max' => 500],
            [['cover_img'], 'string', 'max' => 255],
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
            'customer_id' => Yii::t('app', 'Customer ID'),
            'category_id' => Yii::t('app', '{Course}{Category}', ['Course' => Yii::t('app', 'Course'), 'Category' => Yii::t('app', 'Category')]),
            'teacher_id' => Yii::t('app', '{MainSpeak}{Teacher}', ['MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')]),
            'name' => Yii::t('app', '{Course}{Name}', ['Course' => Yii::t('app', 'Course'), 'Name' => Yii::t('app', 'Name')]),
            'level' => Yii::t('app', 'Level'),
            'des' => Yii::t('app', 'Des'),
            'cover_img' => Yii::t('app', 'Cover Img'),
            'is_recommend' => Yii::t('app', 'Is Recommend'),
            'is_publish' => Yii::t('app', 'Is Publish'),
            'zan_count' => Yii::t('app', 'Zan Count'),
            'favorite_count' => Yii::t('app', 'Favorite Count'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    public function beforeSave($insert) 
    {
        if (!$this->id) {
            $this->id = md5(time() . rand(1, 99999999));
        }
        
        if (parent::beforeSave($insert)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getTeacher()
    {
        return $this->hasOne(Teacher::className(), ['id' => 'teacher_id']);
    }
}
