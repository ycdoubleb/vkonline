<?php

namespace common\modules\webuploader\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%uploadfile_chunk}}".
 *
 * @property string $chunk_id
 * @property string $file_id
 * @property string $chunk_path
 * @property string $chunk_index
 * @property integer $created_at 创建时间
 * @property integer $updated_at 更新时间
 */
class UploadfileChunk extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%uploadfile_chunk}}';
    }
    
    public function behaviors() {
        return [TimestampBehavior::class];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['chunk_id'], 'required'],
            [['chunk_index'], 'integer'],
            [['chunk_id', 'file_id'], 'string', 'max' => 32],
            [['chunk_path'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'chunk_id' => Yii::t('app', 'Chunk ID'),
            'file_id' => Yii::t('app', 'File ID'),
            'chunk_path' => Yii::t('app', 'Chunk Path'),
            'chunk_index' => Yii::t('app', 'Chunk Index'),
        ];
    }
}
