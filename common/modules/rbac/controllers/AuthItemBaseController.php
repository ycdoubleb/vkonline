<?php

namespace common\modules\rbac\controllers;

use common\modules\rbac\components\Configs;
use common\modules\rbac\models\AuthGroup;
use common\modules\rbac\models\AuthItem;
use common\modules\rbac\models\searchs\AuthItemSearch;
use common\modules\rbac\RbacManager;
use Yii;
use yii\base\NotSupportedException;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\rbac\Item;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AuthItemBaseController extends Controller
{
    const AUTH_ITEM_TABLE = '{{%auth_item}}';
    const ITEM_CHILD_TABLE = '{{%auth_item_child}}';
    const ASSIGNMENT_TABLE = '{{%auth_assignment}}';
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all AuthItem models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new AuthItemSearch(['type' => $this->type]);
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
            'authGroups' => $this->getAuthGroup(),
        ]);
    }

    /**
     * Displays a single AuthItem model.
     * @param  string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        return $this->render('view', [
            'model'     => $model,
            'parents'   => $this->getItemParents($id),
            'childs'    => $this->getItemChildren($id),
            'users'     => $this->getItemUsers($id)
        ]);
    }

    /**
     * Creates a new AuthItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new AuthItem(null);
        $model->type = $this->type;
        if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->name]);
        } else {
            return $this->render('create', ['model' => $model,'authGroups' => $this->getAuthGroup()]);
        }
    }

    /**
     * Updates an existing AuthItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param  string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->getRequest()->post()) && $model->save()) {
            /* @var $rbacManager RbacManager */
            $rbacManager = Yii::$app->authManager;
            $rbacManager->invalidateCache();
            return $this->redirect(['view', 'id' => $model->name]);
        }
        return $this->render('update', ['model' => $model,'authGroups' => $this->getAuthGroup()]);
    }

    /**
     * Deletes an existing AuthItem model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param  string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->getAuthManager()->remove($model->item);
        return $this->redirect(['index']);
    }

    /**
     * Assign items
     * @param string $id
     * @return array
     */
    public function actionAssign($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $success = $model->addChildren($items);
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * Assign or remove items
     * @param string $id
     * @return array
     */
    public function actionRemove($id)
    {
        $items = Yii::$app->getRequest()->post('items', []);
        $model = $this->findModel($id);
        $success = $model->removeChildren($items);
        Yii::$app->getResponse()->format = 'json';

        return array_merge($model->getItems(), ['success' => $success]);
    }

    /**
     * Label use in view
     * @throws NotSupportedException
     */
    public function labels()
    {
        throw new NotSupportedException(get_class($this) . ' does not support labels().');
    }

    /**
     * Type of Auth Item.
     * @return integer
     */
    public function getType()
    {
        throw new \Exception('必须重写该方法');
    }
    
    /**
     * 
     * @return RbacManager
     */
    protected function getAuthManager(){
        return Yii::$app->authManager;
    }
    
    /**
     * 获取所有模块分类
     * @return type
     */
    protected function getAuthGroup()
    {
        $categorys = (new Query())
                    ->select(['id', 'name'])
                    ->from(AuthGroup::tableName())
                    ->orderBy('sort_order')
                    ->all();
        
        return ArrayHelper::map($categorys, 'id', 'name');
    }
    
    /**
     * 获取权限的父级
     * @param string $name
     * @return $result
     */
    protected function getItemParents($name)
    {
        $result = (new Query())
            ->select([
                'ItemParent.name AS name','ItemParent.type AS type','ItemParent.description AS des',
                'Group.id AS group_id','Group.name AS group_name'
                ])
            ->from(['ItemChild' => self::ITEM_CHILD_TABLE])
            ->leftJoin(['ItemParent' => self::AUTH_ITEM_TABLE], 'ItemParent.name = ItemChild.parent')
            ->leftJoin(['ItemGroup' => AuthItem::ITEM_GTOUP_TABLENAME], 'ItemParent.name = ItemGroup.item_name')
            ->leftJoin(['Group' => AuthGroup::tableName()], 'ItemGroup.group_id = Group.id')
            ->where(['ItemChild.child' => $name])
            ->all();
        
        return $result;
    }
    
    /**
     * 获取权限的子级
     * @param string $name
     * @return $result
     */
    protected function getItemChildren($name)
    {
        $result = (new Query())
            ->select([
                'ItemChild.name AS name','ItemChild.type AS type','ItemChild.description AS des',
                'Group.id AS group_id','Group.name AS group_name'
                ])
            ->from(['ItemChildMap' => self::ITEM_CHILD_TABLE])
            ->leftJoin(['ItemChild' => self::AUTH_ITEM_TABLE], 'ItemChild.name = ItemChildMap.child')
            ->leftJoin(['ItemGroup' => AuthItem::ITEM_GTOUP_TABLENAME], 'ItemChild.name = ItemGroup.item_name')
            ->leftJoin(['Group' => AuthGroup::tableName()], 'ItemGroup.group_id = Group.id')
            ->where(['ItemChildMap.parent' => $name])
            ->all();
        
        return $result;
    }
    
    /**
     * 获取该权限被分配的对应用户
     * @param string $name         权限名
     * @return array
     */
    protected function getItemUsers($name)
    {
        $User = Configs::userClass();
        
        $roleItems = $this->getItemParents($name);
        $itemName = ArrayHelper::getColumn($roleItems, 'name');
        $itemName += [$name];
        $userItems = (new Query())
            ->select(['User.id AS user_id','User.nickname AS nickname','Item.description AS item_name'])
            ->from(['UserToItem' => self::ASSIGNMENT_TABLE])
            ->leftJoin(['User' => $User::tableName()], 'User.id = UserToItem.user_id')
            ->leftJoin(['Item' => self::AUTH_ITEM_TABLE], 'Item.name = UserToItem.item_name')    
            ->where(['UserToItem.item_name' => $itemName])
            ->all();
       
        return $userItems;
    }

    /**
     * Finds the AuthItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return AuthItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $auth = $this->getAuthManager();
        $item = $this->type === Item::TYPE_ROLE ? $auth->getRole($id) : $auth->getPermission($id);
        if ($item) {
            $result = (new Query())
                    ->from(AuthItem::ITEM_GTOUP_TABLENAME)
                    ->where(['item_name'=>$id])
                    ->one();
            $authItem = new AuthItem($item,['group_id' => $result['group_id']]);
            return $authItem;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
