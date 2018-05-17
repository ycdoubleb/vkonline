<?php

namespace frontend\modules\course\controllers;

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseAttribute;
use common\models\vk\CourseFavorite;
use common\models\vk\CourseMessage;
use common\models\vk\CourseNode;
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
     * 呈现模块的索引视图。
     * @return mixed [allCategory => 所有分类, filters => 过滤参数,
     *    pagers => 分页, dataProvider => 课程数据
     * ]
     */
    public function actionIndex()
    {
        $searchModel = new CourseSearch();
        $result = $searchModel->courseSearch(array_merge(Yii::$app->request->queryParams, ['limit' => 8]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
        
        unset($result['filter']['limit']);
        return $this->render('index', [
            'allCategory' => Category::getCatsByLevel(1, true),
            'filters' => $result['filter'],
            'pagers' => $result['pager'],
            'dataProvider' => $dataProvider,
        ]);
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
        $data = [];
        try{
            $result = CourseListSearch::search(Yii::$app->request->queryParams,2);
        } catch (\Exception $ex) {
            $mes = $ex->getMessage();
        }
        return [
            'code' => $code,
            'mes' => '',
            'data' => [
                'page' => ArrayHelper::getValue(Yii::$app->request->queryParams, 'page' ,1),
                'courses' => $result['courses'],
            ],
        ];
    }
    
    /**
     * 搜索结果 保存搜索的关键字
     * 如果保存成功，浏览器将被重定向到“index”页面。
     * @return mixed
     */
    public function actionResult()
    {
        $params = Yii::$app->request->queryParams;
        $keyword = ArrayHelper::getValue($params, 'keyword');
        
        $logModel = new SearchLog();
        
        $logModel->keyword = $keyword;
        
        if($logModel->save()){
            return $this->redirect(array_merge(['index'], $params));
        } else {
            Yii::$app->getSession()->setFlash('error','操作失败');
        }
    }
    
    /**
     * 显示一个单一的 Course 模型.
     * @param string $id
     * @return mixed  [
     *  model => 模型, favorite => 关注的课程模型,
     *  praise => 点赞的课程模型, videoNum => 视频数
     *  courseNodes => 课程节点, msgDataProvider => 留言数据
     * ]
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModel = new CourseMessageSearch();
        
        return $this->render('view', [
            'model' => $model,
            'favorite' => $this->findFavoriteModel($id),
            'praise' => $this->findPraiseModel($id),
            'videoNum' => $this->getVideoNumByCourseNode($id),
            'courseNodes' => $this->findCourseNode($id),
            'msgDataProvider' => $searchModel->search(['course_id' => $id]),
        ]);
    }
    
    /**
     * 点击关注
     * @param string $id    //course_id
     * @return json
     */
    public function actionFavorite($id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = $this->findModel($id);
        $favorite = $this->findFavoriteModel($id);
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if(!$favorite->isNewRecord){
                $favorite->is_del = 1;
                if($favorite->update()){
                    $model->favorite_count = $model->favorite_count - 1;
                    $model->save(true, ['favorite_count']);
                }
            }else{
                if($favorite->save()){
                    $model->favorite_count = $model->favorite_count + 1;
                    $model->save(true, ['favorite_count']);
                }
            }
            
            $trans->commit();  //提交事务
            return [
                'code' => 200,
                'data' => $model->favorite_count,
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code' => 404,
                'data' => $model->favorite_count,
                'message' => '操作失败！',
            ];
        }
    }
    
    /**
     * 点击点赞
     * @param string $id    //course_id
     * @return json
     */
    public function actionPraise($id)
    {
        Yii::$app->getResponse()->format = 'json';
        $model = $this->findModel($id);
        $praise = $this->findPraiseModel($id);
        
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if(!$praise->isNewRecord){
                if($praise->delete()){
                    $model->zan_count = $model->zan_count - 1;
                    $model->save(true, ['zan_count']);
                }
            }else{
                if($praise->save()){
                    $model->zan_count = $model->zan_count + 1;
                    $model->save(true, ['zan_count']);
                }
            }
            
            $trans->commit();  //提交事务
            return [
                'code' => 200,
                'data' => $model->zan_count,
                'message' => '操作成功！'
            ];
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return [
                'code' => 404,
                'data' => $model->zan_count,
                'message' => '操作失败！',
            ];
        }
    }

    /**
     * 留言列表视图
     * @return mixed [dataProvider => 留言数据]
     */
    public function actionMsgIndex()
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
     * 基于其course_id 和 user_id找到 CourseFavorite 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $course_id
     * @return model CourseFavorite
     */
    protected function findFavoriteModel($course_id)
    {
        $model = CourseFavorite::findOne(['course_id' => $course_id, 'user_id' => Yii::$app->user->id, 'is_del' => 0]);
        if ($model !== null) {
            return $model;
        } else {
            return new CourseFavorite(['course_id' => $course_id, 'user_id' => Yii::$app->user->id]);
        }
    }
    
    /**
     * 基于其type、course_id 和 user_id找到 PraiseLog 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $course_id
     * @return model PraiseLog
     */
    protected function findPraiseModel($course_id)
    {
        $model = PraiseLog::findOne(['type' => 1, 'course_id' => $course_id, 'user_id' => Yii::$app->user->id]);
        if ($model !== null) {
            return $model;
        } else {
            return new PraiseLog(['type' => 1, 'course_id' => $course_id, 'user_id' => Yii::$app->user->id]);
        }
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
