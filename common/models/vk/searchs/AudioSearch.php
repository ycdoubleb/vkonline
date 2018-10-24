<?php

namespace common\models\vk\searchs;

use common\models\User;
use common\models\vk\Audio;
use common\models\vk\UserCategory;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * AudioSearch represents the model behind the search form of `common\models\vk\Audio`.
 */
class AudioSearch extends Audio
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'file_id', 'customer_id', 'name', 'des', 'created_by'], 'safe'],
            [['user_cat_id', 'content_level', 'level', 'is_recommend', 'is_publish', 'is_official', 'zan_count', 'favorite_count', 'is_del', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['duration'], 'number'],
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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $sign = ArrayHelper::getValue($params, 'sign', 0);  //标记搜索方式
        $page = ArrayHelper::getValue($params, 'page', 1); //分页
        $limit = ArrayHelper::getValue($params, 'limit', 20); //显示数
        $this->user_cat_id = ArrayHelper::getValue($params, 'user_cat_id', null);    //用户分类id
        
        $query = Audio::find()->from(['Audio' => Audio::tableName()]);

        $this->load($params);

        //目录
        if($sign){
            //获取分类的子级ID    
            $userCatIds = UserCategory::getCatChildrenIds($this->user_cat_id, true);     
            $query->andFilterWhere([
                'Audio.user_cat_id' => !empty($userCatIds) ? 
                    ArrayHelper::merge([$this->user_cat_id], $userCatIds) : $this->user_cat_id,
            ]);
        }else{
            if($this->user_cat_id != null && !$sign){
                $query->andFilterWhere(['Audio.user_cat_id' => $this->user_cat_id]);
            }else{
                $query->andFilterWhere(['Audio.user_cat_id' => 0]);
            }
        }
        
        //过滤条件
        $query->andFilterWhere(['Audio.is_del' => 0,]);
        //模糊查询
        $query->andFilterWhere(['like', 'Audio.name', $this->name]);
        
        //关联查询
        $query->leftJoin(['UserCategory' => UserCategory::tableName()], 'UserCategory.id = Audio.user_cat_id');
        $query->leftJoin(['User' => User::tableName()], 'User.id = Audio.created_by');
        
        //如果目录类型是共享类型则显示共享文件
        $query->andFilterWhere(['OR', 
            ['Audio.created_by' => \Yii::$app->user->id], 
            new Expression("IF(UserCategory.type=:type, Audio.customer_id=:customer_id AND Audio.is_del = 0, null)", [
                'type' => UserCategory::TYPE_SHARING, 'customer_id' => Yii::$app->user->identity->customer_id
            ])
        ]);
        
        //以音频id为分组
        $query->groupBy(['Audio.id']);
        //查询总数
        $totalCount = $query->count('id');
        //添加字段
        $query->select([
            'Audio.id', 'Audio.user_cat_id', 'Audio.name', 'Audio.duration', 'Audio.created_at', 
            'Audio.is_publish', 'Audio.level', 'UserCategory.type', 'User.nickname'
        ]);
        //显示数量
        $query->offset(($page - 1) * $limit)->limit($limit);
        
        //查询的音频结果
        $audioResult = $query->asArray()->all();   
        //以audio_id为索引
        $audios = ArrayHelper::index($audioResult, 'id');
        
        return [
            'filter' => $params,
            'total' => $totalCount,
            'data' => [
                'audio' => $audios
            ],
        ];
    }
}