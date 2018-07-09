<?php

namespace frontend\modules\build_course\controllers;

use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\CustomerWatermark;
use common\models\vk\searchs\VideoSearch;
use common\models\vk\TagRef;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoFile;
use common\modules\webuploader\models\Uploadfile;
use common\utils\DateUtil;
use common\utils\StringUtil;
use frontend\modules\build_course\utils\ActionUtils;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
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
     * 列出所有 VideoSearch 模型。
     * @return string|json
     */
    public function actionIndex()
    {
        $searchModel = new VideoSearch();
        $results = $searchModel->buildCourseSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 6]));
        $videos = array_values($results['data']['video']);    //视频数据
        //重修课程数据里面的元素值
        foreach ($videos as $index => $item) {
            $videos[$index]['img'] = StringUtil::completeFilePath($item['img']);
            $videos[$index]['duration'] = DateUtil::intToTime($item['duration']);
            $videos[$index]['des'] = Html::decode($item['des']);
            $videos[$index]['created_at'] = Date('Y-m-d H:i', $item['created_at']);
            $videos[$index]['level_name'] = Video::$levelMap[$item['level']];
            $videos[$index]['teacher_avatar'] = StringUtil::completeFilePath($item['teacher_avatar']);
            $videos[$index]['tags'] = isset($item['tags']) ? $item['tags'] : 'null';
        }
        
        //如果是ajax请求，返回json
        if(\Yii::$app->request->isAjax){
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
        
        return $this->render('index', [
            'searchModel' => $searchModel,      //搜索模型
            'filters' => $results['filter'],     //查询过滤的属性
            'totalCount' => $results['total'],   //总数量
            'teacherMap' => Teacher::getTeacherByLevel(Yii::$app->user->id, 0, false),  //和自己相关的老师
        ]);
    }
    
    /**
     * 显示一个单一的 Video 模型。
     * @param string $id
     * @return mixed 
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModel = new VideoSearch();
        
        return $this->render('view', [
            'model' => $model,  //video模型
            'dataProvider' => $searchModel->relationSearch($model->id),    //相关课程数据
            'watermarksFiles' => $this->getCustomerWatermark(explode(',', $model->mts_watermark_ids)),    //客户下已启用的水印
        ]);
    }
   
    /**
     * 创建 一个新的 Video 模块
     * 如果创建成功，浏览器将被重定向到“查看”页面。
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Video([
            'customer_id' => Yii::$app->user->identity->customer_id, 
            'created_by' => Yii::$app->user->id
        ]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->createVideo($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,  //模型
                'teacherMap' => Teacher::getTeacherByLevel(Yii::$app->user->id, 0, false),  //和自己相关的老师
                'videoFiles' => json_encode([]),
                'watermarksFiles' => json_encode($this->getCustomerWatermark()),    //客户下已启用的水印,
                'wateSelected' => json_encode([]),
            ]);
        }
    }
    
    /**
     * 更新 现有的 Video 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页面。
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by == Yii::$app->user->id){
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The video does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
    
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->updateVideo($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,  //模型
                'teacherMap' => Teacher::getTeacherByLevel($model->created_by, 0, false),   //和自己相关的老师
                'videoFiles' => json_encode(Uploadfile::getUploadfileByFileId($model->videoFile->file_id)),    //已存在的视频文件
                'watermarksFiles' => json_encode($this->getCustomerWatermark()),    //客户下已启用的水印
                'tagsSelected' => array_values(TagRef::getTagsByObjectId($model->id, 2)),   //已选的标签
                'wateSelected' => json_encode(explode(',', $model->mts_watermark_ids)),    //已选的水印
            ]);
        }
    }
    
    /**
     * 删除 现有的 Video 模型。
     * 如果删除成功，浏览器将被重定向到“查看”或者“列表” 页面。
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by == Yii::$app->user->id){
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The video does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if (Yii::$app->request->isPost) {
            return ActionUtils::getInstance()->deleteVideo($model);
        }
    }
    
    /**
     * 转码 现有的 Video。
     * 如果转码成功，浏览器将被重定向到“查看”页面。
     * @param string $id
     * @return mixed
     */
    public function actionTranscoding($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by == Yii::$app->user->id){
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The video does not exist.'));
            }
            if($model->mts_status == Video::MTS_STATUS_YES){
                throw new NotFoundHttpException(Yii::t('app', 'The video has been transcoding without repeating the transcoding.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if (Yii::$app->request->isPost) {
            ActionUtils::getInstance()->transcodingVideo($model);
            return Yii::$app->controller->redirect(['view', 'id' => $model->id]);
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
     * 获取客户下已启用的所有水印图
     * @param integer|array $cw_id      水印图id
     * @return array
     */
    protected function getCustomerWatermark($cw_id = null)
    {
        //查询客户下的水印图
        $query = (new Query())->select([
            'Watermark.id', 'Watermark.width', 'Watermark.height', 
            'Watermark.dx AS shifting_X', 'Watermark.dy AS shifting_Y', 
            'Watermark.refer_pos', 'Watermark.is_selected',
            'Uploadfile.path'
        ])->from(['Watermark' => CustomerWatermark::tableName()]);
        //关联实体文件
        $query->leftJoin(['Uploadfile' => Uploadfile::tableName()], '(Uploadfile.id = Watermark.file_id AND Uploadfile.is_del = 0)');
        //条件
        $query->where([
            'Watermark.customer_id' => \Yii::$app->user->identity->customer_id,
            'Watermark.is_del' => 0,
        ]);
        $query->andFilterWhere(['Watermark.id' => $cw_id]);
        //查询结果
        $watermarks = $query->all();
        //重置is_selected、path属性值
        foreach ($watermarks as $id => $item) {
            $watermarks[$id]['is_selected'] = $item['is_selected'] ? 'checked' : null;
            $watermarks[$id]['path'] = StringUtil::completeFilePath($item['path']);
        }
        
        return $watermarks;
    }
}
