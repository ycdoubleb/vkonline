<?php

namespace common\models\vk;

use common\components\aliyuncs\Aliyun;
use common\models\User;
use common\models\vk\Customer;
use common\models\vk\Knowledge;
use common\models\vk\TagRef;
use common\models\vk\Teacher;
use common\models\vk\VideoFile;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%video}}".
 *
 * @property string $id
 * @property string $teacher_id 老师ID
 * @property string $customer_id 所属客户ID
 * @property string $user_cat_id 用户目录ID
 * @property string $file_id     文件ID
 * @property string $name 视频名称
 * @property string $duration 时长
 * @property int $is_link 是否为外链：0否 1是
 * @property int $content_level 内容评级：初1 中2 高3
 * @property string $des 视频简介
 * @property int $level 等级：0私有 1内网 2公共
 * @property string $img 图片路径
 * @property int $is_recommend 是否推荐：0否 1是
 * @property int $is_publish 是否发布：0否 1是
 * @property int $is_official 是否为官网资源：0否 1是
 * @property string $zan_count 赞数
 * @property string $favorite_count 收藏数
 * @property int $is_del 是否删除：0否 1是
 * @property int $sort_order 排序
 * @property int $mts_status            是否转码：0未转码 1转码中 2已转码 5转码失败
 * @property int $mts_need              是否需要转码：0否 1是
 * @property string $mts_watermark_ids     水印配置，多个使用逗号分隔
 * @property string $created_by 创建人ID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @property UserCategory $userCategory 获取用户自定义分类
 * @property Customer $customer 获取客户
 * @property User $createdBy 获取创建者
 * @property Teacher $teacher 获取老师
 * @property VideoFile $videoFile     获取视频与实体文件关联表
 * @property TagRef[] $tagRefs 获取标签
 * @property Knowledge[] $knowledges    获取所有知识点
 * @property VideoFile[] $videoFiles     获取所有视频与实体文件关联表
 */
class Video extends ActiveRecord {

    /** 上传工具场景 */
    const SCENARIO_TOOL_UPLOAD = 'toolUpload';
    
    /** 可见范围-公开 */
    const PUBLIC_LEVEL = 2;

    /** 可见范围-内网 */
    const INTRANET_LEVEL = 1;

    /** 可见范围-私有 */
    const PRIVATE_LEVEL = 0;

    /** 发布状态 全部 */
    const ALL_SATUS = '';

    /** 发布状态-未发布 */
    const NO_PUBLISH = 0;

    /** 发布状态-已发布 */
    const YES_PUBLISH = 1;

    /** 未转码 */
    const MTS_STATUS_NO = 0;

    /** 转码中 */
    const MTS_STATUS_DOING = 1;

    /** 已转码 */
    const MTS_STATUS_YES = 2;

