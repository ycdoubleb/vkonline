<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%knowledge}}".
 *
 * @property string $id
 * @property string $node_id    环节ID
 * @property string $teacher_id 老师ID
 * @property string $video_id   视频ID
 * @property string $name       知识点名称
 * @property string $des        知识点简介
 * @property string $zan_count  赞数
 * @property string $favorite_count 收藏数
 * @property int $is_del        是否删除：0否 1是
 * @property int $sort_order    排序
 * @property string $created_by 创建人ID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @property Video $video
 * @property CourseNode $node
 * @property KnowledgeVideo[] $knowledgeVideos
 */
class Knowledge extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%knowledge}}';
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
            [['id'], 'required'],
            [['des'], 'string'],
            [['zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
            [['id', 'node_id', 'teacher_id', 'video_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['is_del'], 'string', 'max' => 1],
            [['sort_order'], 'string', 'max' => 2],
            [['id'], 'unique'],
            [['video_id'], 'exist', 'skipOnError' => true, 'targetClass' => Video::class, 'targetAttribute' => ['video_id' => 'id']],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => CourseNode::class, 'targetAttribute' => ['node_id' => 'id']],
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
            'video_id' => Yii::t('app', 'Video ID'),
            'name' => Yii::t('app', 'Name'),
            'des' => Yii::t('app', 'Des'),
            'zan_count' => Yii::t('app', 'Zan Count'),
            'favorite_count' => Yii::t('app', 'Favorite Count'),
            'is_del' => Yii::t('app', 'Is Del'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'created_by' => Yii::t('app', 'Created By'),
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
    public function getNode()
    {
        return $this->hasOne(CourseNode::class, ['id' => 'node_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getKnowledgeVideos()
    {
        return $this->hasMany(KnowledgeVideo::class, ['knowledge_id' => 'id']);
    }
}
