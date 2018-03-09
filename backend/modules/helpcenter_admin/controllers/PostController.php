<?php

namespace backend\modules\helpcenter_admin\controllers;

use backend\components\BaseController;
use common\models\helpcenter\Post;
use common\models\helpcenter\PostCategory;
use common\models\helpcenter\searchs\PostSearch;
use common\models\User;
use Yii;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends BaseController
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
        ];
    }

    /**
     * Lists all Post models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'belongCategory' => $this->getBelongCategory(),
            'createdBy' => $this->getCreatedBy(),
        ]);
    }

    /**
     * Displays a single Post model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Post(['created_by' => Yii::$app->user->id]);
        
        $post = Yii::$app->request->post();
        if (isset($post['app_id'])) {
            unset($post['app_id']);
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'parents' => !empty($model->app_id) ? ArrayHelper::map($this->getParentCats($model->app_id), 'id', 'name') : [],
            ]);
        }
    }

    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = Yii::$app->request->post();
        if (isset($post['app_id'])) {
            unset($post['app_id']);
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'parents' => ArrayHelper::map($this->getParentCats($model->parent->app_id), 'id', 'name'),
            ]);
        }
    }

    /**
     * Deletes an existing Post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
    
    
    /**
     * 返回该$app_id下的所有分类
     * @param type $id          应用ID
     * @return array
     */
    public function actionSearchCats($id)
    {
        Yii::$app->getResponse()->format = 'json';
        
        $errors = [];
        $items = [];
        try{
            $items = $this->getParentCats($id);
        } catch (Exception $ex) {
            $errors [] = $ex->getMessage();
        }
        return [
            'type'=>'S',
            'data' => $items,
            'error' => $errors
        ];
    }
    
    /**
     * 根据$app_id查找相对应的分类
     * @param type $app_id          应用ID
     * @return array
     */
    public function getParentCats($app_id)
    {
        $parentCats =  PostCategory::find()->where(['app_id'=>$app_id])->asArray()->all();
         //除顶级菜单外缩进两格(圆角符号下的空格)
        foreach ($parentCats as &$parentCat) {
            $parentCat ['name'] = str_repeat('　　', $parentCat ['level'] - 1) . $parentCat ['name'];
        }
        return $parentCats;
    }
    
    /**
     * 查找所属分类
     * @return array
     */
    public function getBelongCategory() 
    {
        $belongCategory = (new Query())
                ->select(['Post.id','Post.category_id'])
                ->from(['Post' => Post::tableName()])
                ->leftJoin(['PostCategory' => PostCategory::tableName()], 'PostCategory.id = Post.category_id')
                ->addSelect(['PostCategory.name'])
                ->all();
        
        return ArrayHelper::map($belongCategory, 'category_id', 'name');
    }

    /**
     * 查询创建者
     * @return array
     */
    public function getCreatedBy() 
    {
        $createdBy = (new Query())
                ->select(['Post.id', 'Post.created_by'])
                ->from(['Post' => Post::tableName()])
                //关联查询上传者
                ->leftJoin(['CreateBy' => User::tableName()], 'CreateBy.id = Post.created_by')
                ->addSelect(['CreateBy.nickname AS username'])
                ->distinct()
                ->all();
       
        return ArrayHelper::map($createdBy, 'created_by', 'username');
    }
    
}
