<?php

namespace frontend\modules\build_course\controllers;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseActLog;
use common\models\vk\CourseNode;
use common\models\vk\CourseUser;
use common\models\vk\RecentContacts;
use common\models\vk\searchs\CourseActLogSearch;
use common\models\vk\searchs\CourseNodeSearch;
use common\models\vk\searchs\CourseSearch;
use common\models\vk\searchs\CourseUserSearch;
use common\models\vk\searchs\TeacherSearch;
use common\models\vk\searchs\VideoSearch;
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
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;



/**
 * Default controller for the `build_course` module
 */
class DefaultController extends Controller
{
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
            'access' => [
                'class' => AccessControl::className(),
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
        $searchModel = new CourseSearch();
        $result = $searchModel->search(array_merge(\Yii::$app->request->queryParams, [
            'created_by' => \Yii::$app->user->id, 'limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
        
        return $this->render('my_course', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Displays a single Course model.
     * @param integer $id
     * @return mixed
     */
    public function actionViewCourse($id)
    {
        $model = $this->findCourseModel($id);
        $searchUserModel = new CourseUserSearch();
        $searchNodeModel = new CourseNodeSearch();
        
        return $this->render('view_course', [
            'model' => $model,
            'courseUsers' => $searchUserModel->search(['course_id' => $model->id]),
            'courseNodes' => $searchNodeModel->search(['course_id' => $model->id]),
        ]);
    }
   
    /**
     * AddCourse a new Course model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddCourse()
    {
        $model = new Course(['customer_id' => Yii::$app->user->identity->customer_id, 'created_by' => Yii::$app->user->id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->CreateCourse($model);
            return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->render('add_course', [
                'model' => $model,
                'allCategory' => Category::getCatsByLevel(1, true),
                'allTeacher' => Teacher::getTeacherByLevel(),
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
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->UpdateCourse($model);
            return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->render('edit_course', [
                'model' => $model,
                'allCategory' => Category::getCatsByLevel(1, true),
                'allTeacher' => Teacher::getTeacherByLevel(),
            ]);
        }
    }
    
    /**
     * CloseCourse a new Course model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionCloseCourse($id)
    {
        $model = $this->findCourseModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->CloseCourse($model);
            return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->renderAjax('close_course', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * PubCourse a new Course model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionPubCourse($id)
    {
        $model = $this->findCourseModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->PublishCourse($model);
            return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->renderAjax('pub_course', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * Lists all CourseUser models.
     * @return mixed
     */
    public function actionHelpman($course_id)
    {
        $searchModel = new CourseUserSearch();
        $dataProvider = $searchModel->search(['course_id' => $course_id]);
        
        return $this->renderAjax('help_man', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * AddHelpMan a new CourseUser model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddHelpman($course_id)
    {
        $model = new CourseUser(['course_id' => $course_id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->CreateHelpman($model, Yii::$app->request->post());
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
        } else {
            return $this->renderAjax('add_help_man', [
                'model' => $model,
                'helpMans' => $this->getHelpManList($course_id, $model->course->created_by),
                'userRecentContacts' => $this->getUserRecentContacts(),
            ]);
        }
    }

    /**
     * EditHelpman an existing CourseUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionEditHelpman($id)
    {
        $model = CourseUser::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->UpdateHelpman($model);
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('edit_help_man', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * DelHelpman an existing CourseUser model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelHelpman($id)
    {
        $model = CourseUser::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->DeleteHelpman($model);
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('del_help_man',[
                'model' => $model
            ]);
        }
    }
    
    /**
     * Lists all CourseNode.
     * @return mixed
     */
    public function actionCourseFrame($course_id)
    {
        $searchModel = new CourseNodeSearch();
        $dataProvider = $searchModel->search(['course_id' => $course_id]);
        
        return $this->renderAjax('help_man', [
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * AddCouframe a new CourseNode model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddCouframe($course_id)
    {        
        $model = new CourseNode(['course_id' => $course_id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->CreateCouFrame($model);
            return [
                'code'=> $result ? 200 : 404,
                'data' => $result ? ['id' => $model->id, 'name' => $model->name] : [],
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $course_id]);
        } else {
            return $this->renderAjax('add_couframe', [
                'model' => $model,
                'course_id'=>$model->course_id,
            ]);
        }
    }
    
    /**
     * EditCoufram an existing CourseNode model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionEditCouframe($id)
    {
        $model = CourseNode::findOne($id);
                
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->UpdateCouFrame($model);
            return [
                'code'=> $result ? 200 : 404,
                'data'=> $result ? ['id' => $model->id, 'name' => $model->name,] : [],
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('edit_couframe', [
                'model' => $model,
            ]);
        }
    }

    /**
     * DelCouframe an existing CourseNode model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelCouframe($id)
    {
        $model = CourseNode::findOne($id);
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->DeleteCouFrame($model,Yii::t('app', 'Phase'),$model->course_id);
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('del_couframe',[
                'model' => $model,
            ]);
        }
    }
    
    /**
     * MoveCouframe an existing CourseNode model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionMoveCouframe()
    {
        
        if(Yii::$app->request->isPost){
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->MoveCouframe(Yii::$app->request->post());
            
            return [
                'code' => $result ? 200 : 404,
                'message' => ''
            ];
        }else{
            return [
                'code' => 404,
                'message' => ''
            ];
        }
    }
    
    /**
     * Lists all CourseActLog models.
     * @return mixed
     */
    public function actionActlog($course_id)
    {
        $searchModel = new CourseActLogSearch();
        $results = $searchModel->search(Yii::$app->request->queryParams);
        $logs = $this->getCourseActLogs($course_id);
        if(Yii::$app->request->isPost){
            $filter = Yii::$app->request->post();
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> $results ? 200 : 404,
                'data' =>$filter,
                'url' => Url::to(array_merge(['actlog'], $filter)),
                'message' => ''
            ];
        }else{
            return $this->renderAjax('course_actlog', [
                'searchModel' => $searchModel,
                'dataProvider' => $results['dataProvider'],
                'filter' => $results['filter'],
                'action' => $logs['action'],
                'title' => $logs['title'],
                'createdBy' => $logs['created_by'],
            ]);
        }
    }
    
    /**
     * Displays a single CourseActLog model.
     * @param string $id
     * @return mixed
     */
    public function actionViewActlog($id)
    {
        return $this->renderAjax('view_actlog', [
            'model' => CourseActLog::findOne($id),
        ]);
    }
    
    /**
     * Renders the my_video view for the module
     * @return string
     */
    public function actionMyVideo()
    {
        $searchModel = new VideoSearch();
        $result = $searchModel->search(array_merge(\Yii::$app->request->queryParams, [
            'created_by' => \Yii::$app->user->id, 'limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['video']),
        ]);
        
        return $this->render('my_video', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'courseMap' => $result['data']['course'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Displays a single Video model.
     * @param integer $id
     * @return mixed
     */
    public function actionViewVideo($id)
    {
        $model = $this->findVideoModel($id);
        $searchModel = new VideoSearch();
        
        return $this->render('view_video', [
            'model' => $model,
            'dataProvider' => $searchModel->relationSearch($id),
        ]);
    }
   
    /**
     * AddVideo a new Video model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionAddVideo($node_id)
    {
        $model = new Video(['node_id' => $node_id, 'customer_id' => Yii::$app->user->identity->customer_id, 'created_by' => Yii::$app->user->id]);
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
            //return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->renderAjax('add_video', [
                'model' => $model,
                'allRef' => $this->getVideoByReference(),
                'allTeacher' => Teacher::getTeacherByLevel(),
                'videoFile' => Video::getUploadfileByVideo(),
                'attFiles' => Video::getUploadfileByAttachment(),
            ]);
        }
    }
    
    /**
     * EditVideo a new Video model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionEditVideo($id)
    {
        $model = $this->findVideoModel($id);
        
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
            //return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->renderAjax('edit_video', [
                'model' => $model,
                'allRef' => $this->getVideoByReference(),
                'allTeacher' => Teacher::getTeacherByLevel(),
                'videoFile' => Video::getUploadfileByVideo($model->source_id),
                'attFiles' => Video::getUploadfileByAttachment($model->id),
            ]);
        }
    }
    
    /**
     * DelVideo an existing Video model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelVideo($id)
    {
        $model = $this->findVideoModel($id);
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->DeleteVideo($model);
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $model->course_id]);
        } else {
            return $this->renderAjax('del_video',[
                'model' => $model,
            ]);
        }
    }
    
    /**
     * CiteVideo an existing Video model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionCiteVideo($id)
    {
        $result = $this->findVideoByCiteInfo($id);
        Yii::$app->getResponse()->format = 'json';
        
        if (\Yii::$app->request->isPost) {
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
     * Renders the my_teacher view for the module
     * @return string
     */
    public function actionMyTeacher()
    {
        $searchModel = new TeacherSearch();
        $result = $searchModel->search(array_merge(\Yii::$app->request->queryParams, [
            'created_by' => \Yii::$app->user->id, 'limit' => 12]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['teacher']),
        ]);
        
        return $this->render('my_teacher', [
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * Displays a single Teacher model.
     * @param integer $id
     * @return mixed
     */
    public function actionViewTeacher($id)
    {
        $model = $this->findTeacherModel($id);
        $searchModel = new TeacherSearch();
        
        return $this->render('view_teacher', [
            'model' => $model,
            'dataProvider' => $searchModel->relationSearch($id),
        ]);
    }
    
    /**
     * AddTeacher a new Teacher model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddTeacher()
    {
        $model = new Teacher(['customer_id' => Yii::$app->user->identity->customer_id,'created_by' => Yii::$app->user->id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view-teacher', 'id' => $model->id]);
        } else {
            return $this->render('add_teacher', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * EditTeacher a new Teacher model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionEditTeacher($id)
    {
        $model = $this->findTeacherModel($id);
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view-teacher', 'id' => $model->id]);
        } else {
            return $this->render('edit_teacher', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * Finds the Course model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Course the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findCourseModel($id)
    {
        if (($model = Course::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findVideoModel($id)
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
     * Finds the Teacher model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Teacher the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findTeacherModel($id)
    {
        if (($model = Teacher::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
     * 获取用户关联的最近联系人
     * @return array
     */
    protected function getUserRecentContacts()
    {
        $query = (new Query())->select(['User.id','User.nickname','User.avatar'])
            ->from(['RecentContacts'=>RecentContacts::tableName()]);
        
        $query->leftJoin(['User'=> User::tableName()],'User.id = RecentContacts.contacts_id');
        $query->where(['user_id'=> Yii::$app->user->id]);
        $query->orderBy(['RecentContacts.updated_at' => SORT_DESC]);
        
        return $query->limit(8)->all();
    }


    /**
     * 获取所有协助人员
     * @param string $course_id 课程id
     * @param string $user_id   用户id
     * @return array
     */
    protected function getHelpManList($course_id, $user_id)
    {
        //查找已添加的协作人员
        $query = (new Query())->select(['user_id'])->from(CourseUser::tableName());
        $query->where(['course_id' => $course_id]);
        $user_ids = ArrayHelper::getColumn($query->all(), 'user_id');
        //合并创建者和已添加的协作人员
        $userIds = array_merge([$user_id], $user_ids);
        //查找所有可以添加的协作人员
        $user = (new Query())->select(['id', 'nickname'])->from(User::tableName());
        $user->where(['NOT IN', 'id', $userIds])->andWhere(['status' => 10]);
        
        return ArrayHelper::map($user->all(), 'id', 'nickname');
    }
    
    /**
     * 获取该课程下的所有记录
     * @param string $course_id                             
     * @return array
     */
    protected function getCourseActLogs($course_id)
    {
        $query = (new Query())->select(['action','title','created_by', 'User.nickname']);
        $query->from(CourseActLog::tableName());
        $query->leftJoin(['User' => User::tableName()], 'User.id = created_by');
        $query->where(['course_id' => $course_id]);
        
        return [
            'action' => ArrayHelper::map($query->all(), 'action', 'action'),
            'title' => ArrayHelper::map($query->all(), 'title', 'title'),
            'created_by' => ArrayHelper::map($query->all(), 'created_by', 'nickname'),
        ];
    }
    
    /**
     * 获取可以引用的视频
     * @return array
     */
    protected function getVideoByReference()
    {
        $refs = [];
        $vidos = Video::getVideoNode([]);
        foreach ($vidos as $model) {
            $refs[] = [
                'id' => $model->id,
                'name' => $model->courseNode->course->name . ' / ' . $model->courseNode->name . 
                        ' / ' . $model->name .'（'. $model->teacher->name .'）'
            ];
        }
        
        return ArrayHelper::map($refs, 'id', 'name');
    }
}
