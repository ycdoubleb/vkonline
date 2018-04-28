<?php

namespace common\modules\rbac\controllers;

use common\modules\rbac\components\Configs;
use common\modules\rbac\models\AuthGroup;
use common\modules\rbac\models\AuthItem;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;

/**
 * RoleController implements the CRUD actions for AuthItem model.
 */
class RoleController extends AuthItemBaseController {

    /**
     * 为该角色分配权限
     * @return mixed
     */
    public function actionAddPermission($id) {
        $post = Yii::$app->request->post();

        if (\Yii::$app->getRequest()->isPost) {
            /**
             * 整理提交上来的权限
             */
            $items = [];
            foreach ($post['permissions'] as $permissions) {
                $items = array_merge($items, $permissions);
            }
            $model = $this->findModel($id);
            $model->addChildren($items);
            return $this->redirect(['view', 'id' => $id]);
        } else {
            return $this->renderAjax('_model_add_permission', [
                        'available' => $this->getAvailableItem($id),
                        'id' => $id,
            ]);
        }
    }

    /**
     * 删除角色分配权限
     * @return mixed
     */
    public function actionRemovePermission($id) {
        $post = Yii::$app->request->post();

        /**
         * 整理提交上来的权限
         */
        $items = [];
        if(isset($post['permissions'])){
            foreach ($post['permissions'] as $permissions) {
                $items = array_merge($items, $permissions);
            }
            $model = $this->findModel($id);
            $model->removeChildren($items);
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }
    
    /**
     * 分配角色给用户
     * @return mixed
     */
    public function actionAssignmentUser($id) {
        $post = Yii::$app->request->post();

        if (\Yii::$app->getRequest()->isPost) {
            /**
             * 整理提交上来的权限
             */
            $items = $post['users'];
            
            $success = Yii::$app->authManager->assign($id, $items);
                
            return $this->redirect(['view', 'id' => $id]);
        } else {
            return $this->renderAjax('_model_assignment_user', [
                        'available' => $this->getAvailableUser($id),
                        'id' => $id,
            ]);
        }
    }
    
    /**
     * 用户余除角色
     * @return mixed
     */
    public function actionRemoveAssignment($id) {
        $post = Yii::$app->request->post();

        if (\Yii::$app->getRequest()->isPost) {
            /**
             * 整理提交上来的权限
             */
            $items = isset($post['users']) ? $post['users'] : [] ;
            $success = Yii::$app->authManager->revoke($id, $items);
                
            return $this->redirect(['view', 'id' => $id]);
        } else {
            return $this->renderAjax('_model_assignment_user', [
                        'available' => $this->getAvailableUser($id),
                        'id' => $id,
            ]);
        }
    }

    /**
     * 获取未配置的权限
     * @param string $id 目标id
     */
    protected function getAvailableItem($id) {
        $model = $this->findModel($id);
        $available = [];
        foreach ($model->getItems()['available'] as $key => $value) {
            if ($value == 'permission')
                $available [] = $key;
        }
        $result = (new Query())
                ->select([
                    'Item.name AS name', 'Item.type AS type', 'Item.description AS des',
                    'Group.id AS group_id', 'Group.name AS group_name'
                ])
                ->from(['Item' => self::AUTH_ITEM_TABLE])
                ->leftJoin(['ItemGroup' => AuthItem::ITEM_GTOUP_TABLENAME], 'Item.name = ItemGroup.item_name')
                ->leftJoin(['Group' => AuthGroup::tableName()], 'ItemGroup.group_id = Group.id')
                ->where(['Item.name' => $available])
                ->all();
        return $result;
    }
    
    /**
     * 获取未分配的用户
     * @param type $id
     */
    protected function getAvailableUser($id){
        $User = Configs::userClass();
        
        $hasUser = $this->getItemUsers($id);
        $allUsers = $User::find()
                    ->select(['id','nickname'])
                    ->where(['status' => $User::STATUS_ACTIVE])
                    ->asArray()
                    ->all();
        $allUsers = ArrayHelper::map($allUsers, 'id', 'nickname');
        foreach ($hasUser as $user){
            unset($allUsers[$user['user_id']]);
        }
        return $allUsers;
    }

    public function getType() {
        return Item::TYPE_ROLE;
    }

}
