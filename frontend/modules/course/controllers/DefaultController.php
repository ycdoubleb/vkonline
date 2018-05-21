<?php

namespace frontend\modules\course\controllers;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\CommentPraise;
use common\models\vk\Course;
use common\models\vk\CourseAttribute;
use common\models\vk\CourseComment;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseMessage;
use common\models\vk\CourseNode;
use common\models\vk\CourseProgress;
use common\models\vk\Customer;
use common\models\vk\searchs\CourseListSearch;
use common\models\vk\Video;
use common\models\vk\VideoProgress;
use frontend\modules\course\utils\ActionUtils;
use Yii;
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
        return $this->renderAjax('__nodes', $this->findNodeDetail($course_id));
    }
    
    /**
     * 获取评价视图
     * @param string $id 课程ID
     */
    public function actionGetComment($course_id)
    {
        /* 查询我的评论 */
        $model = (new Query())
                ->select([
                    'Comment.id comment_id','Comment.content','Comment.star','Comment.created_at','Comment.zan_count',
                    'User.id as user_id','User.nickname as user_nickname','User.avatar as user_avatar',
                    '(CommentPraise.result=1) as is_praise'
                    ])
                ->from(['Comment' => CourseComment::tableName()])
                ->leftJoin(['User' => User::tableName()], 'Comment.user_id = User.id')
                ->leftJoin(['CommentPraise' => CommentPraise::tableName()], 'CommentPraise.comment_id = Comment.id')
                ->where([
                    'Comment.course_id' => $course_id,
                    'Comment.user_id' => Yii::$app->user->id,
                ])->one();
        /* 数量 */
        $count = CourseComment::find()->where(['course_id' => $course_id])->count();
        
        return $this->renderAjax('__comment', [
                'course_id' => $course_id,
                'myComment' => $model,
                'max_count' => $count,
                'page' => 1,
        ]);
    }
    
    /**
     * 获取作业/任务视图
     */
    public function actionGetTask(){
        return $this->renderAjax('__task', [
        ]);
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
     * 获取节点详细，包括节点数据和视频数据
     * @param string $course_id     课程ID
     */
    protected function findNodeDetail($course_id){
        $user_id = Yii::$app->user->id;
        //查询所有环节的学习情况
        $study_progress = (new Query())
                ->select([
                    'Node.id as node_id','Node.name as node_name','Node.sort_order as node_sort_order',
                    'Video.id as video_id','Video.name video_name','Video.is_ref','Video.source_duration as duration','Video.sort_order as video_sort_order',
                    'Progress.is_finish','Progress.finish_time','Progress.last_time'])
                ->from(['Node' => CourseNode::tableName()])
                ->leftJoin(['Video' => Video::tableName()], 'Node.id = Video.node_id')
                ->leftJoin(['Progress' => VideoProgress::tableName()], 'Progress.course_id=:course_id AND Progress.user_id=:user_id AND Progress.video_id=Video.id',
                        ['course_id' => $course_id,'user_id'=>$user_id])
                ->where([
                    'Node.course_id' => $course_id,
                    'Node.is_del' => 0,
                ])
                //先排节点再排视频
                ->orderBy(['Node.sort_order' => SORT_ASC,'Video.sort_order' => SORT_ASC])
                ->all();
        
        $nodes = [];            //节点
        $video_count = 0;       //视频总数
        $finish_count = 0;      //已完成视频数
        foreach($study_progress as $progress){
            //先建节点数据
            if(!isset($nodes[$progress['node_id']])){
                $nodes[$progress['node_id']] = [
                    'node_id' => $progress['node_id'], 
                    'node_name' => $progress['node_name'],
                    'sort_order' => $progress['node_sort_order'],
                    'videos' => [],
                ];
            }
            //添加视频到节点
            if($progress['video_id']!=null){
                $video_count ++;
                if($progress['is_finish'] == 1){
                    $finish_count ++;
                }
                $nodes[$progress['node_id']]['videos'] []= [
                    'node_id' => $progress['node_id'],
                    'video_id' => $progress['video_id'],
                    'video_name' => $progress['video_name'],
                    'is_ref' => $progress['is_ref'],
                    'duration' => $progress['duration'],
                    'sort_order' => $progress['video_sort_order'],
                    'is_finish' => $progress['is_finish'],
                    'finish_time' => $progress['finish_time'],
                    'last_time' => $progress['last_time'],
                ];
            }
        }
        
        //var_dump($study_progress,$video_count,$finish_count);exit;
        return [
            'video_count' => $video_count,
            'finish_count' => $finish_count,
            'nodes' => $nodes,
        ];
    }
}
