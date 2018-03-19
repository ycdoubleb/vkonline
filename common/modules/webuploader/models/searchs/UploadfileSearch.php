<?php

namespace common\modules\webuploader\models\searchs;

use common\models\User;
use common\modules\webuploader\models\Uploadfile;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * UploadfileSearch represents the model behind the search form of `common\modules\webuploader\models\Uploadfile`.
 */
class UploadfileSearch extends Uploadfile {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'name', 'path', 'thumb_path', 'app_id', 'del_mark', 'is_del', 'is_fixed', 'created_by', 'deleted_by'], 'safe'],
            [['download_count', 'deleted_at', 'created_at', 'updated_at'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
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
    public function search($params) {
        $time = ArrayHelper::getValue($params, 'time');                                                         //时间段
        $query = (new Query())
                ->select(['Uploadfile.id', 'Uploadfile.name', 'Uploadfile.del_mark', 'Uploadfile.is_del',
                    'User.nickname AS created_by', 'Uploadfile.path', 'Uploadfile.created_at'])
                ->from(['Uploadfile' => Uploadfile::tableName()]);

        // add conditions that should always apply here

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
        //关联查询创建者
        $query->leftJoin(['User' => User::tableName()], 'User.id = Uploadfile.created_by');
        
        //按时间段搜索
        if($time != null){
            $times = explode(" - ", $time);
            $query->andFilterWhere(['between', 'Uploadfile.created_at', strtotime($times[0]), strtotime($times[1])]);
        }
        // grid filtering conditions
        $query->andFilterWhere([
            'download_count' => $this->download_count,
            'del_mark' => $this->del_mark,
            'is_del' => $this->is_del,
            'is_fixed' => $this->is_fixed,
            'size' => $this->size,
            'created_by' => $this->created_by,
        ]);

        $query->andFilterWhere(['like', 'Uploadfile.id', $this->id])
                ->andFilterWhere(['like', 'name', $this->name])
                ->andFilterWhere(['like', 'path', $this->path])
                ->andFilterWhere(['like', 'thumb_path', $this->thumb_path]);

        return $dataProvider;
    }
    
}