    /** 转码失败 */
    const MTS_STATUS_FAIL = 5;

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
        self::ALL_SATUS => '全部',
        self::NO_PUBLISH => '未发布',
        self::YES_PUBLISH => '已发布',
    ];

    /**
     * 转码状态名
     * @var array 
     */
    public static $mtsStatusName = [
        self::MTS_STATUS_NO => '未转码',
        self::MTS_STATUS_DOING => '转码中',
        self::MTS_STATUS_YES => '已转码',
        self::MTS_STATUS_FAIL => '转码失败',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%video}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }

    public function scenarios() {
        return [
            self::SCENARIO_TOOL_UPLOAD =>
            ['id','file_id', 'customer_id', 'name', 'created_at', 'updated_at', 'created_by'],
            self::SCENARIO_DEFAULT =>
            ['id', 'teacher_id', 'customer_id','file_id', 'name', 'duration', 'user_cat_id', 'is_link', 'content_level', 'level', 'is_recommend', 'is_publish', 'is_official', 'zan_count',
                'des', 'favorite_count', 'is_del', 'sort_order', 'created_at', 'updated_at', 'mts_status', 'mts_need', 'created_by', 'img', 'mts_watermark_ids'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['file_id'], 'required',],
            [['user_cat_id'], 'checkUserCategoryType'],
            [['teacher_id'], 'required', 'message' => Yii::t('app', "{MainSpeak}{Teacher}{Can't be empty}", [
                'MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher'),
                "Can't be empty" => Yii::t('app', "Can't be empty.")
            ]), 'on' => [self::SCENARIO_DEFAULT]],
            [['name'], 'required', 'message' => Yii::t('app', "{Video}{Name}{Can't be empty}", [
                'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name'),
                "Can't be empty" => Yii::t('app', "Can't be empty.")
            ])],
            [['duration'], 'number'],
            [['user_cat_id', 'is_link', 'content_level', 'level', 'is_recommend', 'is_publish', 'is_official', 'zan_count',
                'favorite_count', 'is_del', 'sort_order', 'created_at', 'updated_at', 'mts_status', 'mts_need'], 'integer'],
            [['des'], 'string'],
            [['id', 'teacher_id', 'customer_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['img'], 'string', 'max' => 255],
            [['id'], 'unique'],
        ];
    }
    
    /**
     * 检验素材文件是否为共享目录
     * @param string $attribute     user_cat_id
     * @param string $params
     */
    public function checkUserCategoryType($attribute)
    {
        $oldAttribute = $this->getOldAttribute($attribute); 
        $newAttribute = $this->getAttribute($attribute); 
        $oldCategoryModel = UserCategory::getCatById($oldAttribute);
        $newCategoryModel = UserCategory::getCatById($newAttribute);
        if($oldAttribute != null && $newAttribute > 0){
            if($oldCategoryModel->type == UserCategory::TYPE_SHARING && $newCategoryModel->type != UserCategory::TYPE_SHARING){
                $this->addError($attribute, '“共享文件”不能移动到非共享目录下。');  
                return false; 
            }
        }
        return true; 
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'teacher_id' => Yii::t('app', 'Teacher ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'user_cat_id' => Yii::t('app', 'User Cat ID'),
            'name' => Yii::t('app', 'Name'),
            'duration' => Yii::t('app', 'Duration'),
            'is_link' => Yii::t('app', 'Is Link'),
            'content_level' => Yii::t('app', 'Content Level'),
            'des' => Yii::t('app', 'Des'),
            'level' => Yii::t('app', 'Level'),
            'img' => Yii::t('app', 'Img'),
            'is_recommend' => Yii::t('app', 'Is Recommend'),
            'is_publish' => Yii::t('app', 'Is Publish'),
            'is_official' => Yii::t('app', 'Is Official'),
            'zan_count' => Yii::t('app', 'Zan Count'),
            'favorite_count' => Yii::t('app', 'Favorite Count'),
            'is_del' => Yii::t('app', 'Is Del'),
            'mts_status' => Yii::t('app', 'Mts Status'),
            'mts_need' => Yii::t('app', 'Mts Need'),
            'mts_watermark_ids' => Yii::t('app', 'Mts Watermark Ids'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if (!$this->id) {
                $this->id = md5(time() . rand(1, 99999999));
            }
            $upload = UploadedFile::getInstance($this, 'img');
            if ($upload != null) {
                //获取后缀名，默认为 jpg 
                $ext = pathinfo($upload->name,PATHINFO_EXTENSION);
                $user = Yii::$app->user->identity;
                $img_path = "brand/{$user->customer_id}/{$user->id}/{$this->id}.{$ext}";
                //上传到阿里云
                Aliyun::getOss()->multiuploadFile($img_path, $upload->tempName);
                $this->img = $img_path . '?rand=' . rand(0, 9999);
            }
            //都没做修改的情况下保存旧数据
            if (trim($this->img) == '') {
                $this->img = $this->getOldAttribute('img');
            }
            $this->des = Html::encode($this->des);
            
            return true;
        }

        return false;
    }

    public function afterFind() {
        $this->des = Html::decode($this->des);
        $this->img = Aliyun::absolutePath(!empty($this->img) ? $this->img : 'static/imgs/notfound.png');
    }

    /**
     * @return ActiveQuery
     */
    public function getUserCategory()
    {
        return $this->hasOne(UserCategory::class, ['id' => 'user_cat_id']);
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
    public function getCreatedBy() {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return ActiveQuery
     */
    public function getTeacher() {
        return $this->hasOne(Teacher::class, ['id' => 'teacher_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVideoFile() {
        return $this->hasOne(VideoFile::className(), ['video_id' => 'id'])
                        ->where(['is_source' => 1, 'is_del' => 0]);
    }

    /**
     * @return ActiveQuery
     */
    public function getTagRefs() {
        return $this->hasMany(TagRef::class, ['object_id' => 'id'])
                        ->where(['is_del' => 0])->with('tags');
    }

    /**
     * @return ActiveQuery
     */
    public function getKnowledges() {
        return $this->hasMany(Knowledge::className(), ['video_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVideoFiles() {
        return $this->hasMany(VideoFile::className(), ['video_id' => 'id']);
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
