<?php

namespace common\modules\rbac\models;

use common\models\System;
use Exception;
use common\modules\rbac\RbacManager;
use Yii;
use yii\base\Model;
use yii\helpers\Json;
use yii\rbac\Item;


/**
 *
 * @property string $name                       名称
 * @property integer $group_id                  所属分组id
 * @property integer $type                      类型
 * @property string $description                描述
 * @property string $rule_name                  规则名
 * @property string $data                       数据
 * 
 * @property System $roleCategory               角色类别
 * @property Item $item 数据
 * @property AuthGroup $authGroup               分组
 */
class AuthItem extends Model
{
    /* 权限 */
    const ITEM_GTOUP_TABLENAME = '{{%auth_item_group}}';
    
    /**
     * 分组id
     * @var int 
     */
    public $group_id;
    /**
     * 名称
     * @var string 
     */
    public $name;
    /**
     * 类型
     * @var integer 
     */
    public $type;
    /**
     * 描述
     * @var string 
     */
    public $description;
    /**
     * 规则名称
     * @var string 
     */
    public $ruleName;
    /**
     * 数据
     * @var string 
     */
    public $data;

    /**
     * @var Item
     */
    private $_item;
    

    /**
     *
     * @var RbacManager
     */
    protected $authManager;
    
    /**
     * 初始对象
     * @param Item $item
     * @param array $config
     */
    public function __construct($item,$config = array()) 
    {
        $this->authManager = \Yii::$app->authManager;
        $this->_item = $item;
        if($item !== null)
        {
            $this->name = $item->name;
            $this->type = $item->type;
            $this->description = $item->description;
            $this->ruleName = $item->ruleName;
            $this->data = $item->data === null ? null : json_decode($time->data);
        }
        
        parent::__construct($config);
    }
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_item}}';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'name', 'type'], 'required'],
            [['name'],'coustom_unique','when'=>function()
                {
                    return $this->getIsNewRecord() || ($this->_item->name != $this->name);
                }],
            [['name'], 'match', 'pattern' => '/^[\w-]+$/'],
            [['type'], 'integer'],
            [['description', 'data'], 'string'],
            [['name', 'ruleName'], 'string', 'max' => 64],
            [['ruleName'],'in',
                'range'=>  array_keys($this->authManager->getRules()),
                'message'=>'没有找到对应规则!'],
            [['description', 'data', 'ruleName'], 'default']
        ];
    }
    
    /**
     * 重写唯一过虑器
     */
    public function coustom_unique()
    {
        $value = $this->name;
        if($this->authManager->getRole($value) !== null || $this->authManager->getPermission($value) !== null)
        {
            $message = \Yii::t('yii', '{attribute}"{value}" has already been taken.');
            $params = [
                'attribute'=>$this->getAttributeLabel('name'),
                'value'=>$value
            ];
            $this->addError('name', \Yii::$app->getI18n()->format($message, $params, \Yii::$app->language));
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'group_id' => Yii::t('app/rbac', 'Group ID'),
            'authgroup.name' => Yii::t('app/rbac', 'Group ID'),
            'name' => Yii::t('app', 'Name'),
            'type' => Yii::t('app', 'Type'),
            'description' => Yii::t('app', 'Des'),
            'ruleName' => Yii::t('app/rbac', 'Rule Name'),
            'data' => 'Data',
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * 检查是否为新创建对象
     * @return boolean
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }
    
    /**
     * 获取类型名
     * @param mixed $type
     * @return string|array;
     */
    public static function getTypeName($type = null)
    {
        $result = [
            Item::TYPE_ROLE => 'Role',
            Item::TYPE_PERMISSION => 'Permission'
        ];
        if($type !== null)
            return $result[$type];
        return $result;
    }

    /**
     * 保存 角色/权限到 [yii\rbac\authManager]
     */
    public function save()
    {
        if ($this->validate()) {
            $manager = $this->authManager;
            if ($this->_item === null) {
                if ($this->type == Item::TYPE_ROLE) {
                    $this->_item = $manager->createRole($this->name);
                } else {
                    $this->_item = $manager->createPermission($this->name);
                }
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;
            $this->_item->description = $this->description;
            $this->_item->ruleName = $this->ruleName;
            $this->_item->data = $this->data === null || $this->data === '' ? null : Json::decode($this->data);
            if ($isNew) {
                if($manager->add($this->_item)){
                    //添加成功，关联分组
                    Yii::$app->db->createCommand()->insert(self::ITEM_GTOUP_TABLENAME, [
                                'item_name' => $this->name,
                                'group_id' => $this->group_id,
                            ])->execute();
                }
            } else {
                
                $manager->update($oldName, $this->_item);
                //关联分组
                Yii::$app->db->createCommand()->update(self::ITEM_GTOUP_TABLENAME, 
                    ['item_name' => $this->name,'group_id' => $this->group_id,],['item_name' => $oldName])->execute();
            }
            $manager->invalidateCache();
            return true;
        } else {
            return false;
        }
    }
    /**
     * 向一个权限或者角色添加子对象
     * Adds an item as a child of another item.
     * @param array $items
     * @return int
     */
    public function addChildren($items)
    {
        /* @var $manager RbacManager */
        $manager = Yii::$app->authManager;
        $item = $this;
        $success = 0;
        if ($item->item) {
            foreach ($items as $name) {
                $child = $manager->getPermission($name);
                if ($item->type == Item::TYPE_ROLE && $child === null) {
                    $child = $manager->getRole($name);
                }
                try {
                    $manager->addChild($item->item, $child);
                    $success++;
                } catch (Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            $manager->invalidateCache();
        }
        return $success;
    }

    /**
     * 把对象从一个权限或者角色的子对象列表里删除
     * Remove an item as a child of another item.
     * @param array $items
     * @return int
     */
    public function removeChildren($items)
    {
        /* @var $manager RbacManager */
        $manager = Yii::$app->authManager;
        $item = $this;
        $success = 0;
        if ($item->item !== null) {
            foreach ($items as $name) {
                $child = $manager->getPermission($name);
                if ($item->type == Item::TYPE_ROLE && $child === null) {
                    $child = $manager->getRole($name);
                }
                try {
                    $manager->removeChild($item->item, $child);
                    $success++;
                } catch (Exception $exc) {
                    Yii::error($exc->getMessage(), __METHOD__);
                }
            }
        }
        if ($success > 0) {
            $manager->invalidateCache();
        }
        return $success;
    }

    /**
     * 获取目标对象【可配置】和【已配置】的子对象
     * Get items
     * @param string $itemName  目标对象 
     * @return array(available,assigned)
     */
    public function getItems()
    {
        /* @var $manager RbacManager */
        $manager = Yii::$app->authManager;
        $item = $this;
        $available = [];
        if ($item->type == Item::TYPE_ROLE) {
            foreach (array_keys($manager->getRoles()) as $name) {
                $available[$name] = 'role';
            }
        }
        foreach (array_keys($manager->getPermissions()) as $name) {
            $available[$name] = $name[0] == '/' ? 'route' : 'permission';
        }

        $assigned = [];
        foreach ($manager->getChildren($item->item->name) as $item) {
            $assigned[$item->name] = $item->type == 1 ? 'role' : ($item->name[0] == '/' ? 'route' : 'permission');
            unset($available[$item->name]);
        }
        unset($available[$item->name]);
        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }
    /**
     * 分组
     * @return AuthGroup
     */
    public function getAuthGroup(){
        return AuthGroup::find()->where(['id' => $this->group_id])->one();
    }
    
    /**
     * 
     * @return yii\rbac\Item;
     */
    public function getItem()
    {
        return $this->_item;
    }
}
