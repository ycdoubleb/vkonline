<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%visit_log}}".
 *
 * @property string $id
 * @property string $item_id 课程，视频等ID
 * @property string $share_by 分享人ID
 * @property string $visit_ip 来访IP
 * @property string $visit_agent 客户端
 * @property int $is_pc 是否为PC：0否 1是
 * @property string $income 分享来源：wixin,qq,qzone,xlwb
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class VisitLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%visit_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'created_at', 'updated_at'], 'integer'],
            [['item_id', 'share_by'], 'string', 'max' => 32],
            [['visit_ip', 'visit_agent', 'income'], 'string', 'max' => 255],
            [['is_pc'], 'string', 'max' => 1],
            [['id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'item_id' => Yii::t('app', 'Item ID'),
            'share_by' => Yii::t('app', 'Share By'),
            'visit_ip' => Yii::t('app', 'Visit Ip'),
            'visit_agent' => Yii::t('app', 'Visit Agent'),
            'is_pc' => Yii::t('app', 'Is Pc'),
            'income' => Yii::t('app', 'Income'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
