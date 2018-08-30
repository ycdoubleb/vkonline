<?php

namespace backend\modules\helpcenter_admin\controllers;

use common\models\helpcenter\PostCategory;
use common\models\helpcenter\searchs\PostCategorySearch;
use common\widgets\grid\GridViewChangeSelfController;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * CategoryController implements the CRUD actions for PostCategory model.
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
     * Lists all PostCategory models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PostCategorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single PostCategory model.
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
     * Creates a new PostCategory model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new PostCategory();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            $model->updateParentPath();
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'parents' => !empty($model->app_id) ? ArrayHelper::map($this->getParentCats($model->app_id), 'id', 'name') : [],
            ]);
        }
    }

    /**
     * Updates an existing PostCategory model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $parentsdata = $this->getParentCats($model->app_id);

        if ($model->load(Yii::$app->request->post())) {
            /** 开启事务 */
            $trans = Yii::$app->db->beginTransaction();
            try
            {
                $moveCatChildrens  = PostCategory::getCatChildren($model->id, false, false, true);     //移动分类下所有子级
                if($model->save()){
                    $model->updateParentPath();     //修改路径
                    foreach($moveCatChildrens as $moveChildren){
                        //获取修改子集的Category模型
                        $childrenModel = PostCategory::findOne($moveChildren['id']);
                        $childrenModel->updateParentPath(); //修改子集路径
                        //计算路径中','出现的次数，确定为自身等级
                        $childrenModel->level = substr_count($childrenModel->parent_id_path, ',');
                        $childrenModel->update(false, ['level']);
                    }
                }else{
                    throw new Exception($model->getErrors());
                }
                $trans->commit();  //提交事务
                Yii::$app->getSession()->setFlash('success','操作成功！');
                return $this->redirect(['view', 'id' => $model->id]);
            } catch (Exception $ex) {
                $trans ->rollBack(); //回滚事务
                Yii::$app->getSession()->setFlash('error','操作失败::'.$ex->getMessage());
            }
        }
        return $this->render('update', [
            'model' => $model,
            'parents' => ArrayHelper::map($parentsdata, 'id', 'name'),
        ]);
    }

    /**
     * Deletes an existing PostCategory model.
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
     * Finds the PostCategory model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return PostCategory the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = PostCategory::findOne($id)) !== null) {
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
        try {
            $items = $this->getParentCats($id);
        } catch (Exception $ex) {
            $errors [] = $ex->getMessage();
        }
        return [
            'type' => 'S',
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
        $parentCats = PostCategory::find()->where(['app_id' => $app_id])
                ->orderBy('parent_id_path')->asArray()->all();
        //除顶级菜单外缩进两格(圆角符号下的空格)
        foreach ($parentCats as &$parentCat) {
            $parentCat ['name'] = str_repeat('　　', $parentCat ['level'] - 1) . $parentCat ['name'];
        }
        return $parentCats;
    }

}
