<?php

namespace frontend\modules\admin_center\controllers;

use common\models\vk\Category;
use common\models\vk\CourseAttribute;
use common\models\vk\searchs\CourseAttributeSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * AttributeController implements the CRUD actions for CourseAttribute model.
 */
class AttributeController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            //access验证是否有登录
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ],
        ];
    }

    /**
     * Lists all CourseAttribute models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CourseAttributeSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CourseAttribute model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
            'categoryName' => $this->getCategoryName(),
        ]);
    }

    /**
     * Creates a new CourseAttribute model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate($category_id = null)
    {
        $model = new CourseAttribute(['category_id' => $category_id]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/admin_center/category/view', 'id' => $model->category_id]);
        }
        
        return $this->render('create', [
            'model' => $model,
            'category' => $this->getCategory($model->category_id),
        ]);
    }

    /**
     * Updates an existing CourseAttribute model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/admin_center/category/view', 'id' => $model->category_id]);
        }

        return $this->render('update', [
            'model' => $model,
            'category' => $this->getCategory($model->category_id),
        ]);
    }

    /**
     * Deletes an existing CourseAttribute model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $model->is_del = 1;
        $model->save(false, ['is_del']);

        return $this->redirect(['/admin_center/category/view', 'id' => $model->category_id]);
    }

    /**
     * Finds the CourseAttribute model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return CourseAttribute the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CourseAttribute::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 获取分类名
     * @return array
     */
    private function getCategoryName()
    {
        $category = Category::find()->select(['id', 'name'])->orderBy('sort_order')
                ->all();
        return  ArrayHelper::map($category, 'id', 'name');
    }
    
    /**
     * 获取分类
     * @param int $category_id  分类ID
     * @return array
     */
    private function getCategory($category_id)
    {
        $category = Category::find()->select(['id', 'path'])
                ->andFilterWhere(['id' => $category_id])
                ->orderBy('sort_order')
                ->one();
        return $category;
    }
}
