<?php

namespace common\models\vk;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;


/**
 * This is the model class for table "{{%course}}".
 *
 * @property string $id             课程ID
 * @property string $customer_id    客户ID
 * @property string $category_id    所属分类
 * @property string $teacher_id     主讲老师
 * @property string $name           课程名称
 * @property int $level             级别：0私有 1内网 2公开
 * @property string $des            课程简介
 * @property string $cover_img      封面
 * @property int $is_recommend      是否推荐：0否 1是
 * @property int $is_publish        是否发布：0否 1是
 * @property string $zan_count      赞
 * @property string $favorite_count 收藏数
 * @property string $created_by     创建人
 * @property int $is_official       是否为官网资源：0否 1是
 * @property string $created_at     创建时间
 * @property string $updated_at     更新时间
 * 
 * @property Customer $customer     获取客户
 * @property Category $category     获取分类
 * @property User $createdBy        获取创建者
 * @property Teacher $teacher       获取老师
 */
class Course extends ActiveRecord
{
    /** 可见范围-公开 */
    const PUBLIC_LEVEL = 2;
    /** 可见范围-内网 */
    const INTRANET_LEVEL = 1;
    /** 可见范围-私有 */
    const PRIVATE_LEVEL = 0;
    
    /** 发布状态-未发布 */
    const NO_PUBLISH = 0;
    /** 发布状态-已发布 */
    const YES_PUBLISH = 1;
    
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
     * 发布状态
     * @var array 
     */
    public static $publishStatus = [
        self::NO_PUBLISH => '未发布',
        self::YES_PUBLISH => '已发布',
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
            TimestampBehavior::class
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
            [['category_id', 'level', 'is_recommend', 'is_publish', 'zan_count', 'favorite_count', 
                'is_official', 'created_at', 'updated_at'], 'integer'],
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
            'is_official' => Yii::t('app', 'Is Official'), 
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
            $upload = UploadedFile::getInstance($this, 'cover_img');
            if ($upload != null) {
                $string = $upload->name;
                $array = explode('.', $string);
                //获取后缀名，默认为 png 
                $ext = count($array) == 0 ? 'png' : $array[count($array) - 1];
                $uploadpath = $this->fileExists(Yii::getAlias('@frontend/web/upload/course/cover_imgs/'));
                $upload->saveAs($uploadpath . $this->id . '.' . $ext);
                $this->cover_img = '/upload/course/cover_imgs/' . $this->id . '.' . $ext . '?rand=' . rand(0, 1000);
            }

            if ($this->isNewRecord) {
                //设置默认
                if (trim($this->cover_img) == ''){
                    $this->cover_img = '';
                }
                //保存自己为协作人
                $model = new CourseUser(['course_id' => $this->id, 
                    'user_id' => $this->created_by, 'privilege' => CourseUser::ALL
                ]);
                $model->save();
            }else {
                if (trim($this->cover_img) == ''){
                    $this->cover_img = $this->getOldAttribute('cover_img');
                }
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getTeacher()
    {
        return $this->hasOne(Teacher::class, ['id' => 'teacher_id']);
    }
    
    /**
     * 检查目标路径是否存在，不存即创建目标
     * @param string $uploadpath    目录路径
     * @return string
     */
    protected function fileExists($uploadpath) {

        if (!file_exists($uploadpath)) {
            mkdir($uploadpath);
        }
        return $uploadpath;
    }
}
