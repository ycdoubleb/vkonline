<?php

namespace common\modules\rbac\models\searchs;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;

/**
 * AuthItemSearch
 */
class AuthItemSearch extends Model
{
    public $group_id;
    public $name;
    public $type;
    public $description;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['group_id', 'name', 'description'], 'safe'],
            [['group_id', 'type'], 'integer'],
        ];
    }
    
    /**
     * 查找 authitem 认证
     *
     * @param array $params 过滤数据
     *
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        /* @var yii\rbac\AuthManager $authManager */
        $authManager = Yii::$app->authManager;
        //权限与权限分组的关系
        $groupMap = ArrayHelper::map((new Query())->from('{{%auth_item_group}}')->all(),'item_name','group_id');
        
        if ($this->type == Item::TYPE_ROLE) {
            $items = $authManager->getRoles();
        } else {
            $items = array_filter($authManager->getPermissions(), function($item) {
                return $this->type == Item::TYPE_PERMISSION xor strncmp($item->name, '/', 1) === 0;
            });
        }
        $this->load($params);
        if ($this->validate()) {
            $search = mb_strtolower(trim($this->name));
            $desc = mb_strtolower(trim($this->description));
            $group_id = mb_strtolower(trim($this->group_id));
            $targets = [];
            foreach ($items as $name => $item) {
                
                $f = (empty($search) || mb_strpos(mb_strtolower($item->name), $search) !== false) &&
                    (empty($desc) || mb_strpos(mb_strtolower($item->description), $desc) !== false) &&
                    (empty($group_id) || $groupMap[$name] == $group_id);
                if ($f) {
                    $targets [] = new self([
                        'name' => $item->name,
                        'type' => $item->type,
                        'group_id' => isset($groupMap[$name]) ? $groupMap[$name] : null,
                        'description' => $item->description,
                    ]);
                }
            }
        }
        
        return new ArrayDataProvider([
            'allModels'=>$targets
        ]);
    }
    
    public function attributeLabels(){
        return [
            'group_id' => Yii::t('app/rbac', 'Group ID'),
            'name' => Yii::t('app', 'Name'),
            'description' => Yii::t('app', 'Des'),
        ];
    }
}
