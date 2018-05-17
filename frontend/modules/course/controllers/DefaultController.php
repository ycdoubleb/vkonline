<?php

namespace frontend\modules\course\controllers;

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseAttribute;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseMessage;
use common\models\vk\CourseNode;
use common\models\vk\CourseProgress;
use common\models\vk\Customer;
use common\models\vk\PraiseLog;
use common\models\vk\SearchLog;
use common\models\vk\searchs\CourseListSearch;
use common\models\vk\searchs\CourseMessageSearch;
use common\models\vk\searchs\CourseSearch;
use common\models\vk\Video;
use frontend\modules\course\utils\ActionUtils;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `course` module
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
     * 搜索课程面
     */
    public function actionList(){
        //当前已选择的分类
        $category_id = ArrayHelper::getValue(Yii::$app->request->queryParams, 'cat_id');
        $customer_id = ArrayHelper::getValue(Yii::$app->request->queryParams, 'customer_id' , "0");
        //获取当前分类的父级分类[顶级分类,子级分类,……]
        if ($category_id === "0") {
            //没有选择任何单位情况下，只需要显示顶级分类
            $categoryLevels = [Category::getCatsByLevel(1, true)];
        } else {
            $categoryLevels = Category::getCustomerSameLevelCats($category_id, $customer_id, true);
        }
        //当前分类所有属性
        $attrs = CourseAttribute::find()->where([
                    'category_id' => $category_id,
                    'type' => 1,
                    'input_type' => 2,
                    'index_type' => 1,
                    'is_del' => 0,
                ])->orderBy('sort_order')->all();
        
        $result = CourseListSearch::search(Yii::$app->request->queryParams,1);
        
        return $this->render('list',[
            'categoryLevels' => $categoryLevels,     //所有分类，包括顶级分类，子级分类
            'customers' => ArrayHelper::map(Customer::find()->select(['id','name'])->asArray()->all(), 'id', 'name'),   //所有客户
            'attrs' => $attrs,  //属性
            
            'max_count' => $result['max_count'],    //最大数量
            'courses' => [],        //课程
        ]);
    }
    
    /**
     * 滚动换页
     */
    public function actionSearchList(){
        Yii::$app->response->format = 'json';
        $code = 0;
        $mes = '';
        try{
            $result = CourseListSearch::search(Yii::$app->request->queryParams,2);
        } catch (\Exception $ex) {
            $mes = $ex->getMessage();
        }
        return [
            'code' => $code,
            'mes' => $mes,
            'data' => [
                'page' => ArrayHelper::getValue(Yii::$app->request->queryParams, 'page' ,1),
                'courses' => $result['courses'],
            ],
        ];
    }

    /**
     * 查看课程详情
     * @param string $id
     */
    public function actionView($id)
    {
        $detail = $this->findViewDetail($id);
        
        return $this->render('view', [
            'model' => $detail['course'],
            'study_progress' => $detail['study_progress'],
        ]);
    }

    /**
     * 获取课程目录列表
     * @param string $course_id 课程ID
     */
    public function actionGetNode($course_id){
        return $this->renderAjax('__nodes', [
            
        ]);
    }
    
    /**
     * 获取评价
     * @param string $id 课程ID
     */
    public function actionGetcomment()
    {
        $searchModel = new CourseMessageSearch();
        
        return $this->renderAjax('message', [
            'dataProvider' => $searchModel->search(Yii::$app->request->queryParams)
        ]);
    }
    
    /**
     * 添加一条新的留言
     * 如果创建成功，则返回json数据，否者则返回上一步
     * @param string $id    //course_id
     * @return json|goBack
     */
    public function actionAddMsg($id)
    {
        $model = new CourseMessage(['course_id' => $id, 'type' => CourseMessage::COURSE_TYPE]);
        $model->loadDefaultValues();
        
        if(Yii::$app->request->isPost){
            Yii::$app->getResponse()->format = 'json';
            $result = ActionUtils::getInstance()->addCourseMsg($model, Yii::$app->request->post());
            
            return [
                'code'=> $result ? 200 : 404,
                'message' => ''
            ];
        } else {
            return $this->goBack(['course/default/view', 'id' => $model->course_id]);
        }
    }
    
    /**
     * 基于其主键值找到 Course 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return Course 
     * @throws NotFoundHttpException 
     */
    protected function findModel($id)
    {
        if (($model = Course::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
    }
    
    /**
     * 查找课程详细信息
     * @param string $id 课程ID
     * @return array [Course,StudyProgress];
     */
    protected function findViewDetail($id){
        
        $user_id = Yii::$app->user->id;
        /* @var $query Query */
        $course_query = (new Query())
                ->select([
                    'Course.id','Course.name','Course.category_id','Course.cover_img',
                    'Course.customer_id','Customer.name as customer_name',
                    'Course.avg_star','Course.learning_count','Course.content_time','Course.content',
                    '(Favorite.is_del = 0) as is_favorite'
                ])
                ->from(['Course' => Course::tableName()])
                ->leftJoin(['Customer' => Customer::tableName()],"Course.customer_id = Customer.id")
                ->leftJoin(['Favorite' => CourseFavorite::tableName()],"Course.id = Favorite.course_id AND Favorite.user_id = '$user_id'")
                ->where(['Course.id' => $id]);
        
        /* 查找视频环节 */
        $video_num_query = (new Query())
                ->select(['Video.id'])
                ->from(['Video' => Video::tableName()])
                ->leftJoin(['Node' => CourseNode::tableName()], 'Node.id = Video.node_id')
                ->where([
                    'Node.course_id' => $id,
                    'Node.is_del' => 0,
                    'Video.is_del' => 0,
                ]);
        
        /* 查找学习进度 */
        $study_progress_query = (new Query())
                ->select(['StudyProgress.*','Video.name as video_name'])
                ->from(['StudyProgress' => CourseProgress::tableName()])
                ->leftJoin(['Video' => Video::tableName()], 'Video.id = StudyProgress.last_video')
                ->where([
                    'StudyProgress.course_id' => $id,
                    'StudyProgress.user_id' => $user_id,
                ]);
        
        return [
            'course' => array_merge($course_query->one(),['node_count' => $video_num_query->count()]),
            'study_progress' => $study_progress_query->one(),
        ];
    }
    
    /**
     * 查询所有课程节点
     * @param string $course_id
     * @return model CourseNode 
     */
    protected function findCourseNode($course_id)
    {
        $qurey = CourseNode::find();
            
        $qurey->where(['course_id' => $course_id, 'is_del' => 0]);
        
        $qurey->orderBy(['sort_order' => SORT_ASC]);
        
        return $qurey->all();
    }
    
    /**
     * 获取环节数
     * @param string $course_id
     * @return array 
     */
    protected function getVideoNumByCourseNode($course_id)
    {
         $query = Video::find()->select(['COUNT(Video.id) AS node_num'])
            ->from(['Video' => Video::tableName()]);
        
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        
        $query->where(['Video.is_del' => 0, 'CourseNode.course_id' => $course_id]);
        
        $query->groupBy('CourseNode.course_id');
        
        return $query->asArray()->one();
    }
}
