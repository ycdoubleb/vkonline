<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Knowledge;
use common\models\vk\searchs\VideoFavoriteSearch;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\TagRef;
use common\models\vk\Teacher;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\utils\DateUtil;
use common\utils\StringUtil;
use frontend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
     * 创建 一个新的 Knowledge 模块
     * 如果创建成功，返回json数据
     * @param string $node_id
     * @return mixed
     */
    public function actionCreate($node_id)
    {
        $model = new Knowledge(['node_id' => $node_id, 'created_by' => \Yii::$app->user->id]);
        $model->loadDefaultValues();
        
        if(ActionUtils::getInstance()->getIsHavePermission($model->node->course_id, true)){
            if($model->node->course->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{beenPublished}{canNot}{Add}', [
                    'beenPublished' => Yii::t('app', 'The course has been published,'),
                    'canNot' => Yii::t('app', 'Can not be '), 'Add' => Yii::t('app', 'Add')
                ]));
            }
            if($model->node->course->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
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

        if(ActionUtils::getInstance()->getIsHavePermission($model->node->course_id, true)){
            if($model->node->course->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{beenPublished}{canNot}{Edit}', [
                    'beenPublished' => Yii::t('app', 'The course has been published,'),
                    'canNot' => Yii::t('app', 'Can not be '), 'Edit' => Yii::t('app', 'Edit')
                ]));
            }
            if($model->node->course->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->updateKnowledge($model, Yii::$app->request->post());
        } else {
            return $this->renderAjax('update', [
                'model' => $model,  //模型,
                //如果有引用资源加载单个video详细
                'videoDetail' => $model->has_resource ? 
                    $this->findVideoDetails($model->knowledgeVideo->video_id) : [],
            ]);
        }
    }

    /**
     * 删除 现有的 Knowledge 模型。
     * 如果删除成功，返回json数据
     * @param string $id
     * @return json
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if(ActionUtils::getInstance()->getIsHavePermission($model->node->course_id, true)){
            if($model->node->course->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{beenPublished}{canNot}{Delete}', [
                    'beenPublished' => Yii::t('app', 'The course has been published,'),
                    'canNot' => Yii::t('app', 'Can not be '), 'Delete' => Yii::t('app', 'Delete')
                ]));
            }
            if($model->node->course->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if (Yii::$app->request->isPost) {
            Yii::$app->getResponse()->format = 'json';
            return ActionUtils::getInstance()->deleteKnowledge($model);
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
        $videos = array_values($results['data']['video']);    //视频数据
        //重修视频数据里面的元素值
        foreach ($videos as $index => $item) {
            $videos[$index]['img'] = StringUtil::completeFilePath($item['img']);
            $videos[$index]['duration'] = DateUtil::intToTime($item['duration']);
            $videos[$index]['is_disabled'] = $item['mts_status'] != Video::MTS_STATUS_YES ? 'disabled' : '';
        }
        
        //分页查询
        if(isset($results['filter']['page'])){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => $videos, 
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
            'type' => UserCategory::TYPE_MYCOLLECT,
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
        $videos = array_values($results['data']['video']);    //视频数据
        //重修视频数据里面的元素值
        foreach ($videos as $index => $item) {
            $videos[$index]['img'] = StringUtil::completeFilePath($item['img']);
            $videos[$index]['duration'] = DateUtil::intToTime($item['duration']);
            $videos[$index]['is_disabled'] = $item['mts_status'] != Video::MTS_STATUS_YES ? 'disabled' : '';
        }
        
        //分页查询
        if(isset($results['filter']['page'])){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => $videos, 
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
            'type' => UserCategory::TYPE_MYVIDOE,
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
        $results = $searchModel->adminCenterSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 15]), false);
        $videos = array_values($results['data']['video']);    //视频数据
        //重修视频数据里面的元素值
        foreach ($videos as $index => $item) {
            $videos[$index]['img'] = StringUtil::completeFilePath($item['img']);
            $videos[$index]['duration'] = DateUtil::intToTime($item['duration']);
            $videos[$index]['is_disabled'] = $item['mts_status'] != Video::MTS_STATUS_YES ? 'disabled' : '';
        }
        
        //分页查询
        if(isset($results['filter']['page'])){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => $videos, 
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
        $results = $this->findVideoDetails($video_id);
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                if($results[0]['mts_status'] == Video::MTS_STATUS_YES){
                    return [
                        'code'=> 200,
                        'data' => [
                            'result' => $results, 
                        ],
                        'message' => '请求成功！',
                    ];
                }else{
                    return [
                        'code'=> 404,
                        'data' => $results[0]['mts_status'],
                        'message' => '请求失败::引用的视频必须为已转码。' ,
                    ];
                }
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
    
    /**
     * 查询单个 video 的详细
     * @param string $video_id
     * @return array
     */
    protected function findVideoDetails($video_id)
    {
        $query = (new Query())->select(['Video.id'])->from(['Video' => Video::tableName()]);
        $query->where(['Video.id' => $video_id]);
        //复制视频对象
        $copyVideo= clone $query;
        //查询视频下的标签
        $tagRefQuery = TagRef::getTagsByObjectId($copyVideo, 2, false);
        $tagRefQuery->addSelect(["GROUP_CONCAT(Tags.`name` ORDER BY TagRef.id ASC SEPARATOR '、') AS tags"]);
        $query->addSelect([
            'Video.name', 'Video.img', 'Video.duration', 'Video.des', 
            'Video.created_at', 'Video.is_publish', 'Video.level', 'Video.mts_status',
            'Teacher.id AS teacher_id', 'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ]);
        $query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');
        $videoInfo = $query->one();
        //以视频id为键值
        $results = array_values(ArrayHelper::merge(ArrayHelper::index([$videoInfo], 'id'), 
            ArrayHelper::index($tagRefQuery->asArray()->all(), 'object_id')));
        
        //重修视频数据里面的元素值
        foreach ($results as $index => $item) {
            $results[$index]['img'] = StringUtil::completeFilePath($item['img']);
            $results[$index]['duration'] = DateUtil::intToTime($item['duration']);
            $results[$index]['des'] = Html::decode($item['des']);
            $results[$index]['created_at'] = Date('Y-m-d H:i', $item['created_at']);
            $results[$index]['level_name'] = Video::$levelMap[$item['level']];
            $results[$index]['teacher_avatar'] = StringUtil::completeFilePath($item['teacher_avatar']);
            $results[$index]['tags'] = isset($item['tags']) ? $item['tags'] : 'null';
        }
        
        return $results;
    }
}
