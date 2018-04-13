<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%teacher}}".
 *
 * @property string $id
 * @property string $name 名称
 * @property int $sex 姓别：0保密 1男 2女
 * @property string $avatar 头像
 * @property int $level 老师等级：0~9
 * @property string $customer_id 所属客户
 * @property string $des ''
 * @property string $created_by 创建人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Teacher extends ActiveRecord
{
    /** 性别 男 */
    const SEX_MALE = 1;

    /** 性别 女 */
    const SEX_WOMAN = 2;

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
            [['name', 'sex'], 'required'],
            [['des'], 'string'],
            [['level', 'created_at', 'updated_at'], 'integer'],
            [['id', 'customer_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['sex'], 'string', 'max' => 1],
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
            'customer_id' => Yii::t('app', 'Customer ID'),
            'des' => Yii::t('app', 'Des'),
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
     * 获取所有老师
     * @param array $condition      默认返回键值对形式
     * @param bool $key_to_value    返回键值对形式
     * 
     * @return array(array|Array) 
     */
    public static function getTeacherByLevel($customer_id, $level = 0, $key_to_value = true) 
    {
        self::$teachers = self::findAll(['customer_id' => $customer_id, 'level' => $level]);
        $teachers = [];
        foreach (self::$teachers as $id => $teacher) {
            $teachers[] = $teacher;
        }
       
        return $key_to_value ? ArrayHelper::map($teachers, 'id', 'name') : $teachers;
    }
}
