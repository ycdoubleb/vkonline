<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%video_transcode}}".
 *
 * @property string $id 文件ID
 * @property string $video_id 视频ID
 * @property string $customer_id Ʒ??ID
 * @property string $name 文件名
 * @property string $thumb_path 缩略图路径
 * @property string $oss_key oss名称
 * @property int $level 视频质量：1=480P 2=720P 3=1080P
 * @property string $size 大小B
 * @property string $width 宽度
 * @property string $height 高度
 * @property string $duration 时长
 * @property string $bitrate 码率
 * @property int $is_del 是否已经删除标记：0未删除，1已删除
 * @property string $created_by 上传人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class VideoTranscode extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%video_transcode}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['level', 'size', 'width', 'height', 'bitrate', 'is_del', 'created_at', 'updated_at'], 'integer'],
            [['duration'], 'number'],
            [['id', 'video_id', 'customer_id', 'created_by'], 'string', 'max' => 32],
            [['name', 'thumb_path', 'oss_key'], 'string', 'max' => 255],
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
            'video_id' => Yii::t('app', 'Video ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'name' => Yii::t('app', 'Name'),
            'thumb_path' => Yii::t('app', 'Thumb Path'),
            'oss_key' => Yii::t('app', 'Oss Key'),
            'level' => Yii::t('app', 'Level'),
            'size' => Yii::t('app', 'Size'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
            'duration' => Yii::t('app', 'Duration'),
            'bitrate' => Yii::t('app', 'Bitrate'),
            'is_del' => Yii::t('app', 'Is Del'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
