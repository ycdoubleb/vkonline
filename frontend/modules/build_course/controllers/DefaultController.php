<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Course;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `build_course` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->redirect(['my-course', 'utils' => 'bs_utils']);
    }
    
    /**
     * Renders the my_course view for the module
     * @return string
     */
    public function actionMyCourse()
    {
        return $this->render('my_course');
    }
    
    /**
     * Renders the my_video view for the module
     * @return string
     */
    public function actionMyVideo()
    {
        return $this->render('my_video');
    }
    
    /**
     * Renders the my_teacher view for the module
     * @return string
     */
    public function actionMyTeacher()
    {
        return $this->render('index');
    }
    
    /**
     * Displays a single Course model.
     * @param integer $id
     * @return mixed
     */
    public function actionViewCourse($id)
    {
        $model = $this->findCourseModel($id);
        
        return $this->render('view_course', [
            'model' => $model,
        ]);
    }
   
    /**
     * AddCourse a new Course model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddCourse()
    {
        $model = new Course();
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->render('add_course', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * EditCourse a new Course model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionEditCourse($id)
    {
        $model = $this->findCourseModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->render('edit_course', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * Finds the DemandTask model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Course the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Course::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
}
