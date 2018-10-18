<?php

namespace common\models\vk;

use common\models\User;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%document}}".
 *
 * @property string $id
 * @property string $file_id 对应文件ID
 * @property string $customer_id 所属客户ID
 * @property string $user_cat_id 用户目录ID
 * @property string $name 名称
 * @property string $duration 时长
 * @property int $content_level 内容评级：初1 中2 高3
 * @property string $des 简介
 * @property int $level 等级：0私有 1内网 2公共
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
 * @property UserCategory $userCategory 获取用户自定义分类
 * @property Customer $customer 获取客户
 * @property User $createdBy 获取创建者
 * @property Uploadfile $file   获取上传文件
 * @property TagRef[] $tagRefs 获取标签
 */
class Document extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%document}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_cat_id'], 'checkUserCategoryType'],
            [['name'], 'required', 'message' => Yii::t('app', "{Document}{Name}{Can't be empty}", [
                'Document' => Yii::t('app', 'Document'), 'Name' => Yii::t('app', 'Name'),
                "Can't be empty" => Yii::t('app', "Can't be empty.")
            ])],
            [['user_cat_id', 'content_level', 'level', 'is_recommend', 'is_publish', 'is_official', 'zan_count', 'favorite_count', 'is_del', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['duration'], 'number'],
            [['des'], 'string'],
            [['id', 'file_id', 'customer_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['id'], 'unique'],
            [['file_id'], 'exist', 'skipOnError' => true, 'targetClass' => Uploadfile::className(), 'targetAttribute' => ['file_id' => 'id']],
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
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'file_id' => Yii::t('app', 'File ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'user_cat_id' => Yii::t('app', 'User Cat ID'),
            'name' => Yii::t('app', 'Name'),
            'duration' => Yii::t('app', 'Duration'),
            'content_level' => Yii::t('app', 'Content Level'),
            'des' => Yii::t('app', 'Des'),
            'level' => Yii::t('app', 'Level'),
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

    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if (!$this->id) {
                $this->id = md5(time() . rand(1, 99999999));
            }
            $this->des = Html::encode($this->des);
            
            return true;
        }

        return false;
    }

    public function afterFind() {
        $this->des = Html::decode($this->des);
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
    public function getFile()
    {
        return $this->hasOne(Uploadfile::className(), ['id' => 'file_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getTagRefs() {
        return $this->hasMany(TagRef::class, ['object_id' => 'id'])
            ->where(['is_del' => 0])->with('tags');
    }
}
