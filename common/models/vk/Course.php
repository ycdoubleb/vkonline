<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
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
 */
class Course extends ActiveRecord
{
    //未发布
    const STATUS_NO = 0;
    //已发布
    const STATUS_YES = 1;
    
    //私有
    const LEVEL_ZERO = 0;
    //内网
    const LEVEL_ONE = 1;
    //公开
    const LEVEL_TWO = 2;

    
    /**
     * 发布状态
     * @var array 
     */
    public static $satusPublish = [
        self::STATUS_NO => '未发布',
        self::STATUS_YES => '已发布',
    ];

    /**
     * 发布的位置
     * @var array
     */
    public static $levelStatus = [
        self::LEVEL_ZERO => '私有',
        self::LEVEL_ONE => '内网',
        self::LEVEL_TWO => '公开',
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
            [['id'], 'required'],
            [['category_id', 'zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
            [['id', 'customer_id', 'teacher_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['level', 'is_recommend', 'is_publish'], 'string', 'max' => 1],
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
            'category_id' => Yii::t('app', 'Category ID'),
            'teacher_id' => Yii::t('app', 'Teacher ID'),
            'name' => Yii::t('app', 'Name'),
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
}
