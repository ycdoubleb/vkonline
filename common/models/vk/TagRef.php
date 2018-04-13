<?php

namespace common\models\vk;

use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%tag_ref}}".
 *
 * @property string $id
 * @property string $object_id 视频ID
 * @property string $tag_id 课程标签ID
 * @property int $type 标签类型：1课程 2视频
 * @property int $is_del 是否删除：0否 1是
 * 
 * @property Tags $tags   获取标签
 */
class TagRef extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tag_ref}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tag_id'], 'integer'],
            [['object_id'], 'string', 'max' => 32],
            [['type'], 'string', 'max' => 2],
            [['is_del'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'object_id' => Yii::t('app', 'Object ID'),
            'tag_id' => Yii::t('app', 'Tag ID'),
            'type' => Yii::t('app', 'Type'),
            'is_del' => Yii::t('app', 'Is Del'),
        ];
    }
    
    /**
     * @return ActiveQuery
     */
    public function getTags()
    {
        return $this->hasOne(Tags::class, ['id' => 'tag_id']);
    }
    
    /**
     * 获取所有标签
     * @param string $objectId
     * @param integer $type
     * @param boolen $key_to_value  默认返回键值对模式
     * @return array|object
     */
    public static function getTagsByObjectId($objectId, $type = null, $key_to_value = true)
    {
        $tags = self::find()->where(['is_del' => 0])
            ->andFilterWhere(['type' => $type, 'object_id' => $objectId])
            ->with('tags')->all();
        
        return $key_to_value ? ArrayHelper::map($tags, 'tags.id', 'tags.name') : $tags;
    }
}
