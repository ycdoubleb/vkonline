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

    //我的资源师资搜索
    public function resourceSearch($params)
    {
        self::getInstance();
        $this->load($params);
           
        //条件查询
        self::$query->andFilterWhere([
            'Teacher.created_by' => Yii::$app->user->id,
            'is_certificate' => $this->is_certificate,
        ]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'name', $this->name]);
        return $this->search($params);
    }
    
    //内容中心全部师资搜索
    public function contentSearch($params)
    {
        self::getInstance();
        $this->load($params);
           
        //条件查询
        self::$query->andFilterWhere([
            'Teacher.customer_id' => Yii::$app->user->identity->customer_id,
            'is_certificate' => $this->is_certificate,
        ]);
        //模糊查询
        self::$query->andFilterWhere(['like', 'name', $this->name]);
        return $this->search($params);
    }
    
    //名师堂同名认证下的搜索
    public function teacherSearch($params)
    {
        $this->name = ArrayHelper::getValue($params, 'name');   //老师名称
        
        self::getInstance();
        $this->load($params);
           
        //条件查询
        self::$query->andFilterWhere([
            'Teacher.name' => $this->name,
            'is_certificate' => 1,
        ]);
        
        return $this->search($params);
    }


    /**
     * 使用搜索查询创建数据提供程序实例
     *
     * @param array $params
     *
     * @return ArrayDataProvider
     */
    protected function search($params)
    {
        $page = ArrayHelper::getValue($params, 'page', 1); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20); //显示数
        //查询总数
        $totalCount = self::$query->count('id');
        //显示数量
        self::$query->offset(($page - 1) * $limit)->limit($limit);
        //添加字段
        self::$query->addSelect(['Teacher.avatar', 'Teacher.is_certificate', 'Teacher.name', 'Teacher.job_title']);
        //查询老师结果
        $teacherResult = self::$query->asArray()->all();

        return [
            'filter' => $params,
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
