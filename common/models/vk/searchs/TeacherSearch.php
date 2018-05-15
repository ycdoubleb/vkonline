<?php

namespace common\models\vk\searchs;

use common\models\vk\Course;
use common\models\vk\Teacher;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\data\Pagination;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * TeacherSearch represents the model behind the search form of `common\models\vk\Teacher`.
 */
class TeacherSearch extends Teacher
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
            [['id', 'name', 'sex', 'avatar', 'level', 'job_title', 'customer_id', 'des', 'is_certificate', 'created_by'], 'safe'],
            [['certicicate_at', 'created_at', 'updated_at'], 'integer'],
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
     * 后台教师列表
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchTeacher($params)
    {
        $query = Teacher::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['is_certificate' => $this->is_certificate]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
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
        $this->customer_id = ArrayHelper::getValue($params, 'TeacherSearch.customer_id', Yii::$app->user->identity->customer_id); //客户id
        $this->created_by = ArrayHelper::getValue($params, 'TeacherSearch.created_by', Yii::$app->user->id); //创建者
        $page = ArrayHelper::getValue($params, 'page', 1); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20); //显示数

        self::getInstance();
        $this->load($params);
           
        //条件查询
        self::$query->andFilterWhere([
            'Teacher.customer_id' => $this->customer_id,
            'Teacher.created_by' => $this->created_by,
            'Teacher.sex' => $this->sex,
            'Teacher.level' => $this->level,
            'is_certificate' => $this->is_certificate,
            'Teacher.created_at' => $this->created_at,
            'Teacher.updated_at' => $this->updated_at,
        ]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'name', $this->name]);

        //显示数量
        self::$query->offset(($page - 1) * $limit)->limit($limit);
        //查询总数
        $totalCount = self::$query->count('id');
        //添加字段
        self::$query->select(['Teacher.*']);
        $teacherResult = self::$query->asArray()->all();
        //分页
        $pages = new Pagination(['totalCount' => $totalCount, 'defaultPageSize' => $limit]); 

        return [
            'filter' => $params,
            'pager' => $pages,
            'total' => $totalCount,
            'data' => [
                'teacher' => $teacherResult
            ],
        ];
    }
    
    /**
     * 
     * @return Query
     */
    protected static function getInstance() {
        if (self::$query == null) {
            self::$query = self::findTeacher();
        }
        return self::$query;
    }
    
    /**
     * 查询老师
     * @return Query
     */
    public static function findTeacher() 
    {
        $query = self::find()->select(['Teacher.id'])
            ->from(['Teacher' => self::tableName()]);
        
        return $query;
    }
}
