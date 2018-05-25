<?php

namespace frontend\modules\other\controllers;

use common\models\vk\UserFeedback;
use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;

/**
 * Default controller for the `other` module
 */
class DefaultController extends Controller
{
    public $layout = "main";
    
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
     * Renders the index view for the module
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionContact()
    {
        return $this->render('contact');
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionFeedback()
    {
        $model = new UserFeedback();
        
        if(!empty(\Yii::$app->user->id)){
            if ($model->load(Yii::$app->request->post()) && $model->save()) {
                Yii::$app->getSession()->setFlash('success','反馈成功，我们会尽快处理！');
                return $this->redirect(['feedback']);
            }

            return $this->render('feedback',[
                'model' => $model,
            ]);
        } else {
            return $this->redirect('/site/login');
        }
        
    }
}
