<?php

namespace common\models\vk;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%aliyun_mts_service}}".
 *
 * @property string $id
 * @property string $request_id 请求ID
 * @property string $job_id 任务ID
 * @property string $video_id 视频ID
 * @property int $is_finish 是否完成：0否 1是
 * @property int $is_del 是否删除：0否 1是
 * @property int $level 视频质量等级：1~9
 * @property int $result 结果：0失败 1成功
 * @property string $created_by 任务创建人
 * @property string $created_at 创建时间
 * @property string $updated_at 更前时间
 */
class AliyunMtsService extends ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%aliyun_mts_service}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['is_finish', 'is_del', 'result', 'level', 'created_at', 'updated_at'], 'integer'],
            [['request_id'], 'string', 'max' => 255],
            [['job_id', 'video_id', 'created_by'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'request_id' => Yii::t('app', 'Request ID'),
            'job_id' => Yii::t('app', 'Job ID'),
            'video_id' => Yii::t('app', 'Video ID'),
            'is_finish' => Yii::t('app', 'Is Finish'),
            'is_del' => Yii::t('app', 'Is Del'),
            'result' => Yii::t('app', 'Result'),
            'level' => Yii::t('app', 'Level'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * 获取已完成转码文件等级
     * 
     * @param string $video_id      视频ID
     * @return array [0,1,2,3]
     */
    public static function getFinishLevel($video_id) {
        $hasDoneLevels = AliyunMtsService::find()
                        ->select('level')
                        ->from(AliyunMtsService::tableName())
                        ->where([
                            'video_id' => $video_id,
                            'is_del' => 0,
                            'is_finish' => 1,
                            'result' => 1,
                        ])->column();
        return $hasDoneLevels;
    }

    /**
     * 从反馈里批量添加记录
     * 
     * @param string $video_id      视频ID
     * @param Object $response      反馈数据
     */
    public static function batchInsertServiceForMts($video_id, $response) {
        //保存调用记录，用于完成核实
        $rows = [];
        $time = time();
        $request_id = $result['response']->RequestId;
        foreach ($result['response']->JobResultList->JobResult as $JobResult) {
            $rows [] = [
                $request_id,
                $JobResult->Job->JobId,
                $video->id,
                $JobResult->Success ? 0 : 1,
                \Yii::$app->user->id,
                $time, $time
            ];
        }
        \Yii::$app->db->createCommand()->batchInsert(AliyunMtsService::tableName(), ['request_id', 'job_id', 'video_id', 'is_finish', 'created_by', 'created_at', 'updated_at'], $rows)->execute();
    }

}
