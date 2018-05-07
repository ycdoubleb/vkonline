<?php

namespace common\models\vk;

use Yii;

/**
 * This is the model class for table "{{%customer_category}}".
 *
 * @property string $id
 * @property string $customer_id 客户ID
 * @property string $category_id 分类ID
 * @property int $is_del 是否删除：0否 1是
 * @property string $created_by 创建人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class CustomerCategory extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'is_del' , 'created_at', 'updated_at'], 'integer'],
            [['customer_id', 'created_by'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'category_id' => Yii::t('app', 'Category ID'),
            'is_del' => Yii::t('app', 'Is Del'),
            'created_by' => Yii::t('app', 'Created By'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
