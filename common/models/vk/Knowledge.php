<?php

namespace common\models\vk;

use common\models\User;
use common\utils\DateUtil;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%knowledge}}".
 *
 * @property string $id
 * @property string $node_id 环节ID
 * @property int $type 知识点类型：1视频知识点 2其它
 * @property string $name 知识点名称
 * @property string $des 知识点简介
 * @property string $data 资源自定义数据
 * @property string $zan_count 赞数
 * @property string $favorite_count 收藏数
 * @property int $is_del 是否删除：0否 1是
 * @property int $has_resource 是否已经关联资源：0否 1是
 * @property int $sort_order 排序
 * @property string $created_by 创建人ID
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @property User $createdBy 获取创建者
 * @property CourseNode $node   获取节点
 * @property KnowledgeVideo $knowledgeVideo 获取视频资源     
 * @property KnowledgeVideo[] $knowledgeVideos
 */
class Knowledge extends ActiveRecord
{
    /** video资源 */
    const TYPE_VIDEO_RESOURCE = 1;
    /** html资源 */
    const TYPE_HTML_RESOURCE = 2;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%knowledge}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['type', 'zan_count', 'favorite_count', 'is_del', 'has_resource', 'sort_order', 'created_at', 'updated_at'], 'integer'],
            [['des', 'data'], 'string'],
            [['id', 'node_id', 'created_by'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['id'], 'unique'],
            [['node_id'], 'exist', 'skipOnError' => true, 'targetClass' => CourseNode::className(), 'targetAttribute' => ['node_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'node_id' => Yii::t('app', 'Node ID'),
            'type' => Yii::t('app', 'Type'),
            'name' => Yii::t('app', 'Name'),
            'des' => Yii::t('app', 'Des'),
            'data' => Yii::t('app', 'Data'),
            'zan_count' => Yii::t('app', 'Zan Count'),
            'favorite_count' => Yii::t('app', 'Favorite Count'),
            'is_del' => Yii::t('app', 'Is Del'),
            'has_resource' => Yii::t('app', 'Has Resource'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    public function beforeSave($insert) 
    {
        if (parent::beforeSave($insert)) {
            if (!$this->id) {
                $this->id = md5(time() . rand(1, 99999999));
            }
            $knowledges = self::find()->select(['sort_order'])
                ->where(['node_id' => $this->node_id, 'is_del' => 0])
                ->orderBy(['sort_order' => SORT_DESC])->one();
            //设置顺序
            if($this->isNewRecord && !empty($knowledges)){
                $this->sort_order = $knowledges->sort_order + 1;
            }
            $this->des = Html::encode($this->des);
            
            return true;
        }
        
        return false;
    }
    
    public function afterFind()
    {
        $this->des = Html::decode($this->des);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getNode()
    {
        return $this->hasOne(CourseNode::className(), ['id' => 'node_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getKnowledgeVideo()
    {
        return $this->hasOne(KnowledgeVideo::className(), ['knowledge_id' => 'id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getKnowledgeVideos()
    {
        return $this->hasMany(KnowledgeVideo::className(), ['knowledge_id' => 'id']);
    }
    
    /**
     * 获取知识点资源信息
     * $resInfo = [
     *   'id' => 资源id, 
     *   'data' => 资源数据
     * ]
     * @param string $keyName    键名
     * @return string
     */
    public static function getKnowledgeResourceInfo($id, $keyName)
    {
        $resInfo = ['res_id' => '', 'data' => ''];
        $res = (new Query())->select([
            'Knowledge.type', 'Knowledge.data', 'Knowledge.has_resource',
            'KnowledgeVideo.video_id', 
        ])->from(['Knowledge' => Knowledge::tableName()]);
        $res->leftJoin(['KnowledgeVideo' => KnowledgeVideo::tableName()], 'KnowledgeVideo.knowledge_id = Knowledge.id');
        $res->where(['Knowledge.id' => $id]);
        $resResults = $res->one();
        if($resResults['has_resource']){
            switch ($resResults['type']){
                case self::TYPE_VIDEO_RESOURCE:
                    $res_id = $resResults['video_id'];
                    $data = DateUtil::intToTime($resResults['data']);
                    break;
                case self::TYPE_HTML_RESOURCE:
                    $res_id = null;
                    $data = null;
                    break;
                default :
                    $res_id = null;
                    $data = null;
                    break;
            }
            $resInfo = [
                'res_id' => $res_id,
                'data' => $data,
            ];
        }
        
        return $resInfo[$keyName];
    }
}
