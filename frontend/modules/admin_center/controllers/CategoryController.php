<?php

namespace frontend\modules\admin_center\controllers;

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\searchs\CategorySearch;
use common\widgets\grid\GridViewChangeSelfController;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends GridViewChangeSelfController
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
     * 自定义更新分类层级
     * @param string $categoryIds
     * @return mixed
     */
    public function actionUpdateLevel($categoryIds)
    {
        //切割字符串为数组并过滤空值
        $cat_ids = array_filter(explode(',',$categoryIds));
        //获取到当前客户下和去除已选择的分类
        $dataProvider = $this->getAllCategory($cat_ids);

        return $this->renderAjax('update-level',[
            'dataProvider' => $dataProvider,
            'categoryIds' => $categoryIds
        ]);
    }

    /**
     * 保存自定义更新分类层级
     * @return array
     */
    public function actionSaveLevel()
    {
        \Yii::$app->getResponse()->format = 'json';
        $catId = ArrayHelper::getValue(Yii::$app->request->post(), 'cat_id');   //移动到的目标分类ID
        $childrenIds = ArrayHelper::getValue(Yii::$app->request->post(), 'children_id');    //需要移动的所有分类ID
        
        $catQuery = Category::getCatById($catId);   //目标分类模型
        //分割字符串为数组并过滤空值
        $chil_ids = array_filter(explode(',', $childrenIds));
        //获取移动的分类模型（数组）
        $chilQuerys = Category::find()->select(['id', 'name', 'parent_id', 'path', 'level'])
                ->where(['is_show' => 1])->andFilterWhere(['IN', 'id', $chil_ids])
                ->all();
        //获取移动的分类的所有等级
        foreach ($chilQuerys as $chilQuery) {
            $level[] = $chilQuery->level;
        }
        //计算移动后的总层级（移动的层级+目标层级）
        $countLevel = max($level) - min($level) + 1 + $catQuery->level; 

        if($countLevel <= 4){   //移动后的总层级小于等于4才能保存 否则不保存
            foreach ($chilQuerys as $chilQuery) {
                //移动的分类的父级ID是否存在于需要移动的ID中（存在多级一起移动的情况 true）
                if(in_array($chilQuery->parent_id, $chil_ids)){
                    if($chilQuery->level == 4){     //移动的层级为4时 (移动的分类存在3个层级级一起移动的情况 true)
                        $parQuery = Category::getCatById($chilQuery->parent_id);
                        $chilQuery->path = "$catQuery->path" . ",$parQuery->parent_id,$chilQuery->parent_id,$chilQuery->id";
                    } else {    //移动的层级不为4 （移动的分类存在2个层级一起移动的情况）
                        $chilQuery->path = "$catQuery->path" . ",$chilQuery->parent_id,$chilQuery->id";
                    }
                    $chilQuery->parent_id = $chilQuery->parent_id;
                    $chilQuery->level = substr_count($chilQuery->path, ',');
                } else {        //移动的分类不存多级一起移动的情况
                    $chilQuery->parent_id = $catId;
                    $chilQuery->path = "$catQuery->path" . ",$chilQuery->id";
                    $chilQuery->level = $catQuery->level + 1;
                }
                $chilQuery->update(false, ['parent_id', 'path', 'level']);
            }
            $result = [
                'code' => 200,
                'message' => 'success',
            ];
            Category::invalidateCache();        //取消缓存
            Yii::$app->getSession()->setFlash('success', '操作成功！');
        } else {
            $result = [
                'code' => 400,
                'message' => 'error',
            ];
            Yii::$app->getSession()->setFlash('error', '操作失败!移动后的分类层级不能超过4级');
        }

        return $result;
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
    
    /**
     * 获取到当前客户下和去除已选择的分类
     * @param srray $cat_ids 已选择的分类id
     * @return array
     */
    protected function getAllCategory($cat_ids){
        //公共的分类和属于客户的分类
        $filter = [' ', Yii::$app->user->identity->customer_id];

        $query = Category::find()
                ->select(['id', 'name', 'parent_id', 'path'])
                ->where(['is_show' => 1])
                ->andFilterWhere(['IN', 'customer_id', $filter])    //过滤非当前客户下的分类
                ->andFilterWhere(['NOT IN', 'id', $cat_ids]);       //过滤已选择的分类

        $query->orderBy(['path' => SORT_ASC]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 1000,
            ],
        ]);
        
        return $dataProvider;
    }
}
