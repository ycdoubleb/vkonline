<?php

namespace common\models\vk\searchs;

use common\models\User;
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
 * VideoSearch represents the model behind the search form of `common\models\vk\Video`.
 */
class VideoSearch extends Video
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'node_id', 'teacher_id', 'source_id', 'customer_id', 'ref_id', 'name', 'source_level', 'source_wh', 'source_bitrate', 'content_level', 'des', 'level', 'img', 'is_ref', 'is_recommend', 'is_publish', 'sort_order', 'created_by'], 'safe'],
            [['zan_count', 'favorite_count', 'created_at', 'updated_at'], 'integer'],
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
        $tags = ArrayHelper::getValue($params, 'VideoSearch.tags');
        $query = (new Query())
                ->select(['Video.id', 'Customer.name AS customer_id', 'Video.name', 'Teacher.name AS teacher_id', 
                        'User.nickname AS created_by', 'Video.is_publish', 'Video.level', 'SUM(Uploadfile.size) AS size',
                        'Tags.name AS tags', 'Video.created_at'])
                ->from(['Video' => Video::tableName()]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);
        
        $query->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = Video.customer_id');//关联查询所属客户
        $query->leftJoin(['Teacher' => Teacher::tableName()], 'Teacher.id = Video.teacher_id');    //关联查询主讲老师
        $query->leftJoin(['User' => User::tableName()], 'User.id = Video.created_by');             //关联查询课程创建者
        $query->leftJoin(['Attachment' => VideoAttachment::tableName()], 'Attachment.video_id = Video.id'); //关联查询视频附件中间表
        //关联查询视频文件/关联查询视频附件
        $query->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Video.source_id OR Uploadfile.id = Attachment.file_id');     
        $query->leftJoin(['TagRef' => TagRef::tableName()], 'TagRef.object_id = Video.id');        //关联查询标签中间表
        $query->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id');                //关联查询标签
        
        $query->where(['Video.is_del' => 0]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        // grid filtering conditions
        $query->andFilterWhere([
            'Video.customer_id' => $this->customer_id,
            'Video.teacher_id' => $this->teacher_id,
            'Video.created_by' => $this->created_by,
            'Video.is_publish' => $this->is_publish,
            'Video.level' => $this->level,
        ]);

        $query->andFilterWhere(['like', 'Video.name', $this->name])
                ->andFilterWhere(['like', 'Tags.name', $tags]);

        $query->groupBy(['Video.id']);
        
        return $dataProvider;
    }
    
    /**
     * 
     * @param string $id
     * @return ActiveDataProvider
     */
    public function  relationSearch($id)
    {
        $query = (new Query())->select(['Course.name', 'User.nickname'])
            ->from(['Video' => self::tableName()]);
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], 'CourseNode.id = Video.node_id');
        $query->leftJoin(['Course' => Course::tableName()], 'Course.id = CourseNode.course_id');
        $query->leftJoin(['User' => User::tableName()], 'User.id = Course.created_by');
        
        $query->andFilterWhere([
            'Video.ref_id' => $id
        ]);
        
        $query->groupBy('Course.id');
        
        return $dataProvider;
    }
}
