<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Knowledge;
use common\models\vk\searchs\KnowledgeSearch;
use common\models\vk\searchs\VideoFavoriteSearch;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\TagRef;
use common\models\vk\Teacher;
use common\models\vk\Video;
use frontend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * KnowledgeController implements the CRUD actions for Knowledge model.
 */
class KnowledgeController extends Controller
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
                    //'delete' => ['POST'],
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
     * 创建 一个新的 Knowledge 模块
     * 如果创建成功，返回json数据
     * @param string $node_id
     * @return mixed
     */
    public function actionCreate($node_id)
    {
        $model = new Knowledge(['node_id' => $node_id, 'created_by' => \Yii::$app->user->id]);
        $model->loadDefaultValues();

        if(!ActionUtils::getInstance()->getIsHasEditNodePermission($model->node->course_id)){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->createKnowledge($model, Yii::$app->request->post());
        }else{
            return $this->renderAjax('create', [
                'model' => $model,  //模型
                'teacherMap' => Teacher::getTeacherByLevel(Yii::$app->user->id, 0, false),  //和自己相关的老师
            ]);
        }
    }

    /**
     * 更新 现有的 Knowledge 模型。
     * 如果更新成功，返回json数据
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if($model->created_by == \Yii::$app->user->id){
            if(!ActionUtils::getInstance()->getIsHasEditNodePermission($model->node->course_id)){
                throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->updateKnowledge($model, Yii::$app->request->post());
        } else {
            return $this->renderAjax('update', [
                'model' => $model,  //模型
                'teacherMap' => Teacher::getTeacherByLevel($model->created_by, 0, false),  //和自己相关的老师
            ]);
        }
    }

    /**
     * 删除 现有的 Knowledge 模型。
     * 如果删除成功，返回json数据
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if($model->created_by == \Yii::$app->user->id){
            if(!ActionUtils::getInstance()->getIsHasEditNodePermission($model->node->course_id)){
                throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->deleteKnowledge($model);
        } else {
            return $this->renderAjax('delete',[
                'model' => $model,  //模型
            ]);
        }
    }

    /**
     * 引用 自己收藏的视频
     * 如果是 page 非为空，返回成功的json数据，否则返回收藏的视频
     * @return json
     */
    public function actionMyCollect()
    {
        $searchModel = new VideoFavoriteSearch();
        $results = $searchModel->myCollectSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 15]));
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($results['data']['video']),
        ]);
        
        //分页查询
        if(isset($results['filter']['page'])){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => array_values($results['data']['video']), 
                        'page' => $results['filter']['page']
                    ],
                    'message' => '请求成功！',
                ];
            }catch (Exception $ex) {
                return [
                    'code'=> 404,
                    'data' => [],
                    'message' => '请求失败::' . $ex->getMessage(),
                ];
            }
        }
        
        return $this->renderAjax('reference', [
            'searchModel' => $searchModel,      //搜索模型
            'dataProvider' => $dataProvider,    //我收藏的视频数据
            'filters' => $results['filter'],    //查询过滤的属性
            'totalCount' => $results['total'],  //总数量
        ]);
    }
    
    /**
     * 引用 自己的视频
     * 如果是 page 非为空，返回成功的json数据，否则返回自己的视频
     * @return json
     */
    public function actionMyVideo()
    {
        $searchModel = new VideoSearch();
        $results = $searchModel->buildCourseSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 15]));
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($results['data']['video']),
        ]);
        
        //分页查询
        if(isset($results['filter']['page'])){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => array_values($results['data']['video']), 
                        'page' => $results['filter']['page']
                    ],
                    'message' => '请求成功！',
                ];
            }catch (Exception $ex) {
                return [
                    'code'=> 404,
                    'data' => [],
                    'message' => '请求失败::' . $ex->getMessage(),
                ];
            }
        }
        
        return $this->renderAjax('reference', [
            'searchModel' => $searchModel,      //搜索模型
            'dataProvider' => $dataProvider,    //我收藏的视频数据
            'filters' => $results['filter'],    //查询过滤的属性
            'totalCount' => $results['total'],  //总数量
        ]);
    }
    
    /**
     * 引用 品牌内部的视频
     * 如果是 page 非为空，返回成功的json数据，否则返回自己的视频
     * @return json
     */
    public function actionInsideVideo()
    {
        $searchModel = new VideoSearch();
        $results = $searchModel->adminCenterSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 15]));
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($results['data']['video']),
        ]);
        
        //分页查询
        if(isset($results['filter']['page'])){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => array_values($results['data']['video']), 
                        'page' => $results['filter']['page']
                    ],
                    'message' => '请求成功！',
                ];
            }catch (Exception $ex) {
                return [
                    'code'=> 404,
                    'data' => [],
                    'message' => '请求失败::' . $ex->getMessage(),
                ];
            }
        }
        
        return $this->renderAjax('reference', [
            'searchModel' => $searchModel,      //搜索模型
            'dataProvider' => $dataProvider,    //我收藏的视频数据
            'filters' => $results['filter'],    //查询过滤的属性
            'totalCount' => $results['total'],  //总数量
        ]);
    }
    
    /**
     * 选择 引用的资源视频
     * @param string $video_id
     * @return json
     */
    public function actionChoice($video_id)
    {
        $query = (new Query())->select(['Video.id'])->from(['Video' => Video::tableName()]);
        $query->where(['Video.id' => $video_id]);
        //复制视频对象
        $copyVideo= clone $query;
        //查询视频下的标签
        $tagRefQuery = TagRef::getTagsByObjectId($copyVideo, 2, false);
        $tagRefQuery->addSelect(["GROUP_CONCAT(Tags.`name` ORDER BY TagRef.id ASC SEPARATOR '、') AS tags"]);
        $query->addSelect([
            'Video.name', 'Video.img', 'Video.duration', 'Video.des', 'Video.created_at', 'Video.is_publish', 'Video.level',
            'Teacher.id AS teacher_id', 'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ]);
        $query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');
        $results = ArrayHelper::merge(ArrayHelper::index([$query->one()], 'id'), 
                ArrayHelper::index($tagRefQuery->asArray()->all(), 'object_id'));
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => array_values($results), 
                    ],
                    'message' => '请求成功！',
                ];
            }catch (Exception $ex) {
                return [
                    'code'=> 404,
                    'data' => [],
                    'message' => '请求失败::' . $ex->getMessage(),
                ];
            }
        }
    }

    /**
     * 基于其主键值找到 Knowledge 模型。
     * 如果找不到模型，将抛出404个HTTP异常。
     * @param string $id
     * @return Knowledge 加载模型
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = Knowledge::findOne(['id' => $id, 'is_del' => 0])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
