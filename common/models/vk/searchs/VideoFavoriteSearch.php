<?php

namespace common\models\vk\searchs;

use common\models\vk\Course;
use common\models\vk\Customer;
use common\models\vk\PlayStatistics;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * CourseFavoriteSearch represents the model behind the search form of `common\models\vk\CourseFavorite`.
 */
class VideoFavoriteSearch extends VideoFavorite
{
    /**
     *
     * @var Query 
     */
    private static $query;
    
    /**
     * 视频名称
     * @var string 
     */
    public $name;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['course_id', 'video_id', 'user_id', 'group'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }
    
    //收藏的视频情况下
    public function collectSearch($params)
    {
        $sort_name = ArrayHelper::getValue($params, 'sort', 'default');    //排序
        $this->name = ArrayHelper::getValue($params, 'VideoFavoriteSearch.name');    //视频名称
        
        self::getInstance();
        $this->load($params);
        
        //条件查询
        self::$query->andFilterWhere(['Favorite.user_id' => Yii::$app->user->id]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'Video.name', $this->name]);
        //添加字段
        $addArrays = ['Customer.name AS customer_name', 'Course.id AS course_id', 'Course.name AS course_name', 
            'Video.name', "IF(Video.source_is_link, Video.img, CONCAT('/', Video.img)) AS img", 'Video.source_duration', 'Teacher.id AS teacher_id',
            'Teacher.avatar AS teacher_avatar', 'Teacher.name AS teacher_name'
        ];
        //排序
        if($sort_name != 'default'){
            self::$query->orderBy(["Favorite.{$sort_name}" => SORT_DESC]);
        }
        return $this->search($params, $addArrays);
    }

    //在引用视频的情况下
    public function referenceSearch($params)
    {
        $sort_name = ArrayHelper::getValue($params, 'sort', 'created_at');    //排序
        $this->name = ArrayHelper::getValue($params, 'VideoFavoriteSearch.name');    //视频名称
        
        self::getInstance();
        $this->load($params);
        
        //条件查询
        self::$query->andFilterWhere(['Favorite.user_id' => Yii::$app->user->id]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'Video.name', $this->name]);
        
        //添加字段
        $addArrays = ['Course.name AS course_name', 'Video.name', "IF(Video.source_is_link, Video.img, CONCAT('/', Video.img)) AS img", 
            'Video.source_duration',  'Video.created_at', 'Video.is_ref', 
            'Video.des', 'Video.source_id',
            'Teacher.id AS teacher_id', 'Teacher.avatar AS teacher_avatar',
            'Teacher.name AS teacher_name'
        ];
        //排序
        if($sort_name == 'created_at'){
            self::$query->orderBy(["Favorite.{$sort_name}" => SORT_DESC]);
        }else{
            self::$query->orderBy(["Video.{$sort_name}" => SORT_DESC]);
        }
        
        return $this->search($params, $addArrays);
    }

    /**
     * 使用搜索查询创建数据提供程序实例
     *
     * @param array $params
     * @param array $addArrays  查询属性数组
     *
     * @return ArrayDataProvider
     */
    protected function search($params, $addArrays = [])
    {
        $page = ArrayHelper::getValue($params, 'page', 1); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20); //显示数
        //必要条件
        self::$query->andFilterWhere([
            'Favorite.is_del' => 0,
            'Video.is_del' => 0
        ]);
        //关联查询
        self::$query->leftJoin(['Video' => Video::tableName()], 'Video.id = Favorite.video_id');
        //复制收藏视频对象
        $copyFavoriteVideo= clone self::$query;
        //查询视频的播放量
        $playQuery = (new Query())->select(['Play.video_id', 'SUM(Play.play_count) AS play_num'])
            ->from(['Play' => PlayStatistics::tableName()]);
        $playQuery->where(['Play.video_id' => $copyFavoriteVideo]);
        $playQuery->groupBy('Play.video_id');
        //查询视频下的标签
        $tagRefQuery = TagRef::find()->select(['TagRef.object_id', "GROUP_CONCAT(Tags.`name` ORDER BY TagRef.id ASC SEPARATOR '、') AS tags"])
            ->from(['TagRef' => TagRef::tableName()]);
        $tagRefQuery->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');
        $tagRefQuery->where(['TagRef.is_del' => 0, 'TagRef.object_id' => $copyFavoriteVideo]);
        $tagRefQuery->groupBy('TagRef.object_id');
        //关联查询
        self::$query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Video.customer_id');
        self::$query->leftJoin(['Course' => Course::tableName()], 'Course.id = Favorite.course_id');
        self::$query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');
        //以视频id为分组
        self::$query->groupBy('Favorite.video_id');
        //查询总数
        $totalCount = self::$query->count('video_id');
        //添加字段
        self::$query->addSelect($addArrays);
        //显示数量
        self::$query->offset(($page - 1) * $limit)->limit($limit);
        //视频播放量结果
        $playResult = $playQuery->all();
        //查询标签结果
        $tagRefResult = $tagRefQuery->asArray()->all();
        //查询收藏视频的结果
        $videoResult = self::$query->asArray()->all();
        //以video_id为索引
        $videos = ArrayHelper::index($videoResult, 'video_id');
        $results = ArrayHelper::merge(ArrayHelper::index($playResult, 'video_id') ,
            ArrayHelper::index($tagRefResult, 'object_id'));
        //合并查询后的结果
        foreach ($videos as $id => $item) {
            if(isset($results[$id])){
                $videos[$id] += $results[$id];
            }
        }
        
        return [
            'filter' => $params,
            'total' => $totalCount,
            'data' => [
                'video' => $videos
            ],
        ];
    }
    
    /**
     * 
     * @return Query
     */
    protected static function getInstance() {
        if (self::$query == null) {
            self::$query = self::findVideoFavorite();
        }
        return self::$query;
    }
    
    /**
     * 查询关注的课程
     * @return Query
     */
    public static function findVideoFavorite() 
    {
        $query = self::find()->select(['Favorite.video_id'])
            ->from(['Favorite' => self::tableName()]);
        
        return $query;
    }
}
