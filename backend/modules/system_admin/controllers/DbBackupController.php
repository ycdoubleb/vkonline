<?php

namespace backend\modules\system_admin\controllers;

use backend\models\SqlBackup;
use common\models\Dbbackup;
use common\models\searchs\DbbackupSearch;
use kartik\base\Config;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * 管理数据库备份的创建与下载
 *
 * @author Administrator
 */
class DbBackupController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
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
     * Lists all Config models.
     * @return mixed
     */
    public function actionIndex() {
        $searchModel = new DbbackupSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Config model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * 创建当前数据库的备份，并且以当前时间命名
     * @param integer $$include_data 是否包括数据：0否 1是;默认备份表结构和数据
     * @return mixed
     */
    public function actionCreate($include_data = 1) {
        $sql = new SqlBackup ();

        $tables = $sql->getTables();

        if (!$sql->startBackup()) {
            // render error
            Yii::$app->user->setFlash('success', "Error");
            return $this->render('index');
        }

        foreach ($tables as $tableName) {
            $sql->getColumns($tableName);
        }
        if ($include_data) {
            foreach ($tables as $tableName) {
                $sql->getData($tableName);
            }
        }

        $sql->endBackup();
        
        $model = new Dbbackup();
        $model->name = basename($sql->file_name);
        $model->path = $sql->file_name;
        $model->size = filesize($sql->file_name);
        
        if(!$model->save()){
            Yii::$app->user->setFlash('error', $model->getErrors());
        }

        $this->redirect('index');
    }

    /**
     * Updates an existing Config model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                        'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Config model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
    
    /**
     * 下载
     * @param integer $id
     */
    public function actionDownload($id){
        $model = Dbbackup::findOne($id);
        if($model){
            throw new NotFoundHttpException('The requested page does not exist.');
        }
        Yii::$app->response->sendFile($model->path, $model->name);
    }

    /**
     * Finds the Config model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Config the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        if (($model = Dbbackup::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

}
