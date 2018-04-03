<?php

namespace common\models\vk;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%good}}".
 *
 * @property string $id
 * @property string $name       套餐名称
 * @property int $type          类型：1容量套餐
 * @property string $data       套餐数据：1套餐包括的空间大小B
 * @property string $price      价格：元/月
 * @property string $des        描述
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Good extends ActiveRecord
{
    /** 套餐类型-容量套餐 */
    const SIZE_TYPE = 1;
    
    /**
     * 套餐类型
     * @var array 
     */
    public static $sizeType = [
        self::SIZE_TYPE => '容量套餐',
    ];
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%good}}';
    }

    /**
     * @inheritdoc
     */    
    public function behaviors() 
    {
        return [
            TimestampBehavior::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['price', 'created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['type'], 'string', 'max' => 1],
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
            'type' => Yii::t('app', 'Type'),
            'data' => Yii::t('app', 'Data'),
            'price' => Yii::t('app', 'Price'),
            'des' => Yii::t('app', 'Des'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
}
