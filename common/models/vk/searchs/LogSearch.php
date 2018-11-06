<?php

namespace common\models\vk\searchs;

use common\models\vk\Log;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * LogSearch represents the model behind the search form of `common\models\vk\Log`.
 */
class LogSearch extends Log
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'level'], 'integer'],
            [['category', 'title', 'from', 'content', 'created_by', 'created_at', 'updated_at'], 'safe'],
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
     * 使用搜索查询创建数据提供程序实例
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Log::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        //过滤条件
        $query->andFilterWhere([
            'level' => $this->level,
            'category' => $this->category,
            'title' => $this->title,
            'created_by' => $this->created_by,
            'from' => $this->from,
        ]);

        //按时间段搜索
        if($this->created_at != null){
            $time = explode(" - ", $this->created_at);
            $query->andFilterWhere(['between', 'created_at', strtotime($time[0]), strtotime($time[1])]);
        }
        
        //模糊查询
        $query->andFilterWhere(['like', 'content', $this->content]);
        
        $query->orderBy(['created_at' => SORT_DESC]);
        
        return $dataProvider;
    }
}
