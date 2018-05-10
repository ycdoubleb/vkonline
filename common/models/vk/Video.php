<?php
namespace common\models\vk;

use common\models\User;
use common\modules\webuploader\models\Uploadfile;
use common\utils\FfmpegUtil;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;


/**
 * This is the model class for table "{{%video}}".
 *
 * @property string $id
 * @property string $node_id            环节ID
 * @property string $teacher_id         老师ID
 * @property string $source_id          源视频ID
 * @property string $customer_id        所属客户ID
 * @property string $ref_id             引用ID
 * @property string $name               视频名称
 * @property int $source_level          视频质量：1=480P 1=720P 2=1080P
 * @property string $source_wh          分辨率：1080x720
 * @property string $source_bitrate     视频码率：480kpi
 * @property double $source_duration    时长
 * @property int $source_is_link        是否为外链：0否 1是
 * @property int $content_level         内容评级：初1 中2 高3
 * @property string $des                视频简介
 * @property int $level                 等级：0私有 1内网 2公共
 * @property string $img                图片路径
 * @property int $is_ref                是否为引用：0否 1是
 * @property int $is_recommend          是否推荐：0否 1是
 * @property int $is_publish            是否发布：0否 1是
 * @property string $zan_count          赞数
 * @property string $favorite_count     收藏数
 * @property int $is_del                是否删除：0否 1是
 * @property int $sort_order            排序
 * @property string $created_by         创建人ID
 * @property int $is_official           是否为官网资源：0否 1是
 * @property string $created_at         创建时间
 * @property string $updated_at         更新时间

 * @property CourseNode $courseNode 获取环节
 * @property Customer $customer 获取客户
 * @property User $createdBy 获取创建者
 * @property User $teacher 获取老师
 * @property Video $reference 获取引用视频
 * @property Uploadfile $source 获取源视频
 * @property VideoProgress $progress 获取视频播放进度
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
     * 视频
     * @var Video 
     */
    private static $videos;

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
            [['teacher_id', 'name'], 'required'],
            [['source_duration'], 'number'], 
            [['source_level', 'content_level', 'level', 'is_ref', 'is_recommend', 'is_publish', 'zan_count', 'favorite_count', 
                'is_del', 'is_official',  'sort_order', 'created_at', 'updated_at'], 'integer'],
            //[['id', 'node_id', 'teacher_id', 'source_id', 'customer_id', 'ref_id', 'created_by'], 'string', 'max' => 32],
            [['id', 'node_id', 'teacher_id', 'customer_id', 'ref_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            //[['source_level', 'content_level', 'level', 'is_ref', 'is_recommend', 'is_publish', 'source_is_link'], 'integer', 'max' => 1],
            [['source_wh'], 'string', 'max' => 20],
            [['source_bitrate'], 'string', 'max' => 10],
            [['des'], 'string', 'max' => 500],
            [['img'], 'string', 'max' => 255],
            //[['sort_order'], 'string', 'max' => 2],
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
            'node_id' => Yii::t('app', 'Node ID'),
            'teacher_id' => Yii::t('app', '{MainSpeak}{Teacher}', ['MainSpeak' => Yii::t('app', 'Main Speak'), 'Teacher' => Yii::t('app', 'Teacher')]),
            'source_id' => Yii::t('app', 'Source ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'ref_id' => Yii::t('app', 'Ref ID'),
            'name' => Yii::t('app', '{Video}{Name}', ['Video' => Yii::t('app', 'Video'), 'Name' => Yii::t('app', 'Name')]),
            'source_level' => Yii::t('app', 'Source Level'),
            'source_wh' => Yii::t('app', 'Source Wh'),
            'source_bitrate' => Yii::t('app', 'Source Bitrate'),
            'source_duration' => Yii::t('app', 'Source Duration'), 
            'source_is_link' => Yii::t('app', 'Source Is Link'),
            'content_level' => Yii::t('app', 'Content Level'),
            'des' => Yii::t('app', '{Video}{Des}', ['Video' => Yii::t('app', 'Video'), 'Des' => Yii::t('app', 'Des')]),
            'level' => Yii::t('app', 'Level'),
            'img' => Yii::t('app', 'Img'),
            'is_ref' => Yii::t('app', 'Is Ref'),
            'is_recommend' => Yii::t('app', 'Is Recommend'),
            'is_publish' => Yii::t('app', 'Is Publish'),
            'zan_count' => Yii::t('app', 'Zan Count'),
            'favorite_count' => Yii::t('app', 'Favorite Count'),
            'sort_order' => Yii::t('app', 'Sort Order'),
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
            $nodes = self::getVideoNode(['node_id' => $this->node_id]);
            ArrayHelper::multisort($nodes, 'sort_order', SORT_DESC);
            $counode = $nodes == null ? null : reset($nodes);
            if(trim($this->source_id) == ''){
                $this->source_id = $this->getOldAttribute('source_id');
            }
            $videoInfo = FfmpegUtil::getVideoInfoByUfileId($this->source->path);
            $upload = UploadedFile::getInstance($this, 'img');
            if ($upload != null) {
                $string = $upload->name;
                $array = explode('.', $string);
                //获取后缀名，默认为 jpg 
                $ext = count($array) == 0 ? 'jpg' : $array[count($array) - 1];
                $uploadpath = $this->fileExists(Yii::getAlias('@frontend/web/upload/video/screenshots/'));
                $upload->saveAs($uploadpath . $this->source_id . '.' . $ext);
                $this->img = '/upload/video/screenshots/' . $this->source_id . '.' . $ext . '?rand=' . rand(0, 1000);
            }else {
                //设置默认
                $this->img = FfmpegUtil::createVideoImageByUfileId($this->source_id, $this->source->path);
            }
            //都没做修改的情况下保存旧数据
            if(trim($this->img) == ''){
                $this->img = $this->getOldAttribute('img');
            }
            //设置顺序
            if($this->isNewRecord && $counode !== null){
                $this->sort_order = $counode->sort_order + 1;
            }
            //设置源视频属性
            $this->source_level = $videoInfo['level'];
            $this->source_wh = $videoInfo['width'] . '×' . $videoInfo['height'];
            $this->source_bitrate = $videoInfo['bitrate'];
            $this->source_duration = $videoInfo['duration'];
                    
            return true;
        }
        
        return false;
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCourseNode()
    {
        return $this->hasOne(CourseNode::class, ['id' => 'node_id']);
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
    public function getReference()
    {
        return $this->hasOne(Video::class, ['id' => 'ref_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getSource()
    {
        return $this->hasOne(Uploadfile::class, ['id' => 'source_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getProgress()
    {
        return $this->hasOne(VideoProgress::class, ['video_id' => 'id']);
    }
        
    /**
     * 获取已上传的视频
     * @return ActiveQuery
     */
    public static function getUploadfileByVideo($fileId = null)
    {
        $uploadFile = (new Query());
        $uploadFile->select(['Video.source_id AS id', 'Video.name AS video_name', 'Uploadfile.name', 'Uploadfile.size']);
        $uploadFile->from(['Video' => self::tableName()]);
        $uploadFile->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Video.source_id');
        $uploadFile->where(['Uploadfile.id' => $fileId]);
        $uploadFile->andWhere(['Uploadfile.is_del' => 0]);
        
        $hasFile = $uploadFile->one();
        if($hasFile){
            return [$hasFile];
        }else{
            return [];
        }
    }
    
    /**
     * 获取已上传的附件
     * @return ActiveQuery
     */
    public static function getUploadfileByAttachment($id = null)
    {
        $uploadFile = (new Query());
        $uploadFile->select(['Attachment.file_id AS id', 'Video.name AS video_name', 'Uploadfile.name', 'Uploadfile.size']);
        $uploadFile->from(['Video' => self::tableName()]);
        $uploadFile->leftJoin(['Attachment' => VideoAttachment::tableName()], 'Attachment.video_id = Video.id');
        $uploadFile->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Attachment.file_id');
        $uploadFile->where(['Attachment.video_id' => $id]);
        $uploadFile->andWhere(['Attachment.is_del' => 0, 'Uploadfile.is_del' => 0]);
        
        $hasFile = $uploadFile->all();
        if($hasFile !== null){
            return $hasFile;
        }else{
            return [];
        }
    }
     
    /**
     * 获取视频节点
     * @param array $condition  条件
     * @return Video
     */
    public static function getVideoNode($condition) 
    {
        //数组合并
        $condition = array_merge(['is_del' => 0], $condition);
        self::$videos = self::findAll($condition);
        if(self::$videos != null){
            return self::$videos;
        }
        return null;
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
