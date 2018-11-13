<?php

namespace dailylessonend\modules\build_course\controllers;

use common\components\aliyuncs\Aliyun;
use common\models\vk\Image;
use common\models\vk\searchs\ImageSearch;
use common\models\vk\TagRef;
use common\models\vk\UserCategory;
use common\modules\webuploader\models\Uploadfile;
use dailylessonend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * ImageController implements the CRUD actions for Image model.
 */
class ImageController extends Controller
{
    /**
     * {@inheritdoc}
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
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ]
                ],
            ]
        ];
    }

    /**
     * 列出所有 ImageSearch 模型。
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ImageSearch();
        $results = $searchModel->search(array_merge(Yii::$app->request->queryParams, ['limit' => 8]));
        $images = array_values($results['data']['image']);    //图像数据
        $user_cat_id = ArrayHelper::getValue($results['filter'], 'user_cat_id', null);  //用户分类id
        //重修课程数据里面的元素值
        foreach ($images as &$item) {
            $item['img'] = Aliyun::absolutePath(!empty($item['thumb_path']) ? $item['thumb_path'] : 'static/imgs/notfound.png');
        }
        
        //如果是ajax请求，返回json
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => $images, 
                        'page' => $results['filter']['page']
                    ],
                    'message' => '请求成功！',
                ];
            }catch (Exception2 $ex) {
                return [
                    'code'=> 404,
                    'data' => [],
                    'message' => '请求失败::' . $ex->getMessage(),
                ];
            }
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'filters' => $results['filter'],
            'totalCount' => $results['total'],   //总数量
            'locationPathMap' => UserCategory::getUserCatLocationPath($user_cat_id),  //所属目录位置
            'userCategoryMap' => $user_cat_id == null ? UserCategory::getCatsByLevel() : UserCategory::getCatChildren($user_cat_id),    //返回所有目录结构
        ]);
    }
    
    /**
     * 列出所有 ImageSearch 模型，搜索后的结果。
     * @return string|json
     */
    public function actionResult()
    {
        $searchModel = new ImageSearch();
        $results = $searchModel->search(array_merge(Yii::$app->request->queryParams));
        $user_cat_id = ArrayHelper::getValue($results['filter'], 'user_cat_id', null);  //用户分类id
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($results['data']['image']),
            'key' => 'id',
            'pagination' => [
                'pageSize' => 20,
            ]
        ]);
        
        return $this->render('result', [
            'searchModel' => $searchModel,      //搜索模型
            'dataProvider' => $dataProvider,    //搜索结果后的数据
            'filters' => $results['filter'],     //查询过滤的属性
            'totalCount' => $results['total'],   //总数量
            'locationPathMap' => UserCategory::getUserCatLocationPath($user_cat_id),  //所属目录位置
        ]);
    }

    /**
     * 显示一个单一的 Image 模型。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException 如果找不到模型
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        if($model->is_del){
            throw new NotFoundHttpException(Yii::t('app', 'The image does not exist.'));
        }
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * 创建 一个新的图像模型。
     * 如果创建成功，浏览器将被重定向到“查看”页。
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Image([
            'customer_id' => Yii::$app->user->identity->customer_id, 
            'user_cat_id' => ArrayHelper::getValue(Yii::$app->request->queryParams, 'user_cat_id'),
            'created_by' => Yii::$app->user->id
        ]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $is_success = ActionUtils::getInstance()->createImage($model, Yii::$app->request->post());
            if($is_success){
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'imageFiles' => json_encode([]),
        ]);
    }

    /**
     * 更新 现有图像模型。
     * 如果更新成功，浏览器将被重定向到“查看”页。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException 如果找不到模型
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by == Yii::$app->user->id || $model->userCategory->type == UserCategory::TYPE_SHARING){
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The image does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
       
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $is_success = ActionUtils::getInstance()->updateImage($model, Yii::$app->request->post());
            if($is_success){
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        
        return $this->render('update', [
            'model' => $model,
            'imageFiles' => json_encode(Uploadfile::getUploadfileByFileId($model->file_id)),    //已存在的视频文件
            'tagsSelected' => array_values(TagRef::getTagsByObjectId($model->id, 5)),   //已选的标签
        ]);
    }

    /**
     * 删除 现有图形模型。
     * 如果删除成功，浏览器将被重定向到“索引”页。
     * @param string $id
     * @return mixed
     * @throws NotFoundHttpException 如果找不到模型
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by == Yii::$app->user->id || $model->userCategory->type == UserCategory::TYPE_SHARING){
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The image does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if (Yii::$app->request->isPost) {
            $is_success = ActionUtils::getInstance()->deleteImage($model);
            if($is_success){
                return $this->redirect(['index']);
            }
        }
    }

    /**
     * 基于其主键值查找图像模型。
     * 如果找不到模型，将抛出404个HTTP异常。
     * @param string $id
     * @return Image the loaded model
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = Image::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
