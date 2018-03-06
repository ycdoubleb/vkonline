<?php

namespace common\modules\webuploader\models;

use Yii;

/**
 * This is the model class for table "{{%uploadfile_chunk}}".
 *
 * @property string $chunk_id
 * @property string $file_id
 * @property string $chunk_path
 * @property string $chunk_index
 */
class UploadfileChunk extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%uploadfile_chunk}}';
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
