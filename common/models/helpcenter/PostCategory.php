<?php

namespace common\models\helpcenter;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "{{%post_category}}".
 *
 * @property string $id
 * @property string $parent_id          父级ID
 * @property string $parent_id_path     继承id路径 继承路径以","分隔
 * @property string $app_id             应用ID
 * @property string $name               名称
 * @property string $des                描述
 * @property int $is_show               是否显示：0不显示，1显示
 * @property int $level                 等级
 * @property int $sort_order            排序索引
 * @property string $icon               图标
 * @property string $href               跳转路径
 * @property string $created_at
 * @property string $updated_at
 */
class PostCategory extends ActiveRecord
{
    /** app-frontend */
    const APP_FRONTEND = 'app-frontend';
    /** app-mconline */
    const APP_MCONLINE = 'app-mconline';
    /** app-backend */
    const APP_BACKEND = 'app-backend';
    
    /** APP_ID */
    public static $APPID = [
        self::APP_FRONTEND => 'app-frontend',
        self::APP_MCONLINE => 'app-mconline',
        self::APP_BACKEND => 'app-backend',
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post_category}}';
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
            [['parent_id', 'created_at', 'updated_at'], 'integer'],
            [['parent_id_path', 'app_id', 'des', 'icon', 'href'], 'string', 'max' => 255],
            [['name'], 'string', 'max' => 50],
            [['is_show', 'sort_order'], 'string', 'max' => 1], 
            [['level'], 'string', 'max' => 2], 
        ];
    }
    
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            //设置等级
            if (empty($this->parent_id)) {
                $this->parent_id = 0;
            }
            $this->level = $this->parent_id == 0 ? 1 : self::getCatById($this->parent_id)->level + 1;
            return true;
        }
        return false;
    }
    
    /**
     * 更新父级继承路径
     */
    public function updateParentPath() {
        //设置继承路径
        $parent = self::getCatById($this->parent_id);
        $this->parent_id_path = ($this->level == 1 ? "0" : "$parent->parent_id_path") . ",$this->id";
        $this->update(false, ['parent_id_path']);
    }

    /**
     * 父级
     * @return ActiveQuery
     */
    public function getParent(){
        return self::getCatById($this->parent_id);
    }
    
    /**
     * 获取全路径
     */
    public function getFullPath() {
        $parentids = array_values(array_filter(explode(',', $this->parent_id_path)));
        $path = '';
        foreach ($parentids as $index => $id) {
            $path .= ($index == 0 ? '' : ' > ') . self::getCatById($id)->name;
        }
        return $path;
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'parent_id_path' => Yii::t('app', '{Parent}{ID}{Path}',[
                'Parent' => \Yii::t('app', 'Parent'),
                'ID' => \Yii::t('app', 'ID'),
                'Path' => \Yii::t('app', 'Path'),
            ]),
            'app_id' => Yii::t('app', '{App}{ID}',[
                'App' => \Yii::t('app', 'App'),
                'ID' => \Yii::t('app', 'ID'),
            ]),
            'name' => Yii::t('app', 'Name'),
            'des' => Yii::t('app', 'Des'),
            'is_show' => Yii::t(null, '{Is}{Show}', [
                    'Is' => Yii::t('app', 'Is'),
                    'Show' => Yii::t('app', 'Show'),
                ]),
            'level' => Yii::t('app', 'Level'),
            'sort_order' => Yii::t('app', 'Sort'), 
            'icon' => Yii::t('app', 'Icon'),
            'href' => Yii::t('app', 'Href'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * 获取分类
     * @param integer $id
     */
    public static function getCatById($id) {
        $catdata = self::find()->asArray()->all();
        if (isset($catdata[$id-1])) {
            return new PostCategory($catdata[$id-1]);
        }
        return null;
    }
}
