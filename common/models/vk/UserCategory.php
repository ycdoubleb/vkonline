<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\caching\Cache;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%user_category}}".
 *
 * @property string $id
 * @property string $name 分类名称
 * @property string $mobile_name 手机端名称
 * @property int $type 类型：1我的视频 2收藏的视频
 * @property int $level 等级：0顶级 1~3
 * @property string $path 继承路径，多个逗号分隔
 * @property string $parent_id 父级id
 * @property int $sort_order 排序
 * @property string $image 图标路径
 * @property int $is_show 是否显示
 * @property int $is_public 是否是公共目录：1是，0否
 * @property string $des 描述
 * @property string $created_by 创建者
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property UserCategory $parent   父级分类
 * @property string $fullPath   全路径
 * 
 * @property Video[] $videos 获取所有视频
 */
class UserCategory extends ActiveRecord
{
    
    /** 显示状态-不显示 */
    const NO_SHOW = 0;
    /** 显示状态-显示 */
    const YES_SHOW = 1;
    /** 我的视频 */
    const TYPE_MYVIDOE = 1;
    /** 我的收藏 */
    const TYPE_MYCOLLECT = 2;

    /**
     * 显示状态
     * @var array 
     */
    public static $showStatus = [
        self::NO_SHOW => '不显示',
        self::YES_SHOW => '显示',
    ];
    
    /* @var $cache Cache */
    private static $cache;

    /**
     * @see cache
     */
    private static $cacheKey = 'vk_user_category';

