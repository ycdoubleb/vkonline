<?php

namespace common\models\vk;

use common\components\aliyuncs\Aliyun;
use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
 * @property int $is_del     是否删除：0否 1是
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
    /* 保密 */
    const SEX_NONE = 0;
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
        self::SEX_NONE => '保密',
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
            [['level', 'certicicate_at', 'sex', 'is_certificate', 'is_del', 'created_at', 'updated_at'], 'integer'],
            [['id', 'customer_id', 'created_by'], 'string', 'max' => 32],
            [['name', 'job_title'], 'string', 'max' => 50],
            [['avatar'], 'string', 'max' => 255],
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
            'is_del' => Yii::t('app', 'Is Del'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    public function beforeSave($insert) 
    {
        if (parent::beforeSave($insert)) {
            if (!$this->id) {
                $this->id = md5(time() . rand(1, 99999999));
            }
            //如果更改了认证状态即更新认证时间
            if($this->attributes('is_certificate') != $this->getOldAttribute('is_certificate')){
                $this->certicicate_at = time();
            }
            $upload = UploadedFile::getInstance($this, 'avatar');
            if ($upload != null) {
                //获取后缀名，默认为 png 
                $ext = pathinfo($upload->name,PATHINFO_EXTENSION);
                $img_path = "upload/teacher/avatars/{$this->id}.{$ext}";
                //上传到阿里云
                Aliyun::getOss()->multiuploadFile($img_path, $upload->tempName);
                $this->avatar = $img_path . '?rand=' . rand(0, 9999); 
            }
            if (trim($this->avatar) == '' && $this->isNewRecord){
                //设置默认头像
                $this->avatar = 'upload/teacher/avatars/default/' . ($this->sex == 1 ? 'man' : 'women') . rand(1, 25) . '.jpg';
            } elseif (trim($this->avatar) == '' && !$this->isNewRecord) {
                //更新并且没有修改头像情况即使用旧头像
                $this->avatar = $this->getOldAttribute('avatar');
            }
            $this->des = Html::encode($this->des);
            
            return true;
        }
        return false;
    }
    
    public function afterFind()
    {
        $this->des = Html::decode($this->des);
        $this->avatar = Aliyun::absolutePath(!empty($this->avatar) ? $this->avatar : 'upload/avatars/default.jpg');
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
     * 返回老师信息
     * @param string|array $condition    查询条件
     * @param integer level         等级
     * @param bool $key_to_value    返回键值对形式
     * 
     * @return array(array|Array) 
     */
    public static function getTeacherByLevel($condition, $level = 0, $key_to_value = true) 
    {
        self::$teachers = self::find();
        if(!is_array($condition)){
            self::$teachers->andFilterWhere(['created_by' => $condition, 'level' => $level]);
            self::$teachers->orFilterWhere(['is_certificate' => 1]);
        }else{
            self::$teachers->andFilterWhere($condition);
        }
        self::$teachers->orderBy(['is_certificate' => SORT_DESC]);
        $teachers = [];
        foreach (self::$teachers->all() as $id => $teacher) {
            $teachers[] = $teacher;
        }
       
        return $key_to_value ? ArrayHelper::map($teachers, 'id', 'name') : $teachers;
    }
}
