<?php

namespace common\models\vk;

use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%customer_watermark}}".
 *
 * @property string $id
 * @property string $customer_id 品牌ID
 * @property string $file_id 实体文件ID
 * @property int $type 水印类型：1图片 2文字（预留）
 * @property string $name 水印名称
 * @property string $width 水印宽
 * @property string $height 水印高
 * @property string $dx 水平偏移位置
 * @property string $dy 垂直偏移位置
 * @property string $refer_pos 水印的位置，值范围TopRight、TopLeft、BottomRight、BottomLeft
 * @property int $is_del 是否删除：0否 1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property Uploadfile $file 实体文件
 */
class CustomerWatermark extends ActiveRecord {

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%customer_watermark}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['id'], 'required'],
            [['id', 'type', 'is_del', 'created_at', 'updated_at'], 'integer'],
            [['width', 'height', 'dx', 'dy'], 'number'],
            [['customer_id', 'file_id'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 50],
            [['refer_pos'], 'string', 'max' => 20],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', 'Customer ID'),
            'type' => Yii::t('app', 'Type'),
            'name' => Yii::t('app', 'Name'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
            'dx' => Yii::t('app', 'Dx'),
            'dy' => Yii::t('app', 'Dy'),
            'refer_pos' => Yii::t('app', 'Refer Pos'),
            'is_del' => Yii::t('app', 'Is Del'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * 实体文件
     * @return ActiveQuery Description
     */
    public function getFile(){
        return $this->hasOne(Uploadfile::class, ['id' => 'file_id']);
    }

    /**
     * 按条件探索转码水印配置
     * 
     * @return array [[InputFile,Dx,Dy,Width,Height,ReferPos],[]] 
     */
    public static function findAllForMts($condition) {
        /* @var $cw CustomerWatermark */
        $result = self::find($condition)->with('file')->all();
        $cws = [];
        foreach ($result as $cw) {
            $cw_t [] = [
                'InputFile' => [
                    'Object' => urldecode($cw->file->oss_key),      //水印输入文件名
                ],
                'Dx' => $cw->dx,                                    //水平偏移
                'Dy' => $cw->dy,                                    //垂直偏移
                'ReferPos' => $cw->refer_pos,                       //位置
            ];
            if($cw->width != 0 && $cw->height != 0){
                $cw_t['Width'] = $cw->width;        //宽;
                $cw_t['Height'] = $cw->height;      //高
            }
            
            $cws []= $cw_t;
        }
        
        return $cws;
    }

}
