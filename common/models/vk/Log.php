<?php

namespace common\models\vk;

use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%log}}".
 *
 * @property string $id
 * @property int $level 等级：1信息 2警告 3错误
 * @property string $category 分类
 * @property string $title 标题
 * @property string $from 日志来源
 * @property string $content 内容，JSON格式
 * @property string $created_by 创建人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property User $createdBy 获取操作人
 */
class Log extends ActiveRecord
{
    /* 级别-信息 */
    const LEVEL_INFO = 1;
    /* 级别-警告 */
    const LVEVL_WARNING = 2;
    /* 级别-错误 */
    const LVEVL_ERROR = 3;
    
    /**
     * 级别
     * @var array 
     */
    public static $levelMap = [
        self::LEVEL_INFO => '信息',
        self::LVEVL_WARNING => '警告',
        self::LVEVL_ERROR => '错误'
    ];
    
    /**
     * 标题
     * @var array 
     */
    public static $titleMap = [
        'create' => '新增',
        'update' => '修改',
        'delete' => '删除',
        'move' => '移动',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%log}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() 
    {
        return [
            TimestampBehavior::class
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['level', 'created_at', 'updated_at'], 'integer'],
//            [['content'], 'string'],
            [['category'], 'string', 'max' => 20],
            [['title', 'from'], 'string', 'max' => 255],
            [['created_by'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'level' => Yii::t('app', 'Level'),
            'category' => Yii::t('app', 'Category'),
            'title' => Yii::t('app', 'Title'),
            'from' => Yii::t('app', 'From'),
            'content' => Yii::t('app', 'Content'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            $this->content = Html::encode($this->content);
            return true;
        }
        return false;
    }
    
    public function afterFind() {
        $this->content = Html::decode($this->content, true);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
    
    /**
     * 保存日志
     * @param string $category  分类
     * @param string $load_dom  加载渲染的模板（"@frontend/modules/admin_center/views/log/{$load_dom}_dom"）
     * @param array $data   数据
     * @param int $level    级别
     * @param string $from  来源
     */
    public static function savaLog($category, $load_dom, $data, $level = 1, $from = null){
        /** 开启事务 */
        $trans = \Yii::$app->db->beginTransaction();
        try
        {  
            $model = new Log([
                'level' => $level,
                'category' => $category,
                'title' => self::$titleMap[\Yii::$app->controller->action->id],
                'from' => $from == null ? '系统' : $from,
                'content' => \Yii::$app->controller->renderAjax("@frontend/modules/admin_center/views/log/{$load_dom}_dom", $data),
                'created_by' => \Yii::$app->user->id,
            ]);
             
            if($model->save()){
                $trans->commit();  //提交事务
            }else{
                Yii::$app->getSession()->setFlash('error','保存失败::'. implode('；', $model->getErrorSummary(true)));
            }
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            Yii::$app->getSession()->setFlash('error','保存失败::'.$ex->getMessage());
        }
    }
}
