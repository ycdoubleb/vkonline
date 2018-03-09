<?php

namespace common\models\helpcenter;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
/**
 * This is the model class for table "{{%post_appraise}}".
 *
 * @property string $id
 * @property string $post_id            文章ID
 * @property string $user_id            用户ID
 * @property int $result                评论结果：1赞2踩
 * @property string $created_at
 * @property string $updated_at
 */
class PostAppraise extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%post_appraise}}';
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors() 
    {
        return [
            TimestampBehavior::className()
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'created_at', 'updated_at'], 'integer'],
            [['user_id'], 'required'],
            [['user_id'], 'string', 'max' => 32],
            [['result'], 'string', 'max' => 2],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'post_id' => Yii::t('app', 'Post ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'result' => Yii::t('app', 'Result'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