    /**
     * 分类[id,name,mobile_name,type,level,path,parent_id,sort_order,image,is_show,created_by]
     * @var array
     */
    private static $userCategorys;

    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_category}}';
    }

     /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            TimestampBehavior::class
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'level', 'parent_id', 'sort_order', 'is_show', 'is_public', 'created_at', 'updated_at'], 'integer'],
            [['name', 'mobile_name'], 'string', 'max' => 50],
            [['path', 'image', 'des'], 'string', 'max' => 255],
            [['created_by'], 'string', 'max' => 32],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'mobile_name' => Yii::t('app', 'Mobile Name'),
            'type' => Yii::t('app', 'Type'),
            'level' => Yii::t('app', 'Level'),
            'path' => Yii::t('app', 'Path'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'image' => Yii::t('app', 'Image'),
            'is_show' => Yii::t('app', 'Is Show'),
            'is_public' => Yii::t('app', 'Is Public'),
            'des' => Yii::t('app', 'Des'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            if ($this->mobile_name == "") {
                $this->mobile_name = $this->name;
            }
            
            $file_name = md5(time());
            //图片上传
            $upload = UploadedFile::getInstance($this, 'image');
            if ($upload !== null) {
                $string = $upload->name;
                $array = explode('.', $string);
                //获取后缀名，默认名为.jpg
                $ext = count($array) == 0 ? 'jpg' : $array[count($array) - 1];
                $uploadpath = $this->fileExists(Yii::getAlias('@frontend/web/upload/course/category/'));
                $upload->saveAs($uploadpath . $file_name . '.' . $ext);
                $this->image = '/upload/course/category/' . $file_name . '.' . $ext . '?r=' . rand(1, 10000);
            }
            if (trim($this->image) == '') {
                $this->image = $this->getOldAttribute('image');
            }
            //设置等级
            if (empty($this->parent_id)) {
                $this->parent_id = 0;
            }
            //设置排序
//            if(empty($this->sort_order)){
//                $this->sort_order = self::find()->orderBy(['sort_order' => SORT_DESC])->one()->sort_order + 1;
//            }
            $this->level = $this->parent_id == 0 ? 1 : self::getCatById($this->parent_id)->level + 1;
            return true;
        }
        return false;
    }

    /**
     * 关联查询课程
     * @return ActiveQuery
     */
    public function getVideos() {
        return $this->hasMany(Video::class, ['user_cat_id' => 'id'])->where(['is_del' => '0']);
    }
    
    /**
     * 检查目标路径是否存在，不存即创建目标
     * @param string $uploadpath    目录路径
     * @return string
     */
    private function fileExists($uploadpath) {

        if (!file_exists($uploadpath)) {
            mkdir($uploadpath);
        }
        return $uploadpath;
    }

    /**
     * 更新父级继承路径
     */
    public function updateParentPath() {
        //设置继承路径
        $parent = self::getCatById($this->parent_id);
        $this->path = ($this->level == 1 ? "0" : "$parent->path") . ",$this->id";
        $this->update(false, ['path']);
    }

    /**
     * 父级
     * @return Category
     */
    public function getParent() {
        self::initCache();
        return self::getCatById($this->parent_id);
    }

    /**
     * 获取所有父级
     * @return type
     */
    public function getParents() {
        self::initCache();
        $parentids = array_values(array_filter(explode(',', $this->path)));
        $parents = [];
        foreach ($parentids as $index => $id) {
            $parents [] = self::getCatById($id);
        }
        return $parents;
    }

    /**
     * 获取全路径
     */
    public function getFullPath() {
        self::initCache();
        $parentids = array_values(array_filter(explode(',', $this->path)));
        $path = '';
        foreach ($parentids as $index => $id) {
            $path .= ($index == 0 ? '' : ' > ') . self::getCatById($id)->name;
        }
        return $path;
    }
    
    //==========================================================================
    //
    // Cache
    //
    //==========================================================================

    /* 初始缓存 */
    private static function initCache() {
        if (self::$cache == null) {
            self::$cache = Instance::ensure([
                        'class' => 'yii\caching\FileCache',
                        'cachePath' => Yii::getAlias('@frontend') . '/runtime/cache'
                            ], Cache::class);
        }
        self::loadFromCache();
    }

    /**
     * 取消缓存
     */
    public static function invalidateCache() {
        self::initCache();
        if (self::$cache !== null) {
            self::$cache->delete(self::$cacheKey);
            self::$userCategorys = null;
        }
    }

    /**
     * 从缓存中获取数据
     */
    private static function loadFromCache() {
        if (self::$userCategorys !== null || !self::$cache instanceof Cache) {
            return;
        }
        $data = self::$cache->get(self::$cacheKey);
        if (is_array($data) && isset($data[0])) {
            //从缓存取出分类数据
            self::$userCategorys = ArrayHelper::index($data[0], 'id');
            return;
        }
        $categoryDatas = self::find()->asArray()->all();
        //没有缓存则从数据库获取数据
        self::$userCategorys = ArrayHelper::index($categoryDatas, 'id');
        
        self::$cache->set(self::$cacheKey, [$categoryDatas]);
    }

    //==========================================================================
    //
    // public method
    //
    //==========================================================================
    /**
     * 获取分类
     * @param intger $level      默认返回所有分类
     * @param intger $type       默认返回我的视频类型
     * @param bool $key_to_value    返回键值对形式
     * @param bool $include_unshow  是否包括隐藏的分类
     * 
     * @return array(array|Array) 
     */
    public static function getCatsByLevel($level = 1, $created_by, $type = 1, $key_to_value = false, $include_unshow = false) {
        self::initCache();
        //不传created_by,默认使用当前用户的ID
        if (!isset($created_by) || empty($created_by)) {
            $created_by = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        }
        $userCategorys = [];
        foreach (self::$userCategorys as $id => $category) {
            if ($category['level'] == $level && ($category['created_by'] == $created_by || $category['is_public'] == 1) 
                    && $category['type'] == $type && ($include_unshow || $category['is_show'] == 1)) {
                $userCategorys[] = $category;
            }
        }

        return $key_to_value ? ArrayHelper::map($userCategorys, 'id', 'name') : $userCategorys;
    }

    /**
     * 获取用户分类的子级
     * @param integer $id               分类ID
     * @param string $created_by        用户ID
     * @param integer $type             默认返回我的视频类型
     * @param bool $key_to_value        返回键值对形式
     * @param bool $recursion           是否递归
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [array|key=value]
     */
    public static function getUserCatChildren($id, $created_by, $type = 1, $key_to_value = false, $recursion = false, $include_unshow = false) {
        self::initCache();
        //不传created_by,默认使用当前用户的ID
        if (!isset($created_by) || empty($created_by)) {
            $created_by = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        }
        $childrens = [];
        foreach (self::$userCategorys as $c_id => $category) {
            if ($category['parent_id'] == $id && $category['type'] == $type &&
                    (empty($category['created_by']) || ($created_by && $category['created_by'] == $created_by)) &&
                    ($include_unshow || $category['is_show'] == 1)) {
                $childrens[] = $category;
                if ($recursion) {
                    $childrens = array_merge($childrens, self::getUserCatChildren($c_id, $created_by, $type, false, $recursion, $include_unshow));
                }
            }
        }
        return $key_to_value ? ArrayHelper::map($childrens, 'id', 'name') : $childrens;
    }

    /**
     * 获取分类的子级
     * @param integer $id               分类ID
     * @param intger $type              默认返回我的视频类型
     * @param bool $key_to_value        返回键值对形式
     * @param bool $recursion           是否递归
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [array|key=value]
     */
    public static function getCatChildren($id, $type = 1, $key_to_value = false, $recursion = false, $include_unshow = false) {
        return self::getUserCatChildren($id, null, $type, $key_to_value, $recursion, $include_unshow);
    }

    /**
     * 获取用户分类的子级ID
     * @param integer $id               分类ID
     * @param string $created_by        用户ID
     * @param integer $type             默认返回我的视频类型
     * @param bool $recursion           是否递归
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [id,id...]
     */
    public static function getUserCatChildrenIds($id, $created_by, $type = 1, $recursion = false, $include_unshow = false) {
        self::initCache();
        //不传customerID,默认使用当前用户的客户ID
        if (!isset($created_by) || empty($created_by)) {
            $created_by = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        }
        $childrens = [];
        foreach (self::$userCategorys as $c_id => $category) {
            if ($category['parent_id'] == $id && $category['type'] == $type && 
                    (empty($category['created_by']) || ($created_by && $category['created_by'] == $created_by)) &&
                    ($include_unshow || $category['is_show'] == 1)) {
                $childrens[] = $c_id;
                if ($recursion) {
                    $childrens = array_merge($childrens, self::getUserCatChildrenIds($c_id, $created_by, $type, $recursion, $include_unshow));
                }
            }
        }
        return $childrens;
    }

    /**
     * 获取分类的子级ID
     * @param integer $id               分类ID
     * @param integer $type             默认返回我的视频类型
     * @param bool $recursion           是否递归
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [id,id...]
     */
    public static function getCatChildrenIds($id, $type = 1, $recursion = false, $include_unshow = false) {
        return self::getUserCatChildrenIds($id, null, $type, $recursion, $include_unshow);
    }

    /**
     * 返回用户当前（包括父级）分类同级的所有分类
     * @param integer $id               分类ID
     * @param string $created_by        用户ID
     * @param integer $type             默认返回我的视频类型
     * @param bool $containerSelfLevel  是否包括该分类同级分类
     * @param bool $recursion           是否递归（向上级递归）
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [[level_1],[level_2],..]
     */
    public static function getUserSameLevelCats($id, $created_by, $type = 1, $containerSelfLevel = false, $recursion = true, $include_unshow = false) {
        //不created_by,默认使用当前用户的ID
        if (!isset($created_by) || empty($created_by)) {
            $created_by = Yii::$app->user->isGuest ? null : Yii::$app->user->id;
        }
        $catgegory = self::getCatById($id);
        $userCategorys = [];
        if (($containerSelfLevel && $catgegory != null)) {
            //加上当前目录的子层级
            $childrens = self::getUserCatChildren($id, $created_by, $type, true, false, $include_unshow);
            if (count($childrens) > 0) {
                $userCategorys [] = $childrens;
            }
        }
        /* 递归获取所有层级 */
        do {
            if ($catgegory == null) {
                //当前分类为空时返回顶级分类
                $userCategorys [] = self::getCatsByLevel(1, $created_by, $type, true);
                break;
            } else {
                array_unshift($userCategorys, self::getUserCatChildren($catgegory->parent_id, $created_by, $type, true, false, $include_unshow));
                if (!$recursion)
                    break;
            }
            if ($catgegory->parent_id == 0)
                break;
        }while (($catgegory = self::getCatById($catgegory->parent_id)) != null);

        return $userCategorys;
    }

    /**
     * 返回当前（包括父级）分类同级的所有分类
     * @param integer $id               分类ID
     * @param string $created_by        用户ID
     * @param integer $type             默认返回我的视频类型
     * @param bool $containerSelfLevel  是否包括该分类同级分类
     * @param bool $recursion           是否递归（向上级递归）
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [[level_1],[level_2],..]
     */
    public static function getSameLevelCats($id, $type = 1, $containerSelfLevel = false, $recursion = true, $include_unshow = false) {
        return self::getUserSameLevelCats($id, null, $type, $containerSelfLevel, $recursion, $include_unshow);
    }

    /**
     * 获取分类
     * @param integer $id
     */
    public static function getCatById($id) {
        self::initCache();
        if (isset(self::$userCategorys[$id])) {
            return new UserCategory(self::$userCategorys[$id]);
        }
        return null;
    }

    /**
     * 获取所有分类数据
     * @return array
     */
    public static function getCategorys() {
        self::initCache();
        return self::$userCategorys;
    }
}
