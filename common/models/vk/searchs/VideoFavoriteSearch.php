<?php

namespace common\models\vk\searchs;

use common\models\vk\Video;
use common\models\vk\VideoFavorite;
use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
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

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ArrayDataProvider
     */
    public function search($params)
    {
        $keyword = ArrayHelper::getValue($params, 'keyword'); //关键字
        $page = ArrayHelper::getValue($params, 'page'); //分页
        $limit = ArrayHelper::getValue($params, 'limit'); //显示数
        
        self::getInstance();

        if($this->load($params)){
            self::$query->andFilterWhere([
                'Favorite.id' => $this->id,
                'Favorite.course_id' => $this->course_id,
                'Favorite.video_id' => $this->video_id,
                'Favorite.user_id' => $this->user_id,
                'Favorite.group' => $this->group,
                'Favorite.created_at' => $this->created_at,
                'Favorite.updated_at' => $this->updated_at,
            ]);
        }else{
            self::$query->andFilterWhere([
                'Favorite.user_id' => Yii::$app->user->id,
                'Video.is_del' => 0,
            ]);
        }
        
        //关联查询
        self::$query->with('course', 'video', 'video.teacher');
        //添加字段
        self::$query->select(['Favorite.*']);
        self::$query->leftJoin(['Video' => Video::tableName()], 'Video.id = Favorite.video_id');
        //模糊查询
        self::$query->andFilterWhere(['like', 'Video.name', $keyword]);
        //显示数量
        self::$query->offset(($page-1) * $limit)->limit($limit);
        $videoResult = self::$query->asArray()->all();
        //查询总数
        $totalCount = count($videoResult);
        //分页
        $pages = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]); 
        
        return [
            'filter' => $params,
            'pager' => $pages,
            'total' => $totalCount,
            'data' => [
                'video' => $videoResult
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
