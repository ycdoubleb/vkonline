<?php

namespace common\modules\rbac\models;

use Yii;

/**
 * This is the model class for table "{{%auth_group}}".
 *
 * @property integer $id
 * @property string $name
 * @property string $app
 * @property integer $sort_order
 */
class AuthGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%auth_group}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sort_order'], 'integer'],
            [['name', 'app'], 'string', 'max' => 255],
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
            'app' => Yii::t('app', 'App'),
            'sort_order' => Yii::t('app', 'Sort Order'),
        ];
    }
}
