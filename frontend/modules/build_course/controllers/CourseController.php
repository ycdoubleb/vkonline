<?php

namespace frontend\modules\build_course\controllers;

use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseAttr;
use common\models\vk\CourseAttribute;
use common\models\vk\searchs\CourseActLogSearch;
use common\models\vk\searchs\CourseNodeSearch;
use common\models\vk\searchs\CourseSearch;
use common\models\vk\searchs\CourseUserSearch;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
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
 * Course controller for the `build_course` module
 */
class CourseController extends Controller
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
     * 列出所有 CourseSearch 模型。
     * @return string [
     *    filters => 查询过滤的属性, pagers => 分页, dataProvider => 课程数据
     * ]
     */
    public function actionIndex()
    {
        $searchModel = new CourseSearch();
        $result = $searchModel->buildCourseSearch(array_merge(\Yii::$app->request->queryParams, ['limit' => 6]));
        
        $dataProvider = new ArrayDataProvider([
            'allModels' => array_values($result['data']['course']),
        ]);
                
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            return [
                'code'=> 200,
                'page' => $result['filter']['page'],
                'data' => array_values($result['data']['course']),
                'message' => '请求成功！',
            ];
        }
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'filters' => $result['filter'],
            'totalCount' => $result['total'],
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 显示一个单一的 Course 模型。
     * @param string $id
     * @return mixed [
     *      model => 模型, courseUser => 所有协作人员数据, courseNodes => 所有课程节点数据
     * ]
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchUserModel = new CourseUserSearch();
        $searchNodeModel = new CourseNodeSearch();
        $searchCourseLog = new CourseActLogSearch();
        
        return $this->render('view', [
            'model' => $model,
            'courseLogModel' => $searchCourseLog,
            'courseUsers' => $searchUserModel->search(['course_id' => $model->id]),
            'courseNodes' => $searchNodeModel->search(['course_id' => $model->id]),
            'courseLogs' => $searchCourseLog->search(['course_id' => $model->id]),
            'courseAttrs' => $this->getCourseAttrByCourseId($model->id),
            'logs' => ActionUtils::getInstance()->getCourseActLogs($model->id),
            'path' => $this->getCategoryFullPath($model->category_id),
            'is_hasEditNode' => ActionUtils::getInstance()->getIsHasEditNodePermission($model->id),
        ]);
    }
   
    /**
     * 创建 一个新的 Course 模块
     * 如果创建成功，浏览器将被重定向到“查看”页面。
     * @return mixed [
     *     model => 模型, allCategory => 所有分类, allTeacher => 所有老师, allTags => 所有标签
     * ]
     */
    public function actionCreate()
    {
        $model = new Course([
            'customer_id' => Yii::$app->user->identity->customer_id, 
            'created_by' => Yii::$app->user->id,
            'is_official' => Yii::$app->user->identity->is_official,
        ]);
        $model->loadDefaultValues();
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->createCourse($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'allCategory' => Category::getCatsByLevel(1, true),
                'allTeacher' => Teacher::getTeacherByLevel(Yii::$app->user->id, 0, false),
                'attFiles' => Course::getUploadfileByAttachment(),
                //'allTags' => ArrayHelper::map(Tags::find()->all(), 'id', 'name'),
            ]);
        }
    }
    
    /**
     * 更新 现有的 Course 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed [
     *      model => 模型, allCategory => 所有分类, allTeacher => 所有老师, allTags => 所有标签
     *      tagsSelected => 已选的标签
     * ]
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by !== Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->updateCourse($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
                'allCategory' => Category::getCatsByLevel(1, true),
                'allTeacher' => Teacher::getTeacherByLevel($model->created_by, 0, false),
                'attFiles' => Course::getUploadfileByAttachment($model->id),
                'allAttrs' => $this->getCourseAttributeByCategoryId($model->category_id),
                //'allTags' => ArrayHelper::map(Tags::find()->all(), 'id', 'name'),
                'attrsSelected' => array_keys($this->getCourseAttrByCourseId($model->id)),
                'tagsSelected' => array_values(TagRef::getTagsByObjectId($id, 1)),
            ]);
        }
    }
    
    /**
     * 关闭 现有的 Course 模型。
     * 如果关闭成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed [model => 模型]
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by !== Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->closeCourse($model);
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->renderAjax('close', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 发布 现有的 Course 模型。
     * 如果发布成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed [model => 模型]
     */
    public function actionPublish($id)
    {
        $model = $this->findModel($id);
        
        if($model->created_by !== Yii::$app->user->id){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        
        if (Yii::$app->user->identity->is_official || $model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->publishCourse($model);
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->renderAjax('publish', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 通过分类id查找分类下对应的属性
     * @param integer $cate_id
     * @return json
     */
    public function actionAttrSearch($cate_id)
    {
        $courseAttrs = $this->getCourseAttributeByCategoryId($cate_id);
        Yii::$app->getResponse()->format = 'json';
        
        if(Yii::$app->request->isPost){
            return [
                'code'=> 200,
                'data' => $courseAttrs,
                'message' => '请求成功！',
            ];
        }
        
        return [
            'code'=> 404,
            'data' => [],
            'message' => '请求失败！',
        ];
    }

    /**
     * 基于其主键值找到 Course 模型。
     * 如果找不到模型，就会抛出404个HTTP异常。
     * @param string $id
     * @return Course 加载模型
     * @throws NotFoundHttpException 如果找不到模型
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
     * 获取课程分类下的对应属性
     * @param integer $category_id  
     * @return type
     */
    protected function getCourseAttributeByCategoryId($categoryId)
    {
        $attributes = (new Query())->select(['id', 'name', 'values', 'sort_order'])
            ->from(CourseAttribute::tableName())->where(['category_id' => $categoryId])
            ->orderBy(['sort_order' => SORT_ASC])->all();
        
        $attrs = [];
        foreach ($attributes as $attr){
            $attrs[$attr['name']] = [
                'id' => $attr['id'],
                'values' => explode("\n", $attr['values']),
                'sort_order' => $attr['sort_order']
            ];
        }
        
        return $attrs;
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
