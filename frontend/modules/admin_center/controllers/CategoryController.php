<?php

namespace frontend\modules\admin_center\controllers;

use backend\components\BaseController;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CategorySearch;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends BaseController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
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
     * Lists all Category models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CategorySearch();
        $dataProvider = $searchModel->searchCustomerCategory(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Category model.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $model->courseAttribute,
        ]);
        
        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'path' => $this->getCategoryFullPath($model->id),
        ]);
    }

    /**
     * Creates a new Category model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Category();
        $parentId = ArrayHelper::getValue(\Yii::$app->request->queryParams, 'id');
        $parentModel = Category::findOne(['id' => $parentId]);

        if($parentModel->level > 3){
            throw new NotAcceptableHttpException('分类等级不能大于四级！');
        } else {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $model->updateParentPath();
                Category::invalidateCache();
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                $model->loadDefaultValues();
                return $this->render('create', [
                    'model' => $model,
                    'parentModel' => $parentModel,
                ]);
            }
        }
    }

    /**
     * Updates an existing Category model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if($model->parent_id == 0){
            throw new NotAcceptableHttpException('不能更改顶级分类！');
        } else {
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                $model->updateParentPath();
                Category::invalidateCache();
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                return $this->render('update', [
                    'model' => $model,
                ]);
            }
        }
    }

    /**
     * Deletes an existing Category model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $sunCategory = Category::findOne(['parent_id' => $model->id]);  //查找是否有子分类
        $hasCourse = Course::findOne(['category_id' => $model->id]);    //查找是否有属于该分类的课程
        
        if($model->parent_id == 0 || !empty($hasCourse) || !empty($sunCategory) || count($model->courseAttribute) > 0){
            throw new NotAcceptableHttpException('顶级分类或含有课程或子分类或属性！不能删除');
        } else {
            $model->delete();
            Category::invalidateCache();
        }
        
        return $this->redirect(['index']);
    }
    
    /**
     * 获取子级分类
     * @param type $id
     */
    public function actionSearchChildren($id){
        Yii::$app->getResponse()->format = 'json';
        return [
            'result' => 1,
            'data' => Category::getCatChildren($id),
        ];
    }

    /**
     * Finds the Category model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Category the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Category::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
    
    /**
     * 获取分类全路径
     * @param integer $categoryId
     * @return string
     */
    protected function getCategoryFullPath($categoryId) 
    {
        $parentids = array_values(array_filter(explode(',', Category::getCatById($categoryId)->path)));
        $path = '';
        foreach ($parentids as $index => $id) {
            $path .= ($index == 0 ? '' : ' \ ') . Category::getCatById($id)->name;
        }
        
        return $path;
    }
}
