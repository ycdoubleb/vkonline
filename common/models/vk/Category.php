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
 * This is the model class for table "{{%category}}".
 *
 * @property string $id
 * @property string $customer_id 客户ID
 * @property string $name       分类名称
 * @property string $mobile_name 手机端名称
 * @property int $level         等级：0顶级 1~3
 * @property string $path       继承路径
 * @property string $parent_id  父级id
 * @property string $created_by 创建者
 * @property int $sort_order    排序
 * @property string $image      图标路径
 * @property int $created_at    创建时间
 * @property int $updated_at    更新时间
 * @property int $is_show       是否显示
 * @property string $des        描述
 * 
 * @property Category $parent   分类
 * @property string $fullPath   全路径
 * 
 * @property CourseAttribute $courseAttribute   获取所有课程属性 
 */
class Category extends ActiveRecord
{
    /** 显示状态-不显示 */
    const NO_SHOW = 0;
    /** 显示状态-显示 */
    const YES_SHOW = 1;
    
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
    private static $cacheKey = 'vk_category';

    /**
     * 分类[id,name,mobile_name,level,path,parent_id,sort_order,image,is_show,customer_id,created_by]
     * @var array
     */
    private static $categorys;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category}}';
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
            [['parent_id','level', 'is_show', 'created_at', 'updated_at'], 'integer'],
            [['name', 'mobile_name'], 'string', 'max' => 50],
            [['created_by', 'customer_id'], 'string', 'max' => 32],
            [['path', 'image', 'des'], 'string', 'max' => 255],
        ];
    }

    /**
     * 关联查询课程属性
     * @return ActiveQuery
     */
    public function getCourseAttribute()
    { 
        return $this->hasMany(CourseAttribute::class, ['category_id' => 'id'])->where(['is_del' => '0']);
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
            $this->level = $this->parent_id == 0 ? 1 : self::getCatById($this->parent_id)->level + 1;
            return true;
        }
        return false;
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer ID'), 
            'name' => Yii::t('app', 'Name'),
            'mobile_name' => Yii::t('app', 'Mobile Name'),
            'level' => Yii::t('app', 'Level'),
            'path' => Yii::t('app', 'Path'),
            'parent_id' => Yii::t('app', 'Parent ID'),
            'created_by' => Yii::t('app', 'Created By'), 
            'sort_order' => Yii::t('app', 'Sort Order'),
            'image' => Yii::t('app', 'Image'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'is_show' => Yii::t('app', 'Is Show'),
            'des' => Yii::t('app', 'Des'),
        ];
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
                        'cachePath' => \Yii::getAlias('@frontend') . '/runtime/cache'
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
            self::$categorys = null;
        }
    }

    /**
     * 从缓存中获取数据
     */
    private static function loadFromCache() {
        if (self::$categorys !== null || !self::$cache instanceof Cache) {
            return;
        }
        $data = self::$cache->get(self::$cacheKey);
        if (is_array($data) && isset($data[0])) {
            //从缓存取出分类数据
            self::$categorys = ArrayHelper::index($data[0], 'id');
            return;
        }
        $categoryDatas = self::find()->asArray()->all();
        //没有缓存则从数据库获取数据
        self::$categorys = ArrayHelper::index($categoryDatas, 'id');

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
     * @param bool $key_to_value    返回键值对形式
     * @param bool $include_unshow  是否包括隐藏的分类
     * 
     * @return array(array|Array) 
     */
    public static function getCatsByLevel($level = 1, $key_to_value = false, $include_unshow = false) {
        self::initCache();
        $categorys = [];
        foreach (self::$categorys as $id => $category) {
            if ($category['level'] == $level && ($include_unshow || $category['is_show'] == 1)) {
                $categorys[] = $category;
            }
        }
        
        return $key_to_value ? ArrayHelper::map($categorys, 'id', 'name') : $categorys;
    }
    
    /**
     * 获取客户分类的子级
     * @param integer $id               分类ID
     * @param string $customer_id       客户ID
     * @param bool $key_to_value        返回键值对形式
     * @param bool $recursion           是否递归
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [array|key=value]
     */
    public static function getCustomerCatChildren($id,$customer_id, $key_to_value = false, $recursion = false, $include_unshow = false) {
        self::initCache();
        //不传customerID,默认使用当前用户的客户ID
        if(!isset($customer_id) || empty($customer_id)){
            $customer_id = Yii::$app->user->identity->customer_id;
        }     
        $childrens = [];
        foreach (self::$categorys as $c_id => $category) {
            if ($category['parent_id'] == $id && (empty($category['customer_id']) || $category['customer_id'] == $customer_id) && ($include_unshow || $category['is_show'] == 1)) {
                $childrens[] = $category;
                if ($recursion) {
                    $childrens = array_merge($childrens, self::getCustomerCatChildren($c_id, $customer_id , false, $recursion, $include_unshow));
                }
            }
        }
        return $key_to_value ? ArrayHelper::map($childrens, 'id', 'name') : $childrens;
    }
    
    /**
     * 获取分类的子级
     * @param integer $id               分类ID
     * @param bool $key_to_value        返回键值对形式
     * @param bool $recursion           是否递归
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [array|key=value]
     */
    public static function getCatChildren($id, $key_to_value = false, $recursion = false, $include_unshow = false) {
        return self::getCustomerCatChildren($id, null, $key_to_value, $recursion, $include_unshow);
    }

    /**
     * 获取客户分类的子级ID
     * @param integer $id               分类ID
     * @param string $customer_id       客户ID
     * @param bool $recursion           是否递归
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [id,id...]
     */
    public static function getCustomerCatChildrenIds($id, $customer_id ,$recursion = false, $include_unshow = false) {
        self::initCache();
        //不传customerID,默认使用当前用户的客户ID
        if(!isset($customer_id) || empty($customer_id)){
            $customer_id = Yii::$app->user->identity->customer_id;
        }   
        $childrens = [];
        foreach (self::$categorys as $c_id => $category) {
            if ($category['parent_id'] == $id && (empty($category['customer_id']) || $category['customer_id'] == $customer_id) &&  ($include_unshow || $category['is_show'] == 1)) {
                $childrens[] = $c_id;
                if ($recursion) {
                    $childrens = array_merge($childrens, self::getCustomerCatChildrenIds($c_id, $customer_id, $recursion, $include_unshow));
                }
            }
        }
        return $childrens;
    }
    /**
     * 获取分类的子级ID
     * @param integer $id               分类ID
     * @param bool $recursion           是否递归
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [id,id...]
     */
    public static function getCatChildrenIds($id ,$recursion = false, $include_unshow = false) {
        return self::getCustomerCatChildrenIds($id, null, $recursion, $include_unshow);
    }

    /**
     * 反回客户当前（包括父级）分类同级的所有分类
     * @param integer $id               分类ID
     * @param string $customer_id       客户ID
     * @param bool $containerSelfLevel  是否包括该分类同级分类
     * @param bool $recursion           是否递归（向上级递归）
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [[level_1],[level_2],..]
     */
    public static function getCustomerSameLevelCats($id, $customer_id, $containerSelfLevel = false, $recursion = true, $include_unshow = false) {
        //不传customerID,默认使用当前用户的客户ID
        if(!isset($customer_id) || empty($customer_id)){
            $customer_id = Yii::$app->user->identity->customer_id;
        } 
        $catgegory = self::getCatById($id);
        $categorys = [];
        if(($containerSelfLevel && $catgegory!=null)){
            //加上当前目录的子层级
            $childrens = self::getCustomerCatChildren($id, $customer_id, true, false, $include_unshow);
            if(count($childrens)>0){
                $categorys []= $childrens;
            }
        }
        /* 递归获取所有层级 */
        do {
            if ($catgegory == null) {
                //当前分类为空时返回顶级分类
                $categorys [] = self::getCatsByLevel(1, true);
                break;
            } else {
                array_unshift($categorys, self::getCustomerCatChildren($catgegory->parent_id, $customer_id, true, false, $include_unshow));
                if (!$recursion)
                    break;
            }
            if ($catgegory->parent_id == 0)
                break;
        }while (($catgegory = self::getCatById($catgegory->parent_id)) != null);
        return $categorys;
    }
    
    /**
     * 反回当前（包括父级）分类同级的所有分类
     * @param integer $id               分类ID
     * @param string $customer_id       客户ID
     * @param bool $containerSelfLevel  是否包括该分类同级分类
     * @param bool $recursion           是否递归（向上级递归）
     * @param bool $include_unshow      是否包括隐藏的分类
     * 
     * @return array [[level_1],[level_2],..]
     */
    public static function getSameLevelCats($id , $containerSelfLevel = false, $recursion = true, $include_unshow = false){
        return self::getCustomerSameLevelCats($id, null, $include_unshow, $recursion, $containerSelfLevel);
    }

    /**
     * 获取分类
     * @param integer $id
     */
    public static function getCatById($id) {
        self::initCache();
        if (isset(self::$categorys[$id])) {
            return new Category(self::$categorys[$id]);
        }
        return null;
    }
}
