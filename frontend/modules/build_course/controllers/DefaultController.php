<?php

namespace frontend\modules\build_course\controllers;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\CourseUser;
use common\models\vk\RecentContacts;
use common\models\vk\searchs\CourseUserSearch;
use common\models\vk\Teacher;
use frontend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
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
        return $this->render('my_course');
    }
    
    /**
     * Displays a single Course model.
     * @param integer $id
     * @return mixed
     */
    public function actionViewCourse($id)
    {
        $model = $this->findCourseModel($id);
        $searchModel = new CourseUserSearch();
        
        return $this->render('view_course', [
            'model' => $model,
            'dataProvider' => $searchModel->search(['course_id' => $model->id]),
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
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
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
        
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view-course', 'id' => $model->id]);
        } else {
            return $this->render('edit_course', [
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
     * Lists all CourseFrame.
     * @return mixed
     */
    public function actionCourseFrame($course_id)
    {
        
        $model = $this->findCourseModel($course_id);
        $phaseSearch = new McbsCoursePhaseSearch();
        $blockSearch = new McbsCourseBlockSearch();
        $chapterSearch = new McbsCourseChapterSearch();
        $sectionSearch = new McbsCourseSectionSearch();
        $activitySearch = new McbsCourseActivitySearch();
        
        return $this->renderAjax('course_frame', [
            'course_id' => $model->id,
            'isPermission' => self::IsPermission($model->id, $model->status, false),
            'dataCouphase' => $phaseSearch->search(['course_id'=>$course_id]),
            'dataCoublock' => $blockSearch->search(['course_id'=>$course_id]),
            'dataCouchapter' => $chapterSearch->search(['course_id'=>$course_id]),
            'dataCousection' => $sectionSearch->search(['course_id'=>$course_id]),
            'dataCouactivity' => $activitySearch->search(['course_id'=>$course_id]),
        ]);
    }
    
    /**
     * AddCouframe a new CourseNode model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionAddCouframe($course_id)
    {        
        $model = new CourseNode(['id' => md5(rand(1,10000) . time()), 'course_id' => $course_id]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            Yii::$app->getResponse()->format = 'json';
            $result = McbsAction::getInstance()->CreateCouFrame($model,Yii::t('app', 'Phase'),$course_id);
            return [
                'code'=> $result ? 200 : 404,
                'data' =>$result ? [
                    'frame_name'=>'phase',
                    'sub_frame'=>'block',
                    'id'=>$model->id,
                    'parent_id'=>'',
                    'name'=>$model->name,
                    'value_percent'=>"占课程总分比例：".number_format($model->value_percent,2)."%",
                ] : [],
                'message' => ''
            ];
            //return $this->redirect(['default/view', 'id' => $course_id]);
        } else {
            return $this->renderAjax('create-couframe', [
                'model' => $model,
                'course_id'=>$model->course_id,
                'title' => Yii::t('app', 'Phase')
            ]);
        }
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
     * Displays a single Teacher model.
     * @param integer $id
     * @return mixed
     */
    public function actionViewTeacher($id)
    {
        $model = $this->findTeacherModel($id);
        
        return $this->render('view_teacher', [
            'model' => $model,
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
     * Finds the Course model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
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
     * Finds the Teacher model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
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
    public function getUserRecentContacts()
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
    public function getHelpManList($course_id, $user_id)
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
}
