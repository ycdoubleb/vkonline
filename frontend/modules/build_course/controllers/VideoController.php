<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\searchs\VideoFavoriteSearch;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\modules\webuploader\models\Uploadfile;
use common\utils\DateUtil;
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
 * Video controller for the `build_course` module
 */
class VideoController extends Controller
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
     * 列出所有 VideoSearch 模型。
     * @return string [
     *    filters => 查询过滤的属性, pagers => 分页, dataProvider => 课程数据, courseMap => 属于自己的课程
     * ]
     */
    public function actionIndex()
    {
        $searchModel = new VideoSearch();
        $result = $searchModel->buildCourseSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
        ]);
        
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> 200,
                'page' => $result['filter']['page'],
                'data' => array_values($result['data']['video']),
                'message' => '请求成功！',
            ];
        }
      
        return $this->render('index', [
            'searchModel' => $searchModel,
            'filters' => $result['filter'],
            'totalCount' => $result['total'],
            'dataProvider' => $dataProvider,
            'courseMap' => $this->getCourseByCreatedBy(),
        ]);
    }
    
    /**
     * 显示一个单一的 Course 模型。
     * @param string $id
     * @return mixed [
     *      model => 模型, dataProvider => 所有相关课程数据
     * ]
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModel = new VideoSearch();
        
        return $this->render('view', [
            'model' => $model,
            'dataProvider' => $searchModel->relationSearch($id),
        ]);
    }
   
    /**
     * 创建 一个新的 Video 模块
     * 如果创建成功，返回json数据
     * @param string $node_id
     * @return mixed|json [
     *     model => 模型, allRef => 所有可被引用数据, allTeacher => 所有老师, 
     *     videoFiles => 已存在的视频文件, attFiles => 已存在的附件文件
     * ]
     */
    public function actionCreate($node_id)
    {
        $model = new Video(['node_id' => $node_id, 
            'customer_id' => Yii::$app->user->identity->customer_id, 
            'created_by' => Yii::$app->user->id
        ]);
        $model->loadDefaultValues();
        
        if(!ActionUtils::getInstance()->getIsHasEditNodePermission($model->courseNode->course_id)){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->CreateVideo($model, Yii::$app->request->post());
            $data = [
                'id' => $model->id, 'node_id' => $model->node_id, 'name' => $model->name,
                'duration' => DateUtil::intToTime($model->source_duration),
            ];
            return [
                'code'=> $result ? 200 : 404,
                'data' => $result ? $data : [],
                'message' => ''
            ];
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
                'allRef' => $this->getVideoByReference(),
                'allTeacher' => Teacher::getTeacherByLevel(Yii::$app->user->id, 0, false),
                'videoFiles' => Video::getUploadfileByVideo(),
                'allTags' => ArrayHelper::map(Tags::find()->all(), 'id', 'name'),
            ]);
        }
    }
    
    /**
     * 更新 现有的 Video 模型。
     * 如果更新成功，返回json数据
     * @param string $id
     * @return mixed|json [
     *     model => 模型, allRef => 所有可被引用数据, allTeacher => 所有老师, 
     *     videoFiles => 已存在的视频文件, attFiles => 已存在的附件文件
     * ]
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if(!ActionUtils::getInstance()->getIsHasEditNodePermission($model->courseNode->course_id)){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->UpdateVideo($model, Yii::$app->request->post());
            $data = [
                'id' => $model->id, 'name' => $model->name,
                'duration' => DateUtil::intToTime($model->source_duration),
            ];
            return [
                'code'=> $result ? 200 : 404,
                'data' => $result ? $data : [],
                'message' => ''
            ];
        } else {
            return $this->renderAjax('update', [
                'model' => $model,
                'allRef' => $this->getVideoByReference(),
                'allTeacher' => Teacher::getTeacherByLevel($model->created_by, 0, false),
                'videoFiles' => Video::getUploadfileByVideo($model->source_id),
                'allTags' => ArrayHelper::map(Tags::find()->all(), 'id', 'name'),
                'tagsSelected' => array_keys(TagRef::getTagsByObjectId($id, 2)),
            ]);
        }
    }
    
    /**
     * 删除 现有的 Video 模型。
     * 如果删除成功，返回json数据
     * @param string $id
     * @return mixed [model => 模型]
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if(!ActionUtils::getInstance()->getIsHasEditNodePermission($model->courseNode->course_id)){
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->DeleteVideo($model);
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
        } else {
            return $this->renderAjax('delete',[
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 引用 已有的 Video 模型。
     * 如果是post传值，返回成功的json数据，否则返回失败
     * @return json
     */
    public function actionReference()
    {
        $searchModel = new VideoFavoriteSearch();
        $result = $searchModel->referenceSearch(Yii::$app->request->queryParams);
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
        ]);
        
        return $this->renderAjax('reference', [
            'searchModel' => $searchModel,
            'filters' => $result['filter'],
            'totalCount' => $result['total'],
            'dataProvider' => $dataProvider,
        ]);
        
        
        
        $result = $this->findVideoByCiteInfo($id);
        Yii::$app->getResponse()->format = 'json';
        
        if (Yii::$app->request->isPost) {
            return [
                'code' => 200,
                'data' => $result,
                'message' => '引用成功'
            ];
        }else{
            return [
                'code' => 404,
                'data' => '',
                'message' => '引用失败'
            ];
        }
    }
    
    /**
     * 基于其主键值找到 Video 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return Video 加载模型
     * @throws NotFoundHttpException 如果找不到模型
     */
    protected function findModel($id)
    {
        if (($model = Video::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
     * 查询引用视频的关联信息
     * @param string $id
     * @return array
     */
    protected function  findVideoByCiteInfo($id)
    {
        //查询引用的视频信息
        $video = (new Query())->select(['id', 'teacher_id', 'source_id', 'name', 'des'])
            ->from(Video::tableName())->where(['id' => $id])->one();
        //查询视频文件
        $source_id = ArrayHelper::getValue($video, 'source_id');
        $source = (new Query())->select(['id', 'name', 'size'])
            ->from(Uploadfile::tableName())->where(['id' => $source_id])->one();
        
        //返回数据
        return [
            'video' => $video,
            'source' => [$source],
        ];
    }
    
    /**
     * 获取属于自己的课程
     * @return array
     */
    protected function getCourseByCreatedBy()
    {
        //根据已存在的视频查询课程id
        $courseIds = Video::find()->select(['CourseNode.course_id'])
            ->from(['Video' => Video::tableName()])
            ->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)')
            ->where(['Video.created_by' => Yii::$app->user->id, 'Video.is_del' => 0]);
        
        //查询课程
        $courses = Course::find()->where(['created_by' => Yii::$app->user->id])
            ->andWhere(['id' => $courseIds])->all();
        
        return ArrayHelper::map($courses, 'id', 'name');
    }

    /**
     * 获取可以引用的视频
     * @return array
     */
    protected function getVideoByReference()
    {
        $refs = [];
        $vidos = Video::getVideoNode(['created_by' => Yii::$app->user->id]);
        if($vidos != null){
            foreach ($vidos as $model) {
                $refs[] = [
                    'id' => $model->id,
                    'name' => $model->courseNode->course->name . ' / ' . $model->courseNode->name . 
                            ' / ' . $model->name .'（'. $model->teacher->name .'）'
                ];
            }
        }
        
        return ArrayHelper::map($refs, 'id', 'name');
    }
}
