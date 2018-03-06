<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\rbac;

use Exception;
use common\modules\rbac\components\Helper;
use Yii;
use yii\caching\Cache;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\rbac\Assignment;
use yii\rbac\DbManager;
use yii\rbac\Role;


/**
 * Description of RbacManager
 *
 * @author Administrator
 */
class RbacManager extends DbManager{
    
    /**
     * 默认管理角色，
     * @var type 
     */
    public $defaultAdminRole = 'admin';
    
    public function init() 
    {
        parent::init();
        $this->loadFromCache();
    }

    /**
     *
     * @var array [parent => list of child]
     */
    protected $childs;
    
    protected $assignmentsCache = [];
    
    public function loadFromCache()
    {
        if ($this->items !== null || !$this->cache instanceof Cache) {
            return;
        }

        $data = $this->cache->get($this->cacheKey);
        
        if (is_array($data) && isset($data[0], $data[1], $data[2])) {
            list ($this->items, $this->rules, $this->parents) = $data;
            return;
        }
        
        $query = (new Query)->from($this->itemTable);
        $this->items = [];
        foreach ($query->all($this->db) as $row) {
            $this->items[$row['name']] = $this->populateItem($row);
        }

        $query = (new Query)->from($this->ruleTable);
        $this->rules = [];
        foreach ($query->all($this->db) as $row) {
            $this->rules[$row['name']] = unserialize($row['data']);
        }

        
        $query = (new Query)->from($this->itemChildTable);
        $this->parents = [];
        foreach ($query->all($this->db) as $row) {
            if (isset($this->items[$row['child']])) {
                $this->parents[$row['child']][] = $row['parent'];
            }
            
            if (isset($this->items[$row['parent']])) {
                $this->childs[$row['parent']][] = $row['child'];
            }
        }
                
        //create roleToUsers;
        //$this->$assignments = [];
        /* 
        $query = (new Query)->from($this->assignmentTable);
        foreach ($query->all($this->db) as $row)
        {
            if(isset($this->items[$row['item_name']]))
                $this->$assignments[$row['user_id']][] = $row['item_name'];
        }*/
        
        $this->cache->set($this->cacheKey, [$this->items, $this->rules, $this->parents]);
    }
    
    public function invalidateCache() {
        Helper::invalidate();
        parent::invalidateCache();
    }
    
    /**
     * 是否为管理员
     * @param string|array $roles 指定的管理角色
     */
    public function isAdmin($roles = []){
        $roles = (empty($roles) || $roles == null) ? [] : (is_array($roles) ? $roles : [$roles]);
        
        $roles = array_merge(is_array($this->defaultAdminRole) ? $this->defaultAdminRole: [$this->defaultAdminRole], $roles);
        foreach ($roles as $role){
            if($this->checkAccess(Yii::$app->user->id, $role)){
                return true;
            }
        }
        return false;
    }
    
    /**
     * 用户分给角色或者权限
     * @param string|array $itemName
     * @param string|array $users
     * @return Assignment|bool 分配单个返回Assignment ;批量分配返回结果true 成功，false失败
     */
    public function assign($role,$userId){
        /* 两个同时为数组时失败 */
        if(is_array($userId) && is_array($role)){
            return false;
        }
        
        if(!is_array($userId) && !is_array($role)){
            $role = ($role instanceof Role) ? $role : $this->getRole($role);
            return parent::assign($role, $userId);
        }else{
            $rows =[];
            $time = time();
            if(is_array($userId)){
                foreach ($userId as $user_id){
                    $rows[]=[$role,$user_id,$time];
                }
            }else if(is_array($role)){
                foreach ($role as $role_name){
                    $rows[]=[$role_name,$userId,$time];
                }
            }
            
            try{
                $num = $this->db->createCommand()->batchInsert($this->assignmentTable, ['item_name','user_id','created_at'], $rows)->execute();
                $this->invalidateCache();
            } catch (Exception $ex) {
                $num = 0;
            }
            return $num > 0;
        }
    }
    /**
     * 删除用户的角色或者权限
     * @param string|array $itemName
     * @param string|array $users
     * @return bool 分配单个返回Assignment ;批量分配返回结果true 成功，false失败
     */
    public function revoke($role,$userId){
        if(empty($userId) || count($userId) == 0)
            return false;
        
        if(!is_array($userId) && !is_array($role)){
            $role = ($role instanceof Role) ? : $this->getRole($role);
            return parent::revoke($role, $userId);
        }else{
            try{
                $num = $this->db->createCommand()->delete($this->assignmentTable,['item_name' => $role,'user_id' => $userId])->execute();
                $this->invalidateCache();
            } catch (Exception $ex) {
                $num = 0;
            }
            return $num > 0;
        }
    }
    /**
     * @inheritdoc
     */
    public function getAssignments($userId)
    {
        if(isset($this->assignmentsCache[$userId]))
            return $this->assignmentsCache[$userId];
        else{
            $assignments = parent::getAssignments($userId);
            $this->assignmentsCache[$userId] = $assignments;
            return $assignments;
        }
    }
    
