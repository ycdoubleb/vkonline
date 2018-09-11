<?php

namespace common\models\vk\searchs;

use common\models\AdminUser;
use common\models\vk\BrandAuthorize;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\Teacher;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * BrandAuthorizeSearch represents the model behind the search form of `common\models\vk\BrandAuthorize`.
 */
class BrandAuthorizeSearch extends BrandAuthorize
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'level', 'start_time', 'end_time', 'is_del', 'created_at', 'updated_at'], 'integer'],
            [['brand_from', 'brand_to', 'created_by'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * （后台）Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = (new Query())
                ->select(['BrandAuthorize.id', 'BrandFrom.name AS from_name', 'BrandTo.name AS to_name', 
                    'start_time', 'end_time', 'AdminUser.nickname AS created_by'])
                ->from(['BrandAuthorize' => BrandAuthorize::tableName()]);

        $query->leftJoin(['BrandFrom' => Customer::tableName()], 'BrandFrom.id = BrandAuthorize.brand_from');
        $query->leftJoin(['BrandTo' => Customer::tableName()], 'BrandTo.id = BrandAuthorize.brand_to');
        $query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = BrandAuthorize.created_by');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'is_del' => $this->is_del,
        ]);

        $query->andFilterWhere(['like', 'brand_from', $this->brand_from])
            ->andFilterWhere(['like', 'brand_to', $this->brand_to])
            ->andFilterWhere(['like', 'created_by', $this->created_by]);

        return $dataProvider;
    }
    
    /**
     * 查找获得授权的品牌
     * @param array $params
     * @return ActiveDataProvider
     */
    public function searchBrandAuthrize($params)
    {
        $brand_from = ArrayHelper::getValue($params, 'BrandAuthorizeSearch.brand_from');

        $query = (new Query())
                ->select(['BrandAuthorize.id', 'BrandAuthorize.is_del', 'BrandFrom.name', 'BrandFrom.logo'])
                ->from(['BrandAuthorize' => BrandAuthorize::tableName()]);

        $query->where(['BrandAuthorize.brand_to' => Yii::$app->user->identity->customer->id]);
        $query->andFilterWhere(['BrandAuthorize.is_del' => 0]);     //授权有效
        $query->andFilterWhere(['>=', 'BrandAuthorize.end_time', time()]);  //授权未过期
        // add conditions that should always apply here
        $query->leftJoin(['BrandFrom' => Customer::tableName()], 'BrandFrom.id = BrandAuthorize.brand_from');
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'BrandFrom.name', $brand_from]);

        return $dataProvider;
    }
    
    /**
     * 查找授权品牌下的课程
     * @param array $params
     * @return array
     */
    public function searchAuthorizeCourse($params)
    {
        $id = ArrayHelper::getValue($params, 'id');
        $page = ArrayHelper::getValue($params, 'page', 1); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20); //显示数
        $course_name = ArrayHelper::getValue($params, 'course_name'); //课程名称
        $brandAuthorize = BrandAuthorize::findOne($id);
        
        $query = BrandAuthorize::find()
                ->select(['Course.category_id', 'Category.name AS category_name', 'Course.id', 'Course.name', 'Course.cover_img',
                    'Teacher.name AS teacher_name', 'COUNT(CourseNode.id) AS node_num'])
                ->from(['BrandAuthorize' => BrandAuthorize::tableName()]);
        $query->andFilterWhere(['is_publish' => Course::YES_PUBLISH]);    //只可以查看已发布的课程数据
        $this->load($params);
        
        $categoryId = Category::getCatChildrenIds($this->start_time, true);    //获取分类的子级ID
        $query->andFilterWhere(['Course.customer_id' => $brandAuthorize->brand_from]);
        
        //关联查询
        $query->leftJoin(['BrandFrom' => Customer::tableName()], 'BrandFrom.id = BrandAuthorize.brand_from');
        $query->leftJoin(['Course' => Course::tableName()], 'Course.customer_id = BrandFrom.id');
        $query->leftJoin(['Category' => Category::tableName()], 'Category.id = Course.category_id');
        $query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.course_id = Course.id');
        
        //条件查询
        $query->andFilterWhere([
            'Course.category_id' => !empty($categoryId) ? ArrayHelper::merge([$this->start_time], $categoryId) : $this->start_time,
        ]);
        //模糊查询
        $query->andFilterWhere(['like', 'Course.name', $course_name]);
        $query->groupBy(['Course.id']);
        //查询总数
        $totalCount = $query->count('id');
        //显示数量
        $query->offset(($page - 1) * $limit)->limit($limit);
        $authorizeCourses = $query->asArray()->all();

        return [
            'filter' => $params,
            'total' => $totalCount,
            'data' => [
                'course' => $authorizeCourses
            ],
        ];
    }
}
