<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%scheduled_task_log}}".
 *
 * @property string $id
 * @property integer $type                  类型
 * @property string $action                 执行动作eg:mconline/check-expire-file
 * @property integer $result                0/1 失败/成功
 * @property string $feedback               执行返馈
 * @property string $created_by             执行人，空系统
 * @property string $created_at
 * @property string $updated_at
 */
class ScheduledTaskLog extends ActiveRecord
{
    /*
     * 检查过期文件
     */
    const TYPE_MCONLINE_CHECK_EXPIRE_FILE = 1;
    
    /**
     * 检查文件大小上限
     */
    const TYPE_CHECK_MAX_FILE_LIMIT = 2;
    
    /**
     * 设置预约任务状态
     */
    const TYPE_SET_SCENEBOOK_STATUS = 3;
    
    /** 失败 */
    const TYPE_NO_SUCCESS = 0;
    /** 成功 */
    const TYPE_YES_SUCCESS = 1;

    public static $type = [
        self::TYPE_MCONLINE_CHECK_EXPIRE_FILE => '检查过期文件',
        self::TYPE_CHECK_MAX_FILE_LIMIT => '检查文件大小上限',
    ];
    
    public static $IsSuccess = [
        self::TYPE_NO_SUCCESS => '失败',
        self::TYPE_YES_SUCCESS => '成功',  
    ];

        /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%scheduled_task_log}}';
    }
    
    public function behaviors() {
        return [TimestampBehavior::className()];
    }
    
    public function afterFind() {
        if($this->result){
            $this->feedback = json_decode($this->feedback, true);
        }else{
            $this->feedback = $this->feedback;
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'result', 'created_at', 'updated_at'], 'integer'],
            [['feedback'], 'string'],
            [['action'], 'string', 'max' => 255],
            [['created_by'], 'string', 'max' => 36],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'type' => Yii::t('app', 'Type'),
            'action' => Yii::t('app', 'Action'),
            'result' => Yii::t('app', 'Result'),
            'feedback' => Yii::t('app', 'Feedback'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
