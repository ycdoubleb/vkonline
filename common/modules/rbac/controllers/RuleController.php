<?php

namespace common\modules\rbac\controllers;

use common\models\rbac\ShootOwnAppraiseRule;
use common\models\rbac\ShootOwnCancelRule;
use common\models\rbac\ShootOwnRule;
use common\modules\rbac\models\BizRule;
use common\modules\rbac\models\searchs\BizRuleSearch;
use common\modules\rbac\RbacName;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\rbac\ManagerInterface;
use yii\web\Controller;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;

class RuleController extends Controller
{
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
        $searchModel = new BizRuleSearch();
        $dataProvider = $searchModel->search(Yii::$app->getRequest()->getQueryParams());
        
        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel
        ]);
    }
    
    public function actionCreate()
    {
        /* @var $manager ManagerInterface */
        $manager = Yii::$app->authManager;
        $rules = $manager->getRules();
        
        /* 所有者规则 */;
        $this->addRule(new ShootOwnRule(),$rules);
        /* 所有者取消规则 */;
        $this->addRule(new ShootOwnCancelRule(),$rules);
        /* 添加评价规则,只有该任务的【接洽人】和【摄影师】才有权限进行评价 */
        $this->addRule(new ShootOwnAppraiseRule(),$rules);
        
        
        return $this->redirect('index');
    }
    
    private function addRule($rule,$rules)
    {
        /* @var $manager ManagerInterface */
        $manager = Yii::$app->authManager;
        
        if(isset($rules[$rule->name]))
            $manager->update ($rule->name, $rule);
        else
            $manager->add ($rule);
        
        return $rule;
    }
    
    public function actionView($id)
    {
        return $this->render('view',['model'=>$this->findModel($id)]);
    }
    
    public function actionsDelete($id)
    {
        Yii::$app->authManager->remove ($this->findModel($id)->getItem());
        return $this->redirect('index');
    }
    
    public function actionsDeleteAll($id)
    {
        Yii::$app->authManager->removeAllRules();
        return $this->redirect('index');
    }
    
    public function beforeAction($action) {
        if(parent::beforeAction($action))
        {
            if(!\Yii::$app->user->can(RbacName::PERMSSION_RBAC_ADMIN))
                throw new NotAcceptableHttpException('无权限操作！');
            else
                return true;
        }
    }


    /**
     * 
     * @param type $id
     * @return BizRule
     * @throws NotFoundHttpException
     */
    protected function findModel($id)
    {
        $rule = Yii::$app->authManager->getRule($id);
        if($rule != null)
            return new BizRule($rule);
        else
            throw  new NotFoundHttpException('The requested page does not exist.');
    }
}
