<?php

namespace common\models\vk;

use common\models\AdminUser;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%brand_authorize}}".
 *
 * @property string $id
 * @property string $brand_from     授权品牌
 * @property string $brand_to       被授权品牌
 * @property int $level             保留字段
 * @property string $start_time     开始时间（授权）
 * @property string $end_time       结束时间（授权）
 * @property int $is_del            是否删除（无效） 1是 0否
 * @property string $created_by     创建人（授权人）
 * @property string $created_at     创建时间（授权时间）
 * @property string $updated_at     更新时间
 * 
 * @property Customer $fromName     获取授权方
 * @property Customer $toName       获取被授权方
 * @property AdminUser $createdBy   创建人
 */
class BrandAuthorize extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%brand_authorize}}';
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
            [['id', 'level', 'is_del', 'created_at', 'updated_at'], 'integer'],
            [['brand_from', 'brand_to', 'created_by'], 'string', 'max' => 32],
            [['start_time', 'end_time'], 'string'],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'brand_from' => Yii::t('app', 'Brand From'),
            'brand_to' => Yii::t('app', 'Brand To'),
            'level' => Yii::t('app', '{Authorizes}{Level}', [
                'Authorizes' => Yii::t('app', 'Authorizes'), 'Level' => Yii::t('app', 'Level')]),
            'start_time' => Yii::t('app', 'Start Time'),
            'end_time' => Yii::t('app', 'End Time'),
            'is_del' => Yii::t('app', '{Is}{Delete}', [
                        'Is' => Yii::t('app', 'Is'), 'Delete' => Yii::t('app', 'Delete')]),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            
            $this->start_time = strtotime($this->start_time);
            $this->end_time = strtotime($this->end_time);
            
            return true;
        }
        return false;
    }

    public function afterFind() {
        $this->start_time = date('Y-m-d H:i', $this->start_time);
        $this->end_time = date('Y-m-d H:i', $this->end_time);
    }


    /**
     * @return ActiveQuery
     */
    public function getFromName()
    {
        return $this->hasOne(Customer::class, ['id' => 'brand_from']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getToName()
    {
        return $this->hasOne(Customer::class, ['id' => 'brand_to']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'created_by']);
    }
}