    /**
     * 获取属于该角色或者权限的所有用户
     * @param string $itemName  目标角色或者权限
     * @param array $result     []
     * @return 最终结果,所甩用户集合
     */
    protected function getItemUser($itemName,$result)
    {
        $atousers = isset($this->assignmentToUsers[$itemName]) ? $this->assignmentToUsers[$itemName] : [];
        $result = array_unique(ArrayHelper::merge($atousers,$result));
        if(isset($this->childs[$itemName]))
        {
            foreach ($this->childs[$itemName] as $child)
                $result = array_unique(ArrayHelper::merge($this->getItemUser($child,$result),$result));
        }
        return $result;
    }

    /**
     * 获取拥有该角色或者权限所有用户，<br/>
     * 比如，获取所有【编导】，或者所有能【创建预约】的用户
     * @param string $itemName  角色或者权限 [User]
     * @return array [User,User]
     */
    public function getItemUsers($itemName)
    {
        $User = components\Configs::userClass();
        
        $result = $User::find()
                ->leftJoin(['Assignment'=> $this->assignmentTable],"Assignment.user_id = id")
                ->where(['Assignment.item_name'=>$itemName])
                ->all();
        return $result;
    }
    /**
     * 获取拥有该角色或者权限所有用户，<br/>
     * 比如，获取所有【编导】，或者所有能【创建预约】的用户
     * @param string $itemName  角色或者权限 [User]
     * @return array [id=>name,id=>name]
     */
    public function getItemUserList($itemName){
        $User = components\Configs::userClass();
        $result = (new Query())
                ->select(['User.id','User.nickname'])
                ->from(['User'=>  $User::tableName()])
                ->leftJoin(['Assignment'=> $this->assignmentTable],"Assignment.user_id = User.id")
                ->where(['Assignment.item_name'=>$itemName])
                ->all(Yii::$app->db);
        return ArrayHelper::map($result, 'id', 'nickname');
    }
    
    /**
     * 获取拥有该路由的所有用户，<br/>
     * 比如 Url::to(['/rbac/route/index']) 或者 '/rbac/route/index'
     * @param string|array $routes  路由(
     *      Url::to(['/rbac/route/index']) 或者 '/rbac/route/index', 
     *      [Url::to(['/rbac/route/index']), Url::to(['/rbac/route/view'])] 或者 [''/rbac/route/index'', '/rbac/route/view']
     * )
     * @return array [id=>name,id=>name]
     */
    public function getItemUserLists($routes)
    {
        $User = components\Configs::userClass();
        $result = (new Query())
                ->select(['User.id', 'User.nickname'])
                ->from(['Permission' => $this->itemChildTable])
                ->leftJoin(['Role' => $this->itemChildTable], 'Role.child = Permission.parent')
                ->leftJoin(['Assignment'=> $this->assignmentTable],"Assignment.item_name = Role.parent")
                ->leftJoin(['User' => $User::tableName()], 'User.id = Assignment.user_id')
                ->where(['Permission.child' => $routes])
               ->all(Yii::$app->db);
                
        return ArrayHelper::map($result, 'id', 'nickname');
    }

    /**
     * 判断用户是否属于{$roseName} 角色
     * @param type $roleName    目标角色
     * @param type $userId      目标id
     * @return boolean 
     */
    public function isRole($roleName,$userId)
    {
        return $this->checkAccess($userId, $roleName);
    }
}
