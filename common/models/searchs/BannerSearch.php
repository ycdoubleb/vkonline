<?php

namespace common\models\searchs;

use common\models\AdminUser;
use common\models\Banner;
use common\models\User;
use common\models\vk\Customer;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * BannerSearch represents the model behind the search form of `common\models\Banner`.
 */
class BannerSearch extends Banner
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['title', 'path', 'link', 'target', 'type', 'sort_order', 'is_publish', 'des', 'created_by'], 'safe'],
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
        $query = Banner::find()
                ->select(['Banner.id', 'Banner.title', 'Banner.path', 'Banner.link','Banner.target', 
                    'Banner.sort_order', 'Banner.type', 'Banner.is_publish', 'Banner.created_at', 
                    'AdminUser.nickname AS created_by'])
                ->from(['Banner' => Banner::tableName()]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'key' => 'id',
        ]);

        $query->leftJoin(['AdminUser' => AdminUser::tableName()], 'AdminUser.id = Banner.created_by');//关联查询创建人
        
        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }
        
        // grid filtering conditions
        $query->andFilterWhere([
            'Banner.is_publish' => $this->is_publish,
            'Banner.created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'Banner.title', $this->title]);
        
        return $dataProvider;
    }
}
