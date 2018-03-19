<?php

namespace common\modules\webuploader\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%uploadfile}}".
 *
 * @property string $id                 文件ID
 * @property string $name   `           文件名
 * @property string $path               文件路径
 * @property string $thumb_path         缩略图路径
 * @property string $app_id             应用ID
 * @property string $download_count     下载次数
 * @property integer $del_mark          即将删除标记：0未标记，1已标记
 * @property integer $is_del            是否已经删除标记：0未删除，1已删除
 * @property integer $is_fixed          是否为永久保存：0否，1是，设置后不会自动删除文件
 * @property string $created_by         上传人
 * @property string $deleted_by         删除人ID
 * @property string $deleted_at         删除时间
 * @property string $created_at         创建时间
 * @property string $created_at         创建时间
 * @property string $updated_at         更新时间
 * @property string $size               大小B
 */
class Uploadfile extends ActiveRecord
{
    /** 否 */
    const TYPE_NO_CHOICE = 0;
    /** 是 */
    const TYPE_YES_CHOICE = 1;
    
    /** 类型 */
    public static $TYPES = [
        self::TYPE_NO_CHOICE => '否',
        self::TYPE_YES_CHOICE => '是',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%uploadfile}}';
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
            [['id'], 'required'],
            [['download_count', 'del_mark', 'is_del', 'is_fixed', 'created_at','deleted_at', 'updated_at'], 'integer'],
            [['id', 'created_by', 'deleted_by'], 'string', 'max' => 32],
            [['name', 'path', 'thumb_path'], 'string', 'max' => 255],
            [['app_id'], 'string', 'max' => 50],
            [['del_mark', 'is_del', 'is_fixed'], 'integer'],
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
            'name' => Yii::t('app', 'Name'),
            'path' => Yii::t('app', 'Path'),
            'thumb_path' => Yii::t('app', 'Thumb Path'),
            'app_id' => Yii::t('app', 'App ID'),
            'download_count' => Yii::t('app', 'Download Count'),
            'del_mark' => Yii::t('app', 'Del Mark'),
            'is_del' => Yii::t('app', 'Is Del'),
            'is_fixed' => Yii::t('app', 'Is Fixed'),
            'created_by' => Yii::t('app', 'Created By'),
            'deleted_by' => Yii::t('app', 'Deleted By'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'size' => Yii::t('app', 'Size'),
        ];
    }
}
