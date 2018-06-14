<?php

namespace common\models\vk;

use common\models\User;
use common\models\vk\Customer;
use common\models\vk\Knowledge;
use common\models\vk\TagRef;
use common\models\vk\Teacher;
use common\models\vk\VideoFile;
use common\modules\webuploader\models\Uploadfile;
use common\utils\StringUtil;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\web\UploadedFile;


/**
 * This is the model class for table "{{%video}}".
 *
 * @property string $id
 * @property string $teacher_id 老师ID
 * @property string $customer_id 所属客户ID
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
 * @property string $created_by 创建人ID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @property Customer $customer 获取客户
 * @property User $createdBy 获取创建者
 * @property Teacher $teacher 获取老师
 * @property VideoFile $videoFile     获取视频与实体文件关联表
 * @property TagRef[] $tagRefs 获取标签
 * @property Knowledge[] $knowledges    获取所有知识点
 * @property VideoFile[] $videoFiles     获取所有视频与实体文件关联表
 */
class Video extends ActiveRecord
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
        return '{{%video}}';
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
            [['teacher_id'], 'required', 'message' => Yii::t('app', "{MainSpeak}{Teacher}{Can't be empty}", [
                'MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher'),
                "Can't be empty" => Yii::t('app', "Can't be empty.")
            ])],
            [['name'], 'required', 'message' => Yii::t('app', "{Video}{Name}{Can't be empty}", [
                'Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name'),
                "Can't be empty" => Yii::t('app', "Can't be empty.")
            ])],
            [['duration'], 'number'],
            [['is_link', 'content_level', 'level', 'is_recommend', 'is_publish', 'is_official', 'zan_count', 'favorite_count', 'is_del', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['des'], 'string'],
            [['id', 'teacher_id', 'customer_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['img'], 'string', 'max' => 255],
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
            'customer_id' => Yii::t('app', 'Customer ID'),
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
            'sort_order' => Yii::t('app', 'Sort Order'),
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
            $upload = UploadedFile::getInstance($this, 'img');
            if ($upload != null) {
                $string = $upload->name;
                $array = explode('.', $string);
                //获取后缀名，默认为 jpg 
                $ext = count($array) == 0 ? 'jpg' : $array[count($array) - 1];
                $uploadpath = $this->fileExists(Yii::getAlias('@frontend/web/upload/video/screenshots/'));
                $upload->saveAs($uploadpath . $this->id . '.' . $ext);
                $this->img = '/upload/video/screenshots/' . $this->id . '.' . $ext . '?rand=' . rand(0, 1000);
            }
            //都没做修改的情况下保存旧数据
            if(trim($this->img) == ''){
                $this->img = $this->getOldAttribute('img');
            }
            $this->des = htmlentities($this->des);
            
            return true;
        }
        
        return false;
    }
    
    public function afterFind()
    {
        $this->des = html_entity_decode($this->des);
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
     * @return ActiveQuery
     */
    public function getVideoFile()
    {
        return $this->hasOne(VideoFile::className(), ['video_id' => 'id'])
            ->where(['is_source' => 1, 'is_del' => 0]);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getTagRefs()
    {
        return $this->hasMany(TagRef::class, ['object_id' => 'id'])->with('tags');
    }
    
    /**
     * @return ActiveQuery
     */
    public function getKnowledges()
    {
        return $this->hasMany(Knowledge::className(), ['video_id' => 'id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getVideoFiles()
    {
        return $this->hasMany(VideoFile::className(), ['video_id' => 'id']);
    }
            
    /**
     * 获取已上传的视频
     * @return ActiveQuery
     */
    public static function getUploadfileByVideo($fileId)
    {
        //查询实体文件
        $uploadFile = (new Query())->select([
            'Uploadfile.id', 'Uploadfile.name', 'Uploadfile.path', 
            'Uploadfile.thumb_path', 'Uploadfile.size'
        ])->from(['Uploadfile' => Uploadfile::tableName()]);
        //条件查询
        $uploadFile->where([
            'Uploadfile.id' => $fileId,
            'Uploadfile.is_del' => 0
        ]);
        $videoFile = $uploadFile->one();
        if(!empty($videoFile)){
            //重置path、thumb_path
            $videoFile['path'] = StringUtil::completeFilePath($videoFile['path']);
            $videoFile['thumb_path'] = StringUtil::completeFilePath($videoFile['thumb_path']);
            return [$videoFile];
        }else{
            return [];
        }
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
