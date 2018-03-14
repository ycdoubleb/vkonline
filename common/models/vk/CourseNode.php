<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%course_node}}".
 *
 * @property string $id
 * @property string $course_id 所属课程ID
 * @property string $parent_id 父级引用ID
 * @property int $level 等级：0顶级 1级~3级
 * @property string $name 环节名称
 * @property string $des 描述
 * @property int $is_del 是否删除：0否 1是
 * @property int $sort_order 排序
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property Course $course 获取课程
 */
class CourseNode extends ActiveRecord
{
    /**
     * 课程环节
     * @var CourseNode 
     */
    private static $nodes;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_node}}';
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
            //[['id'], 'required'],
            [['name'], 'required'],
            [['level', 'is_del', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['id', 'course_id', 'parent_id'], 'string', 'max' => 32],
            //[['level', 'is_del'], 'string', 'max' => 1],
            [['name'], 'string', 'max' => 50],
            [['des'], 'string', 'max' => 255],
            //[['sort_order'], 'string', 'max' => 2],
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
            'course_id' => Yii::t('app', 'Course ID'),
            'parent_id' => Yii::t('app', 'Parent'),
            'level' => Yii::t('app', 'Level'),
            'name' => Yii::t('app', 'Name'),
            'des' => Yii::t('app', 'Des'),
            'is_del' => Yii::t('app', 'Is Del'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * 
     * @param type $insert 
     */
    public function beforeSave($insert) 
    {
        if (!$this->id) {
            $this->id = md5(time() . rand(1, 99999999));
        }
        
        if(parent::beforeSave($insert))
        {
            if($this->isNewRecord){
                $nodes = self::getCouByNode(['course_id'=>$this->course_id]);
                ArrayHelper::multisort($nodes, 'sort_order', SORT_DESC);
                //设置等级
                if($this->parent_id == null){
                    $this->level = 1;
                }else{
                    $this->level = reset($nodes)->level + 1;
                }
                //设置顺序
                if(reset($nodes) == null){
                    if($this->parent_id == null){
                        $this->sort_order = 0;
                    }
                }else{
                    $this->sort_order = reset($nodes)->sort_order + 1;
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Course::className(), ['id' => 'course_id']);
    }
    
    /**
     * 获取课程环节
     * @param array $condition  条件
     * @return CourseNode
     */
    public static function getCouByNode($condition) 
    {
        //数组合并
        $condition = array_merge(['is_del' => 0], $condition);
        self::$nodes = self::findAll($condition);
        if(self::$nodes != null){
            return self::$nodes;
        }
        return null;
    }
    
    /**
     * 获取父级
     * @param integer $level    默认返回所有分类
     * @param bool $key_to_value    返回键值对形式
     * @return array
     */
    public static function getCouNodeByLevel($level = 1, $key_to_value = true)
    {
        self::$nodes = self::getCouByNode(['level' => $level]);
        $parents = [];
        foreach (self::$nodes as $id => $parent) {
            $parents[] = $parent;
        }

        return $key_to_value ? ArrayHelper::map($parents, 'id', 'name') : $parents;
    }

    /**
     * 获取父级路径
     * @return array
     */
    public static function getParentPath($params = null)
    {
        return [];
    }
}
