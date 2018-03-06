<?php
namespace common\modules\rbac\models\searchs;

use yii\base\Model;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AssignmentSearch
 *
 * @author Administrator
 */
class AssignmentSearch  extends Model
{
    public $username;
    public $nickname;
    
    public function rules() 
    {
        return [
            [['username','nickname'],'safe']
        ];
    }
    
    /**
     * 过滤
     * @param array                         $params          
     * @param yii\db\ActiveRecord           $class           用户模型
     * @param string $usernameField         用户 username 列名
     * @return yii\data\ActiveDataProvider 
     */
    public function search($params,$class,$usernameField)
    {
        /* @var $query \yii\db\ActiveQuery */
        $query = $class::find();
        
        $result = new ActiveDataProvider([
            'query'=>$query
        ]);
        
        if(!($this->load($params) && $this->validate()))
            return $result;
        
        $query->andFilterWhere(['like',$usernameField,$this->username]);
        
        return $result;
    }
}
