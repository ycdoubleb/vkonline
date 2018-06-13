<?php

namespace common\models\vk;

use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%video_file}}".
 *
 * @property string $id
 * @property string $video_id   视频ID
 * @property string $file_id    附件ID（实体文件ID）
 * @property int $is_source     是否为源视频：0否 1是
 * @property int $is_del        是否已删除：0否 1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @property Video $video   获取视频
 * @property Uploadfile $uploadfile 获取上传的实体文件
 */
class VideoFile extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%video_file}}';
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
            [['is_source', 'is_del', 'created_at', 'updated_at'], 'integer'],
            [['video_id', 'file_id'], 'string', 'max' => 32],
            [['video_id'], 'exist', 'skipOnError' => true, 'targetClass' => Video::class, 'targetAttribute' => ['video_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'video_id' => Yii::t('app', 'Video ID'),
            'file_id' => Yii::t('app', 'File ID'),
            'is_source' => Yii::t('app', 'Is Source'),
            'is_del' => Yii::t('app', 'Is Del'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getVideo()
    {
        return $this->hasOne(Video::class, ['id' => 'video_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getUploadfile()
    {
        return $this->hasOne(Uploadfile::class, ['id' => 'file_id']);
    }
}
