<?php

namespace common\models\vk;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%teacher}}".
 *
 * @property string $id
 * @property string $name   名称
 * @property int $sex       姓别：0保密 1男 2女
 * @property string $avatar 头像
 * @property int $level     老师等级：0~9
 * @property string $job_title      职称
 * @property string $customer_id    所属客户
 * @property string $des ''
 * @property int $is_certificate    是否认证：0否 1是 
 * @property int $certicicate_at    认证时间 
 * @property string $created_by     创建人
 * @property string $created_at     创建时间
 * @property string $updated_at     更新时间
 * 
 * @property User $createdBy        创建者信息
 * @property Course[] $courses      课程信息
 * @property Customer $customer     客户信息
 */
class Teacher extends ActiveRecord
{
    /** 性别 男 */
    const SEX_MALE = 1;

    /** 性别 女 */
    const SEX_WOMAN = 2;
    
    /** 认证状态 全部 */
    const SATUS_ALL = '';
    /** 认证状态 未认证 */
    const STATUS_NO = 0;
    /** 认证状态 已认证 */
    const STATUS_YES = 1;

    /**
     * 分类[id,name,sex,level,avatar,level,customer_id,des,created_by]
     * @var array
     */
    private static $teachers;
    
    /**
     * 性别
     * @var array 
     */
    public static $sexName = [
        self::SEX_MALE => '男',
        self::SEX_WOMAN => '女',
    ];
    
    /**
     * 认证状态
     * @var array 
     */
    public static $certificateStatus = [
        self::SATUS_ALL => '全部',
        self::STATUS_YES => '已认证',
        self::STATUS_NO => '未认证',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%teacher}}';
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
            [['name', 'sex'], 'required'],
            [['des'], 'string'],
            [['level', 'certicicate_at', 'sex', 'is_certificate', 'created_at', 'updated_at'], 'integer'],
            [['id', 'customer_id', 'created_by'], 'string', 'max' => 32],
            [['name', 'job_title'], 'string', 'max' => 50],
            //[['sex', 'is_certificate'], 'string', 'max' => 1],
            [['avatar'], 'string', 'max' => 255],
            //[['level'], 'string', 'max' => 3],
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
            'name' => Yii::t('app', 'Name'),
            'sex' => Yii::t('app', 'Sex'),
            'avatar' => Yii::t('app', 'Avatar'),
            'level' => Yii::t('app', 'Level'),
            'job_title' => Yii::t('app', 'Job Title'), 
            'customer_id' => Yii::t('app', 'Customer ID'),
            'des' => Yii::t('app', 'Des'),
            'is_certificate' => Yii::t('app', 'Is Certificate'), 
            'certicicate_at' => Yii::t('app', 'Certicicate At'), 
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
            $upload = UploadedFile::getInstance($this, 'avatar');
            if ($upload != null) {
                $string = $upload->name;
                $array = explode('.', $string);
                //获取后缀名，默认为 jpg 
                $ext = count($array) == 0 ? 'jpg' : $array[count($array) - 1];
                $uploadpath = $this->fileExists(Yii::getAlias('@frontend/web/upload/teacher/avatars/'));
                $upload->saveAs($uploadpath . md5($this->name) . '.' . $ext);
                $this->avatar = '/upload/teacher/avatars/' . md5($this->name) . '.' . $ext . '?rand=' . rand(0, 1000);
            }

            if ($this->isNewRecord) {
                //设置默认头像
                if (trim($this->avatar) == ''){
                    $this->avatar = '/upload/teacher/avatars/default/' . ($this->sex == 1 ? 'man' : 'women') . rand(1, 25) . '.jpg';
                }    
            }else {
                if (trim($this->avatar) == ''){
                    $this->avatar = $this->getOldAttribute('avatar');
                }
            }

            return true;
        }
        return false;
    }
    
    /**
     * 关联查询创建者
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
    
    /**
     * 关联查询所有主讲的课程
     * @return ActiveQuery
     */
    public function getCourses()
    {
        return $this->hasMany(Course::class, ['teacher_id' => 'id']);
    }
    
    /**
     * 关联查询所属客户
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
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
    
    /**
     * 查询和自己相关的老师
     * @param array $condition      默认返回键值对形式
     * @param bool $key_to_value    返回键值对形式
     * 
     * @return array(array|Array) 
     */
    public static function getTeacherByLevel($created_by, $level = 0, $key_to_value = true) 
    {
        self::$teachers = self::find()
            ->where(['created_by' => $created_by, 'level' => $level])
            ->orderBy(['is_certificate' => SORT_DESC])->all();
        $teachers = [];
        foreach (self::$teachers as $id => $teacher) {
            $teachers[] = $teacher;
        }
       
        return $key_to_value ? ArrayHelper::map($teachers, 'id', 'name') : $teachers;
    }
}
