<?php

namespace frontend\modules\test\controllers;

use common\components\aliyuncs\Aliyun;
use common\modules\webuploader\models\Uploadfile;
use common\modules\webuploader\models\UploadfileChunk;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;

/**
 * Default controller for the `test` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $files = Uploadfile::findAll(['created_by' => '1cf3a6b67d44d1bf5785147014894ce8']);
        $chunks = UploadfileChunk::find()->all();
        
        return $this->render('index',['files' => $files,'chunks' => $chunks]);
    }
    
    public function actionIndex2()
    {
        $files = Uploadfile::findAll(['created_by' => '1cf3a6b67d44d1bf5785147014894ce8']);
        $chunks = UploadfileChunk::find()->all();
        
        return $this->render('index_1',['files' => $files,'chunks' => $chunks]);
    }
    
    public function ActionPreview($file_id){
        return $this->render('preview',['model' => Uploadfile::findOne(['id' => $file_id])]);
    }
    
    
    public function actionClearFile(){
        Uploadfile::deleteAll(['created_by' => '1cf3a6b67d44d1bf5785147014894ce8']);
        $this->redirect('index');
    }
    
    /**
     * 
     */
    public function actionClearChunk(){
        UploadfileChunk::deleteAll();
        $this->redirect('index');
    }
    
    public function actionUploadFile(){
        $post = Yii::$app->request->post();
        if(count($post) >0){
            $upload = UploadedFile::getInstanceByName('img');
            Aliyun::getOss()->multiuploadFile('test.jpg', $upload->tempName);
        }
        return $this->render('upload-file');
    }
}
