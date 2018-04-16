<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Course;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\modules\webuploader\models\Uploadfile;
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
        
        return $this->render('index', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
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
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->CreateVideo($model, Yii::$app->request->post());
            $data = [
                'id' => $model->id, 'node_id' => $model->node_id, 
                'name' => $model->name, 'is_ref' => $model->is_ref ? 'inline-block' : 'none', 
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
                'allTeacher' => Teacher::getTeacherByLevel(Yii::$app->user->identity->customer_id),
                'videoFiles' => Video::getUploadfileByVideo(),
                'attFiles' => Video::getUploadfileByAttachment(),
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
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->UpdateVideo($model, Yii::$app->request->post());
            $data = [
                'id' => $model->id, 'name' => $model->name,
                'is_ref' => $model->is_ref ? 'inline-block' : 'none', 
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
                'allTeacher' => Teacher::getTeacherByLevel($model->customer_id),
                'videoFiles' => Video::getUploadfileByVideo($model->source_id),
                'attFiles' => Video::getUploadfileByAttachment($model->id),
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
     * 引用 现有的 Video 模型。
     * 如果是post传值，返回成功的json数据，否则返回失败
     * @param string $id
     * @return json
     */
    public function actionReference($id)
    {
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
        //查询附件文件
        $video_id = ArrayHelper::getValue($video, 'id');
        $atts = (new Query())->select(['Uploadfile.id', 'Uploadfile.name', 'Uploadfile.size'])
            ->from(['Attachment' => VideoAttachment::tableName()])
            ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Attachment.file_id')
            ->where(['Attachment.video_id' => $video_id])->all();

        //返回数据
        return [
            'video' => $video,
            'source' => [$source],
            'atts' => $atts,
        ];
    }
    
    /**
     * 获取属于自己的课程
     * @return array
     */
    protected function getCourseByCreatedBy()
    {
        $courses = Course::find()
            ->where(['created_by' => Yii::$app->user->id])
            ->all();
        
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
