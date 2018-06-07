<?php

namespace frontend\modules\res_service\controllers;

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseAttr;
use common\models\vk\CourseAttribute;
use common\models\vk\searchs\ResServerCourseSearch;
use common\models\vk\searchs\ResServerVideoSearch;
use common\models\vk\Video;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;


/**
 * Default controller for the `res_service` module
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
     * 呈现品牌下的数据统计概况
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
            'model' => new Course(),
        ]);
    }
    
    /**
     * 呈现自己品牌下的课程数据
     * @return string
     */
    public function actionCourseIndex()
    {
        $searchModel = new ResServerCourseSearch();
        $dataProvider = new ArrayDataProvider([
            'allModels' => ResServerCourseSearch::findAll(['customer_id' => Yii::$app->user->identity->customer_id]),
        ]);
        
        return $this->render('course_index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            
            'customerMap' => [],
            'orderGoodsMap' => [],
            'teacherMap' => [],
            'statusMap' => [],
            'createdByMap' => [],
        ]);
    }
    
    /**
     * 显示一个单一的 Course 模型。
     * @param string $id
     * @return mixed [
     *      model => 模型
     * ]
     */
    public function actionCourseView($id)
    {
        $model = Course::findOne($id);
        
        return $this->render('course_view', [
            'model' => $model,
            'courseAttrs' => $this->getCourseAttrByCourseId($model->id),
            'path' => $this->getCategoryFullPath($model->category_id),
        ]);
    }
    
    /**
     * 呈现自己品牌下的视频数据
     * @return string
     */
    public function actionVideoIndex()
    {
        $searchModel = new ResServerVideoSearch();
        $dataProvider = new ArrayDataProvider([
            'allModels' => ResServerVideoSearch::findAll([
                'customer_id' => Yii::$app->user->identity->customer_id,
                'is_del' => 0
            ]),
        ]);
        
        return $this->render('video_index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            
            'customerMap' => [],
            'orderGoodsMap' => [],
            'teacherMap' => [],
            'statusMap' => [],
            'createdByMap' => [],
        ]);
    }
    
    /**
     * 显示一个单一的 Video 模型。
     * @param string $id
     * @return mixed [
     *      model => 模型
     * ]
     */
    public function actionVideoView($id)
    {
        $model = Video::findOne($id);
        
        return $this->render('video_view', [
            'model' => $model,
            'paths' => $model->getUploadfileByPath(),
        ]);
    }
    
    /**
     * 获取已经选择的课程属性
     * @param string $courseId
     * @return array
     */
    protected function getCourseAttrByCourseId($courseId)
    {
        $attributes = (new Query())
            ->select(['CourseAttr.attr_id', 'CourseAttr.value', 'CourseAttribute.name', 'CourseAttr.sort_order'])
            ->from(['CourseAttr' => CourseAttr::tableName()])
            ->leftJoin(['CourseAttribute' => CourseAttribute::tableName()], 'CourseAttribute.id = CourseAttr.attr_id')
            ->where(['course_id' => $courseId, 'CourseAttr.is_del' => 0])->orderBy(['sort_order' => SORT_ASC])->all();
        
        $attrs = [];
        foreach ($attributes as $attr) {
            $val = $attr['attr_id'] . '_' . $attr['sort_order'] . '_' . $attr['value'];
            $attrs[$val] = $attr['name'] . '：' . $attr['value'];
        }
        
        return $attrs;
    }
    
    /**
     * 获取分类全路径
     * @param integer $categoryId
     * @return string
     */
    protected function getCategoryFullPath($categoryId) 
    {
        $parentids = array_values(array_filter(explode(',', Category::getCatById($categoryId)->path)));
        $path = '';
        foreach ($parentids as $index => $id) {
            $path .= ($index == 0 ? '' : ' \ ') . Category::getCatById($id)->name;
        }
        
        return $path;
    }
}
