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
 * @property Knowledge[] $knowledges 获取所有知识点
 */
class CourseNode extends ActiveRecord
{
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
        if(parent::beforeSave($insert)){
            if (!$this->id) {
                $this->id = md5(time() . rand(1, 99999999));
            }
            if($this->isNewRecord){
                $nodes = self::getCourseByNodes(['course_id' => $this->course_id]);
                ArrayHelper::multisort($nodes, 'sort_order', SORT_DESC);
                $firstNode = reset($nodes);
                //设置等级
                if($this->parent_id == null){
                    $this->level = 1;
                }
                //设置顺序
                if(!empty($firstNode)){
                    $this->sort_order = $firstNode->sort_order + 1;
                }
            }
            $this->des = htmlentities($this->des);
            
            return true;
        }
        return false;
    }
    
    public function afterFind()
    {
        $this->des = html_entity_decode($this->des);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getKnowledges()
    {
        return $this->hasMany(Knowledge::class, ['node_id' => 'id'])
            ->where(['is_del' => 0])->orderBy(['sort_order' => SORT_ASC]);
    }
    
    /**
     * 获取父级
     * @param integer $level    等级：0顶级 1级~3级
     * @param bool $key_to_value    返回键值对形式
     * @return array|Query
     */
    public static function getCourseNodeByLevel($level = 1, $key_to_value = true)
    {
        //查询所有的环节
        $nodes = self::getCourseByNodes(['level' => $level]);
        $parents = [];
        foreach ($nodes as $id => $parent) {
            $parents[] = $parent;
        }

        return $key_to_value ? ArrayHelper::map($parents, 'id', 'name') : $parents;
    }

    /**
     * 获取父级路径
     * @return array|null
     */
    public static function getCourseNodeByPath($id)
    {
        //查询课程环节的父级
        $nodes = self::getCourseByNodes(['parent_id' => $id]);  
        if ($nodes != null) {
            return $nodes;
        }
        
        return null;
    }
    
    /**
     * 获取课程课程下的所有环节
     * @param array $condition  条件
     * @return CourseNode|null
     */
    public static function getCourseByNodes($condition) 
    {
        $condition = array_merge(['is_del' => 0], $condition);  //数组合并
        $nodes = self::findAll($condition); //查询所有环节
        if($nodes != null){
            return $nodes;
        }
        
        return null;
    }
}
