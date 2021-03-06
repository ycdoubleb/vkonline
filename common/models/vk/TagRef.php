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
     * 获取所有对象的标签
     * @param string|Query $objectId  对象id
     * @param integer $type     标签类型：1课程 2视频
     * @param boolen $key_to_value  默认返回键值对模式
     * @return array|Query
     */
    public static function getTagsByObjectId($objectId, $type = 1, $key_to_value = true)
    {
        //查询对象下的标签
        $tagRef = self::find()->select(['TagRef.object_id'])->from(['TagRef' => TagRef::tableName()]);
        //关联查询
        $tagRef->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        //必要条件查询
        $tagRef->where(['TagRef.is_del' => 0]);
        //条件查询
        $tagRef->andFilterWhere(['TagRef.object_id' => $objectId, 'type' => $type]);
        //以id排序
        $tagRef->orderBy('TagRef.id');
        /** 默认返回键值对模式 */
        if($key_to_value) {
            $tagRef->select(['TagRef.tag_id', 'Tags.name']);
            return ArrayHelper::map($tagRef->asArray()->all(), 'tag_id', 'name');
        }
        //以对象id为分组
        $tagRef->groupBy('TagRef.object_id');
        
        return $tagRef;
    }
}
