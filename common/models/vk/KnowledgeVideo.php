<?php

namespace common\models\vk;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%knowledge_video}}".
 *
 * @property string $id
 * @property string $knowledge_id   知识点ID
 * @property string $video_id       视频ID
 * @property int $is_del            是否已删除：0否 1是
 *
 * @property Knowledge $knowledge   获取知识点
 * @property Video $video       获取视频
 */
class KnowledgeVideo extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%knowledge_video}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['video_id'], 'required'],
            [['knowledge_id', 'video_id'], 'string', 'max' => 32],
            [['is_del'], 'string', 'max' => 1],
            [['knowledge_id'], 'exist', 'skipOnError' => true, 'targetClass' => Knowledge::className(), 'targetAttribute' => ['knowledge_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'knowledge_id' => Yii::t('app', 'Knowledge ID'),
            'video_id' => Yii::t('app', 'Video ID'),
            'is_del' => Yii::t('app', 'Is Del'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getKnowledge()
    {
        return $this->hasOne(Knowledge::class, ['id' => 'knowledge_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getVideo()
    {
        return $this->hasOne(Video::class, ['id' => 'video_id']);
    }
}
