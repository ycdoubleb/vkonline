<?php

namespace common\modules\rbac\controllers;

use common\modules\rbac\components\Configs;
use common\modules\rbac\models\searchs\AssignmentSearch;
use common\modules\rbac\RbacManager;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\rbac\Assignment;
use yii\rbac\Item;
use yii\rbac\ManagerInterface;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class AssignmentController extends Controller
{
    public $userClassName;
    public $idField = 'id';
    public $usernameField = 'username';
    public $nicknameField = 'nickname';
    public $searchClass;
    
    public function init() 
    {
        parent::init();
        if($this->userClassName === null)
        {
            //$this->userClassName = Yii::$app->getUser()->identity;
            $this->userClassName = Configs::userClass();
        }
    }
    
    public function behaviors() 
    {
        return [
             //验证delete时为post传值
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
            //access验证是否有登录
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }
    
    public function actionIndex()
    {
        /* @var  $searchModel yii\db\Model */
        if($this->searchClass === null)
            $searchModel = new AssignmentSearch();
        else
            $searchModel = new $this->searchClass();
        
        $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams(), $this->userClassName, $this->usernameField);
        
        return $this->render('index',[
            'dataProvider'=>$dataProvider,
            'searchModel'=>$searchModel,
            'usernameField'=>$this->usernameField,
            'nicknameField'=>$this->nicknameField
        ]);
    }
    
    public function actionView($id)
    {
        Yii::$app->authManager->getAssignments($id);
        return $this->render('view',[
            'model'=>$this->findModel($id),
            'idField'=>$this->idField,
            'usernameField'=>$this->usernameField
        ]);
    }
    
    /**
     * 分配与取消分配
     * @param string $id 用户
     * @param string $action 动作 assign/remove
     */
    public function actionAssign()
    {
        Yii::$app->getResponse()->format = 'json';
        /* @var $authManager  RbacManager */
        $authManager = Yii::$app->authManager;
        $post = Yii::$app->getRequest()->post();
        
        $id = $post['id'];
        $action = $post['action'];
        $items = $post['items'];
        $item = null;
        $errors = [];
        
        if($action === 'assign')
        {
            try
            {
                foreach($items as $itemName)
                {
                    $item = $authManager->getRole($itemName);
                    $item = $item ?  : $authManager->getPermission($itemName);
                    
                    $authManager->assign($item, $id);
                    $authManager->invalidateCache();
                    
                    $errors[] = $item;
                }
            } catch (\Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }else
        {
            try
            {
                foreach($items as $itemName)
                {
                    $item = $authManager->getRole($itemName);
                    $item = $item ?  : $authManager->getPermission($itemName);
                    
                    $authManager->revoke($item, $id);
                    $authManager->invalidateCache();
                }
            } catch (\Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }
        
        return [
            'type'=>'S',
            'errors'=>$errors
        ];
    }
    
    /**
     * 
     * @param integer $id           用户id
     * @param string $target        avaliable/assigned     
     * @param string $term          过滤字符
     */
    public function actionSearch($id,$target,$term)
    {
        Yii::$app->getResponse()->format = 'json';
        /* @var $authManager ManagerInterface */
        $authManager = Yii::$app->authManager;
        $roles = $authManager->getRoles();
        $permssions = $authManager->getPermissions();
        $avaliable = [
            'Roles'=>[],
            'Permissions'=>[]
        ];
        $assigned = [
            'Roles'=>[],
            'Permissions'=>[]
        ];
        
        foreach($authManager->getAssignments($id) as $itemName=>$assignment)
        {
            if(isset($roles[$itemName]))
            {
                if(empty($term) || strpos($itemName,$term ) !== false)
                    $assigned['Roles'][$itemName] = $this->getAssignmentObject($assignment,$roles[$itemName]);
                unset($roles[$itemName]);
            }else if(isset ($permssions[$itemName]))
            {
                if(empty($term) || strpos($itemName, $term) !== false)
                    $assigned['Permissions'][$itemName] = $this->getAssignmentObject($assignment,$permssions[$itemName]);
                unset($permssions[$itemName]);
            }
        }
        if($target === 'avaliable')
        {
            foreach($roles as $name=>$item)
                $avaliable['Roles'][$name] = $item;
            foreach ($permssions as $name=>$item)
                $avaliable['Permissions'][$name] = $item;
            return array_filter($avaliable);
        }else
            return array_filter($assigned);
    }
    
    /**
     * 合成一个新对象
     * @param Assignmentnt
     * @param Item $role 对应角色/权限
     * @return  Objectg Description
     */
    private function getAssignmentObject($assignment,$role)
    {
        return [
            'userId' => $assignment->userId,
            'name' => $assignment->roleName,
            'createdAt' => $assignment->createdAt,
            'description' => $role->description
        ];
    }
    
    /**
     * 查找
     * @param integer $id 
     * @return Assignment the load model
     * @throws NotFoundHttpException
     */
    private function findModel($id)
    {
        /* @var $class yii\db\Model */
        $class = $this->userClassName;
        if(($model = $class::findIdentity($id)) !== null)
            return $model;
        else
            throw new NotFoundHttpException;
    }
}
