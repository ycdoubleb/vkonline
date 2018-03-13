<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\Teacher;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\modules\webuploader\models\Uploadfile;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * CourseSearch represents the model behind the search form of `common\models\vk\Course`.
 */
class CourseSearch extends Course
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'customer_id', 'teacher_id', 'name', 'level', 'des', 'cover_img', 'is_recommend', 'is_publish', 'created_by'], 'safe'],
            [['category_id', 'zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $tags = ArrayHelper::getValue($params, 'CourseSearch.tags');
        $query = Course::find()
                ->select(['Course.id', 'Customer.name AS customer_id', 'Category.name AS category_id', 'Course.name',
                            'Teacher.name AS teacher_id', 'User.nickname AS created_by', 'Course.is_publish',
                            'Course.level', 'SUM(Uploadfile.size) AS size', 'Tags.name AS tags', 'Course.created_at'])
                ->from(['Course' => Course::tableName()]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);

        $query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Course.customer_id');//关联查询所属客户
        $query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Course.teacher_id');    //关联查询主讲老师
        $query->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by');             //关联查询课程创建者
        $query->leftJoin(['Category' => Category::tableName()], 'Category.id = Course.category_id');//关联查询课程所属分类
        $query->leftJoin(['Node' => CourseNode::tableName()], 'Node.course_id = Course.id');        //关联节点找相应的视频
        $query->leftJoin(['Video' => Video::tableName()], 'Video.node_id = Node.id');               //关联查询视频
        $query->leftJoin(['Attachment' => VideoAttachment::tableName()], 'Attachment.video_id = Video.id'); //关联查询视频附件中间表
        //关联查询视频文件/关联查询视频附件
        $query->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Video.source_id OR Uploadfile.id = Attachment.file_id');     
        
        $query->leftJoin(['TagRef' => TagRef::tableName()], 'TagRef.object_id = Course.id');        //关联查询标签中间表
        $query->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');                 //关联查询标签
               
        $this->load($params);
        
        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
            
        // grid filtering conditions
        $query->andFilterWhere([
            'Course.customer_id' => $this->customer_id,
            'Course.category_id' => $this->category_id,
            'Course.teacher_id' => $this->teacher_id,
            'Course.created_by' => $this->created_by,
            'Course.is_publish' => $this->is_publish,
            'Course.level' => $this->level,
        ]);

        $query->andFilterWhere(['like', 'Course.name', $this->name])
                ->andFilterWhere(['like', 'Tags.name', $tags]);
        
        return $dataProvider;
    }
}
