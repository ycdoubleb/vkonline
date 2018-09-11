<?php

namespace frontend\modules\res_service\controllers;

use common\components\aliyuncs\Aliyun;
use common\models\vk\BrandAuthorize;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Knowledge;
use common\models\vk\KnowledgeVideo;
use common\models\vk\searchs\BrandAuthorizeSearch;
use common\models\vk\VideoFile;
use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

/**
 * BrandAuthorize controller for the `res_service` module
 */
class BrandAuthorizeController extends Controller
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
    
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    
    public function actionToIndex()
    {
        return $this->render('to-index');
    }
    
    /**
     * 获得授权的品牌
     * @return mixed
     */
    public function actionFromIndex()
    {
        $params = Yii::$app->request->queryParams;
        $searchModel = new BrandAuthorizeSearch();
        $dataProvider = $searchModel->searchBrandAuthrize($params);

        return $this->render('from-index',[
            'params' => $params,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * 品牌详情
     * @param integer $id
     * @return mixed
     */
    public function actionFromView($id)
    {
        $to_id = BrandAuthorize::findOne(['id' => $id])->brand_to;  //获得授权的ID
        $i_customer_id = \Yii::$app->user->identity->customer->id;  //本人所在的集团ID
        
        if($to_id != $i_customer_id){
            throw new ForbiddenHttpException('没有权限访问！');
        }
        $searchModel = new BrandAuthorizeSearch();
        $result = $searchModel->searchAuthorizeCourse(Yii::$app->request->queryParams);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $result['data']['course']
        ]);
        
        return $this->render('from-view',[
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'totalCount' => $result['total'],       //课程总数量
            'filters' => $result['filter'],         //过滤条件
            'catFullPath' => $this->getCategoryFullPath(ArrayHelper::getColumn($dataProvider->allModels, 'id')),    //分类全路径
        ]);
    }
    
    /**
     * 课程详情
     * @param string $id
     * @return mixed
     */
    public function actionFromCourse_info($id)
    {
        $result = $this->getFromCourseInfo($id);
        $dataProvider = new ArrayDataProvider([
            'allModels' => $result,
        ]);

        return $this->render('from-course_info',[
            'id' => $id,
            'dataProvider' => $dataProvider,
            'catFullPath' => $this->getCategoryFullPath(ArrayHelper::getColumn($dataProvider->allModels, 'course_id')),    //分类全路径
        ]);
    }

    /**
     * 获得授权课程的信息
     * @param string $id    课程ID
     * @return array
     */
    public static function getFromCourseInfo($id)
    {
        $query = (new Query())
                ->from(['Course' => Course::tableName()])
                ->where(['Course.id' => $id, 'CourseNode.is_del' => 0, 'Knowledge.is_del' => 0, 'KnowledgeVideo.is_del' => 0]);
        $query->leftJoin(['Category' => Category::tableName()], 'Category.id = Course.category_id');    //关联分类
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.course_id = Course.id');//查询课程节点
        $query->leftJoin(['Knowledge' => Knowledge::tableName()], 'Knowledge.node_id = CourseNode.id'); //查询知识点
        $query->leftJoin(['KnowledgeVideo' => KnowledgeVideo::tableName()], 'KnowledgeVideo.knowledge_id = Knowledge.id');  //查询知识点视频
                
        /** 查询环节的知识点数量 */
        $knowNumQuery = clone $query;
        $knowNumQuery->addSelect(['CourseNode.id AS node_id', 'COUNT(Knowledge.id) AS know_num']);
        $knowNumQuery->groupBy(['CourseNode.id']);
        $knowNumQuery->orderBy(['CourseNode.sort_order' => SORT_ASC, 'Knowledge.sort_order' => SORT_ASC]);
        $knowNum = $knowNumQuery->all();
        /** 查询知识点信息 */
        $knowledgeQuey = clone $query;
        $knowledgeQuey->addSelect(['Course.id AS course_id', 'Course.name AS course_name', 'CourseNode.id AS node_id', 'CourseNode.name AS node_name',
                    'Knowledge.id AS knowledge_id', 'Knowledge.name AS knowledge_name']);
        $knowledgeQuey->groupBy(['Knowledge.id']);
        $knowledgeQuey->orderBy(['CourseNode.sort_order' => SORT_ASC, 'Knowledge.sort_order' => SORT_ASC]);
        $knowledges = $knowledgeQuey->all();
        /** 查询视频资源信息 */
        $sourceQuery = clone $query;
        $videoSourse = self::getVideoSorce($sourceQuery);

        /** 组装数据 （把每个节点的知识点数组装到$knowledges） */
        $knowNums = ArrayHelper::index($knowNum, 'node_id');    //改node_id为key值
        foreach ($knowledges as $k => $val) {
            if(isset($knowNums[$val['node_id']])){
                $knowledges[$k] += ['rowspan' => $knowNums[$val['node_id']]['know_num']];
                unset($knowNums[$val['node_id']]);  //只设置相同个节点中的第一个
            }
        }
        /** 组装数据 （把视频路径组装到$knowledge_datas） */
        $knowledge_datas = ArrayHelper::index($knowledges, 'knowledge_id');     //改knowledge_id为key值
        foreach ($knowledge_datas as $key => $value) {
            if(isset($videoSourse[$key])){
                $knowledge_datas[$key] += ['video_source' => $videoSourse[$key]];
            }
        }

        return  $knowledge_datas;
    }

    /**
     * 获取视频资源信息
     * @param Query $sourceQuery
     * @return array
     */
    public static function getVideoSorce($sourceQuery)
    {
        $sourceQuery->leftJoin(['VideoFile' => VideoFile::tableName()], 'VideoFile.video_id = KnowledgeVideo.video_id');  //查询视频文件
        $sourceQuery->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = VideoFile.file_id');           //查询文件
        
        $sourceQuery->addSelect(['Knowledge.id AS knowledge_id', 'Uploadfile.oss_key', 'is_source', 'Uploadfile.level']);
        $sourceQuery->groupBy(['Knowledge.id']);
        $sourceQuery->andFilterWhere(['VideoFile.is_del' => 0, 'Uploadfile.is_del' => 0]);
        
        //原视频
        $sourceVideo = clone $sourceQuery;
        $sourceVideo->andFilterWhere(['is_source' => 1, 'Uploadfile.level' => 0]);
        $sourceDatas = $sourceVideo->all();
        //流畅
        $ldVideo = clone $sourceQuery;
        $ldVideo->andFilterWhere(['is_source' => 0, 'Uploadfile.level' => 0]);
        $ldDatas = $ldVideo->all();
        //标清
        $sdVideo = clone $sourceQuery;
        $sdVideo->andFilterWhere(['is_source' => 0, 'Uploadfile.level' => 1]);
        $sdDatas = $sdVideo->all();
        //高清
        $hdVideo = clone $sourceQuery;
        $hdVideo->andFilterWhere(['is_source' => 0, 'Uploadfile.level' => 2]);
        $hdDatas = $hdVideo->all();
        //超清
        $fdVideo = clone $sourceQuery;
        $fdVideo->andFilterWhere(['is_source' => 0, 'Uploadfile.level' => 3]);
        $fdDatas = $fdVideo->all();

        $videoDatas = ArrayHelper::merge($sourceDatas, $ldDatas, $sdDatas, $hdDatas, $fdDatas);
 
        /** 组装视频地址数据 (相对路径改为绝对路径) */
        $source = [];
        foreach ($videoDatas as $videoData) {
            if($videoData['is_source'] == 1 && $videoData['level'] == 0){
                $source[$videoData['knowledge_id']]['source_video'] = Aliyun::absolutePath($videoData['oss_key']);
            } elseif ($videoData['is_source'] == 0 && $videoData['level'] == 0) {
                $source[$videoData['knowledge_id']]['ld_video'] = Aliyun::absolutePath($videoData['oss_key']);
            } elseif ($videoData['is_source'] == 0 && $videoData['level'] == 1) {
                $source[$videoData['knowledge_id']]['sd_video'] = Aliyun::absolutePath($videoData['oss_key']);
            } elseif ($videoData['is_source'] == 0 && $videoData['level'] == 2) {
                $source[$videoData['knowledge_id']]['hd_video'] = Aliyun::absolutePath($videoData['oss_key']);
            } elseif ($videoData['is_source'] == 0 && $videoData['level'] == 3) {
                $source[$videoData['knowledge_id']]['fd_video'] = Aliyun::absolutePath($videoData['oss_key']);
            }
        }
        
        return $source;
    }

    /**
     * 获取所有课程下的分类全路径
     * @param array $courseIds
     * @return array    键值对
     */
    public static function getCategoryFullPath($courseIds) 
    {
        $catpath = [];
        $fullPath = [];
        //根据课程id查出所有分类
        $allModels = (new Query())->select(['id', 'category_id'])
            ->from(Course::tableName())->where(['id' => $courseIds, 'is_del' => 0])->all();
        //分类路径名称
        foreach ($allModels as $model){
            $parentids = array_values(array_filter(explode(',', Category::getCatById($model['category_id'])->path)));
            foreach ($parentids as $index => $id) {
                $catpath[$model['id']][] = ($index == 0 ? '' : ' > ') . Category::getCatById($id)->name;
            }
        }
        //课程id => 路径 
        foreach ($catpath as $id => $value) {
            $fullPath[$id] = implode('', $value);
        }
        
        return $fullPath;
    }
}
