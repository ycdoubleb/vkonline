<?php

namespace common\models\vk;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%tags}}".
 *
 * @property string $id
 * @property string $name 名称
 * @property string $ref_count 引用次数
 * @property string $des 描述
 */
class Tags extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tags}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ref_count'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'ref_count' => Yii::t('app', 'Ref Count'),
            'des' => Yii::t('app', 'Des'),
        ];
    }
    
    /**
     * 保存标签
     * @param string|array  $tags   "1,2,3,4、5、6，7，8" 或者 ["a","b","c"]
     * @return array<Tags>
     */
    public static function saveTags($tags){
        if(is_string($tags)){
            //把全角",""、"替换为半角","
            $tags = str_replace(['，','、'], ',', $tags);
            $tags = explode(',', $tags);
        }
        //处理空值、重复值、清除左右空格
        $tags = array_unique(array_filter($tags));    
        foreach ($tags as &$tag){
            $tag = trim($tag);
        }
        unset($tag);
        
        //查找已经存在的
        $result = self::find()
                ->where(['name' => $tags])
                ->asArray()
                ->all();
        $result = ArrayHelper::map($result, 'name', 'id');
        
        //准备数据
        $rows = [];
        foreach($tags as $tag){
            //过滤已经存在的标签
            if(!isset($result[$tag])){
                $rows[] = [$tag];
            }
        }
        
        //批量插入数据
        \Yii::$app->db->createCommand()->batchInsert(self::tableName(), ['name'], $rows)->execute();
        //返回所有标签
        return self::find()
                ->where(['name' => $tags])
                ->all();
    }
}
