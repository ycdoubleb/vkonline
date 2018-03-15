<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%video}}".
 *
 * @property string $id
 * @property string $node_id 环节ID
 * @property string $teacher_id 老师ID
 * @property string $source_id 源视频ID
 * @property string $customer_id 所属客户ID
 * @property string $ref_id 引用ID
 * @property string $name 视频名称
 * @property int $source_level 视频质量：1=480P 1=720P 2=1080P
 * @property string $source_wh 分辨率：1080x720
 * @property string $source_bitrate 视频码率：480kpi
 * @property double $source_duration 时长
 * @property int $source_is_link 是否为外链：0否 1是
 * @property int $content_level 内容评级：初1 中2 高3
 * @property string $des 视频简介
 * @property int $level 等级：0私有 1内网 2公共
 * @property string $img 图片路径
 * @property int $is_ref 是否为引用：0否 1是
 * @property int $is_recommend 是否推荐：0否 1是
 * @property int $is_publish 是否发布：0否 1是
 * @property string $zan_count 赞数
 * @property string $favorite_count 收藏数
 * @property int $sort_order 排序
 * @property string $created_by 创建人ID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Video extends ActiveRecord
{
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
            [['id'], 'required'],
            [['source_duration'], 'number'], 
            [['zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
            [['id', 'node_id', 'teacher_id', 'source_id', 'customer_id', 'ref_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['source_level', 'content_level', 'level', 'is_ref', 'is_recommend', 'is_publish', 'source_is_link'], 'int', 'max' => 1],
            [['source_wh'], 'string', 'max' => 20],
            [['source_bitrate'], 'string', 'max' => 10],
            [['des'], 'string', 'max' => 500],
            [['img'], 'string', 'max' => 255],
            [['sort_order'], 'string', 'max' => 2],
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
            'teacher_id' => Yii::t('app', 'Teacher ID'),
            'source_id' => Yii::t('app', 'Source ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'ref_id' => Yii::t('app', 'Ref ID'),
            'name' => Yii::t('app', 'Name'),
            'source_level' => Yii::t('app', 'Source Level'),
            'source_wh' => Yii::t('app', 'Source Wh'),
            'source_bitrate' => Yii::t('app', 'Source Bitrate'),
            'source_duration' => Yii::t('app', 'Source Duration'), 
            'source_is_link' => Yii::t('app', 'Source Is Link'),
            'content_level' => Yii::t('app', 'Content Level'),
            'des' => Yii::t('app', 'Des'),
            'level' => Yii::t('app', 'Level'),
            'img' => Yii::t('app', 'Img'),
            'is_ref' => Yii::t('app', 'Is Ref'),
            'is_recommend' => Yii::t('app', 'Is Recommend'),
            'is_publish' => Yii::t('app', 'Is Publish'),
            'zan_count' => Yii::t('app', 'Zan Count'),
            'favorite_count' => Yii::t('app', 'Favorite Count'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
