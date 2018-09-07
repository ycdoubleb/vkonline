<?php

namespace common\models\vk;

use common\modules\webuploader\models\Uploadfile;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%course_attachment}}".
 *
 * @property string $id
 * @property string $course_id 课程ID
 * @property string $file_id 附件文件ID
 * @property int $is_del 是否删除：0否 1是
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 * 
 * @property Course $course     获取课程
 * @property Uploadfile $uploadfile     获取上传的附件
 */
class CourseAttachment extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%course_attachment}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors() {
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
            [['created_at', 'updated_at'], 'integer'],
            [['course_id', 'file_id'], 'string', 'max' => 32],
            [['is_del'], 'string', 'max' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'course_id' => Yii::t('app', 'Course ID'),
            'file_id' => Yii::t('app', 'File ID'),
            'is_del' => Yii::t('app', 'Is Del'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * @return ActiveQuery
     */
    public function getCourse()
    {
        return $this->hasOne(Course::class, ['id' => 'course_id']);
    }
    
    /**
     * @return ActiveQuery
     */
    public function getUploadfile()
    {
        return $this->hasOne(Uploadfile::class, ['id' => 'file_id']);
    }
}
