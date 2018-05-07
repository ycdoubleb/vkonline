<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%comment_praise}}".
 *
 * @property string $id
 * @property string $comment_id 评论ID
 * @property string $user_id 用户ID
 * @property int $result 评论结果：1赞 2踩
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CommentPraise extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%comment_praise}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['comment_id', 'created_at', 'updated_at' ,'result'], 'integer'],
            [['user_id'], 'string', 'max' => 32],
            [['comment_id', 'user_id'], 'unique', 'targetAttribute' => ['comment_id', 'user_id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'comment_id' => Yii::t('app', 'Comment ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'result' => Yii::t('app', 'Result'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
