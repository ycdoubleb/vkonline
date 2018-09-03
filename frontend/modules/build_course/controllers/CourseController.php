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
use common\models\vk\Teacher;
use common\utils\StringUtil;
use frontend\modules\build_course\utils\ActionUtils;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
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
                    'close' => ['POST'],
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
     * @return string|json
     */
    public function actionIndex()
    {
        $searchModel = new CourseSearch();
        $results = $searchModel->buildCourseSearch(array_merge(\Yii::$app->request->queryParams, ['limit' => 6]));
        $courses = array_values($results['data']['course']);    //课程数据
        //重修课程数据里面的元素值
        foreach ($courses as &$item) {
            $item['cover_img'] = StringUtil::completeFilePath($item['cover_img']);
            $item['level'] = Course::$levelMap[$item['level']];
            $item['is_hidden'] = $item['level'] != Course::INTRANET_LEVEL ? 'hidden' : '';
            $item['color_name'] = $item['is_publish'] ? 'success' : 'danger';
            $item['is_publish'] = Course::$publishStatus[$item['is_publish']];
            $item['teacher_avatar'] = StringUtil::completeFilePath($item['teacher_avatar']);
            $item['tags'] = isset($item['tags']) ? $item['tags'] : 'null';
        }
       
        //如果是ajax请求，返回json
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => [
                        'result' => $courses, 
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
            'filters' => $results['filter'],    //查询过滤的属性
            'totalCount' => $results['total'],  //总数量
        ]);
    }
    
    /**
     * 显示一个单一的 Course 模型。
     * @param string $id
     * @return mixed 
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchUserModel = new CourseUserSearch();
        $searchNodeModel = new CourseNodeSearch();
        $searchCourseLog = new CourseActLogSearch();
        
        return $this->render('view', [
            'model' => $model,  //模型
            'courseLogModel' => $searchCourseLog,   //课程操作记录搜索模型
            'courseUsers' => $searchUserModel->search(['course_id' => $model->id]), //所有协作人员
            'courseNodes' => $searchNodeModel->search(['course_id' => $model->id]), //所有课程节点
            'courseLogs' => $searchCourseLog->search(['course_id' => $model->id]),  //所有课程操作记录
            'courseAttrs' => $this->getCourseAttrByCourseId($model->id),    //已选的课程属性
            'logs' => ActionUtils::getInstance()->getCourseActLogs($model->id), //该课程下的所有操作记录
            'path' => !empty($model->category_id) ? $this->getCategoryFullPath($model->category_id) : '',  //分类全路径
            'haveAllPrivilege' => ActionUtils::getInstance()->getIsHavePermission($model->id),  //只有全部权限
            'haveEditPrivilege' => ActionUtils::getInstance()->getIsHavePermission($model->id, true), //包含编辑权限
        ]);
    }
   
    /**
     * 创建 一个新的 Course 模块
     * 如果创建成功，浏览器将被重定向到“查看”页面。
     * @return mixed 
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
                'model' => $model,  //模型
                'teacherMap' => Teacher::getTeacherByLevel(Yii::$app->user->id, 0, false),  //和自己相关的老师
                'attFiles' => [],
            ]);
        }
    }
    
    /**
     * 更新 现有的 Course 模型。
     * 如果更新成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed 
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        
        if(ActionUtils::getInstance()->getIsHavePermission($model->id)){
            if($model->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{beenPublished}{canNot}{Edit}', [
                    'beenPublished' => Yii::t('app', 'The course has been published,'),
                    'canNot' => Yii::t('app', 'Can not be '), 'Edit' => Yii::t('app', 'Edit')
                ]));
            }
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->updateCourse($model, Yii::$app->request->post());
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,  //模型
                'teacherMap' => Teacher::getTeacherByLevel($model->created_by, 0, false),   //和自己相关的老师
                'attFiles' => Course::getUploadfileByAttachment($model->id),    //已存在的附近
                'allAttrs' => $this->getCourseAttributeByCategoryId($model->category_id),   //已存在的属性
                'attrsSelected' => array_keys($this->getCourseAttrByCourseId($model->id)),  //已选的属性
                'tagsSelected' => array_values(TagRef::getTagsByObjectId($model->id, 1)),  //已选的标签
            ]);
        }
    }
    
    /**
     * 删除 现有的 Course 模型。
     * 如果删除成功，浏览器将被重定向到“查看”页面。
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        if(ActionUtils::getInstance()->getIsHavePermission($model->id)){
            if($model->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{beenPublished}{canNot}{Delete}', [
                    'beenPublished' => Yii::t('app', 'The course has been published,'),
                    'canNot' => Yii::t('app', 'Can not be '), 'Delete' => Yii::t('app', 'Delete')
                ]));
            }
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if (Yii::$app->request->isPost) {
            ActionUtils::getInstance()->deleteCourse($model);
            return $this->redirect(['index']);
        }
        
    }
    
    /**
     * 关闭 现有的 Course 模型。
     * 如果关闭成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed
     */
    public function actionClose($id)
    {
        $model = $this->findModel($id);
        
        if(ActionUtils::getInstance()->getIsHavePermission($model->id)){
            if(!$model->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{notPublished}{canNot}{Down}{Shelves}', [
                    'notPublished' => Yii::t('app', 'The course is not published,'),
                    'canNot' => Yii::t('app', 'Can not be ') ,
                    'Down' => Yii::t('app', 'Down'), 'Shelves' => Yii::t('app', 'Shelves')
                ]));
            }
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if (Yii::$app->request->isPost) {
            ActionUtils::getInstance()->closeCourse($model);
            return $this->redirect(['view', 'id' => $model->id]);
        }
    }
    
    /**
     * 发布 现有的 Course 模型。
     * 如果发布成功，浏览器将被重定向到“查看”页面。
     * @param integer $id
     * @return mixed
     */
    public function actionPublish($id)
    {
        $model = $this->findModel($id);
        
        if(ActionUtils::getInstance()->getIsHavePermission($model->id)){
            if($model->is_publish){
                throw new NotFoundHttpException(Yii::t('app', '{beenPublished}{canNot}{Publish}', [
                    'beenPublished' => Yii::t('app', 'The course has been published,'),
                    'canNot' => Yii::t('app', 'Can not be '), 'Publish' => Yii::t('app', 'Publish')
                ]));
            }
            if($model->is_del){
                throw new NotFoundHttpException(Yii::t('app', 'The course does not exist.'));
            }
        }else{
            throw new NotFoundHttpException(Yii::t('app', 'You have no permissions to perform this operation.'));
        }
        
        if ($model->load(Yii::$app->request->post())) {
            ActionUtils::getInstance()->publishCourse($model);
            return $this->redirect(['view', 'id' => $model->id]);
        }
        
        return $this->renderAjax('publish', [
            'model' => $model,  //模型
        ]);
    }
    
    /**
     * 通过分类id查找分类下对应的属性
     * @param integer $cate_id
     * @return json
     */
    public function actionAttrSearch($cate_id)
    {
        $courseAttrs = $this->getCourseAttributeByCategoryId($cate_id);
        
        //如果是ajax请求，返回json
        if(\Yii::$app->request->isPost){
            Yii::$app->getResponse()->format = 'json';
            try
            { 
                return [
                    'code'=> 200,
                    'data' => $courseAttrs,
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
            ->from(CourseAttribute::tableName())
            ->where(['category_id' => $categoryId, 'is_del' => 0])
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
