<?php

namespace common\models;

use common\models\vk\Customer;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%banner}}".
 *
 * @property string $id
 * @property string $customer_id 客户ID
 * @property string $title 宣传页名称
 * @property string $path 内容路径
 * @property string $link 超联接
 * @property string $target 打开方式：_blank,_self,_parent,_top
 * @property int $type 内容类型：1图片，2视频
 * @property int $sort_order 排序
 * @property int $is_publish 是否发布：0否 1是
 * @property string $des 描述
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Banner extends ActiveRecord
{
    /** 内容-图片 */
    const TYPE_IMG = 1;
    /** 内容-视频 */
    const TYPE_VIDEO = 2;
    
    /** 发布状态-未发布 */
    const NO_PUBLISH = 0;
    /** 发布状态-已发布 */
    const YES_PUBLISH = 1;
    
    /** 打开方式-新开页面 */
    const TARGET_BLANK = '_blank';
    /** 打开方式-替换打开 */
    const TARGET_SELF = '_self';

    /**
     * 内容类型
     * @var array
     */
    public static $contentType = [
        self::TYPE_IMG => '图片',
        self::TYPE_VIDEO => '视频',
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
     * 打开方式
     * @var array 
     */
    public static $targetType = [
        self::TARGET_BLANK => '新开页面',
        self::TARGET_SELF => '替换打开',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%banner}}';
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
            [['type', 'target'], 'required'],
            [['created_at', 'updated_at', 'type', 'is_publish', 'sort_order'], 'integer'],
            [['customer_id', 'created_by'], 'string', 'max' => 32],
            [['title'], 'string', 'max' => 50],
            [['path', 'link', 'des'], 'string', 'max' => 255],
            [['target'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', '{The}{Customer}',['The' => Yii::t('app', 'The'),'Customer' => Yii::t('app', 'Customer')]),
            'title' => Yii::t('app', 'Name'),
            'path' => Yii::t('app', 'Path'),
            'link' => Yii::t('app', 'Href'),
            'target' => Yii::t('app', '{Open}{Mode}',['Open' => Yii::t('app', 'Open'),'Mode' => Yii::t('app', 'Mode')]),
            'type' => Yii::t('app', 'Type'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'is_publish' => Yii::t('app', '{Is}{Publish}',['Is' => Yii::t('app', 'Is'),'Publish' => Yii::t('app', 'Publish')]),
            'des' => Yii::t('app', 'Des'),
            'created_by' => Yii::t('app', 'Created By'), 
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * 关联获取所属客户
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
    
    /**
     * 关联获取创建者
     * @return ActiveQuery
     */
    public function getAdminUser()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'created_by']);
    }
    
    /**
     * 关联获取创建者(属于客户的)
     * @return ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
    
    /**
     * 
     * @param type $insert
     * @return boolean
     */
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            //设置创建人
            if(!$this->created_by){
                $this->created_by = Yii::$app->user->id;
            }
            $file_name = md5(time());
            //图片上传
            $upload = UploadedFile::getInstance($this, 'path');
            if ($upload !== null) {
                $string = $upload->name;
                $array = explode('.', $string);
                //获取后缀名，默认名为.jpg
                $ext = count($array) == 0 ? 'jpg' : $array[count($array) - 1];
                $uploadpath = $this->fileExists(Yii::getAlias('@frontend/web/upload/banner/'));
                $upload->saveAs($uploadpath . $file_name . '.' . $ext);
                $this->path = '/upload/banner/' . $file_name . '.' . $ext . '?r=' . rand(1, 10000);
            }
            if (trim($this->path) == '') {
                $this->path = $this->getOldAttribute('path');
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
    private function fileExists($uploadpath) {

        if (!file_exists($uploadpath)) {
            mkdir($uploadpath);
        }
        return $uploadpath;
    }
}
