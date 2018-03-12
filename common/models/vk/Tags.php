<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%tags}}".
 *
 * @property string $id
 * @property string $name 名称
 * @property string $ref_count 引用次数
 * @property string $des 描述
 */
class Tags extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%tags}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ref_count'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['des'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'ref_count' => Yii::t('app', 'Ref Count'),
            'des' => Yii::t('app', 'Des'),
        ];
    }
}
