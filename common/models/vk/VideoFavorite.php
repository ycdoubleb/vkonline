<?php

namespace common\models\vk;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%video_favorite}}".
 *
 * @property string $id
 * @property string $video_id 视频ID
 * @property string $user_id 用户ID
 * @property string $group 分组
 * @property int $is_del 是否删除：0否 1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property Video $video 获取课程
 * @property User $user 获取用户
 */
class VideoFavorite extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%video_favorite}}';
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
            [['is_del', 'created_at', 'updated_at'], 'integer'],
            [['video_id', 'user_id'], 'string', 'max' => 32],
            [['group'], 'string', 'max' => 50],
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
            'user_id' => Yii::t('app', 'User ID'),
            'group' => Yii::t('app', 'Group'),
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
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
