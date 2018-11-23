<?php

namespace common\modules\webuploader\models;

use common\components\aliyuncs\Aliyun;
use common\models\User;
use common\utils\StringUtil;
use OSS\Core\OssException;
use Yii;
use yii\base\Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "{{%uploadfile}}".
 *
 * @property string $id 文件ID
 * @property string $customer_id 品牌ID
 * @property string $name 文件名
 * @property string $path 文件路径
 * @property string $thumb_path 缩略图路径
 * @property string $app_id 应用ID
 * @property string $download_count 下载次数
 * @property int $del_mark 即将删除标记：0未标记，1已标记
 * @property string $size 大小B
 * @property int $is_del 是否已经删除标记：0未删除，1已删除
 * @property int $is_fixed 是否为永久保存：0否，1是，设置后不会自动删除文件
 * @property int $is_link 是否为外链：0否 1是
 * @property string $width 宽度
 * @property string $height 高度
 * @property int $level 视频质量：1=480P 2=720P 3=1080P
 * @property string $duration 时长
 * @property string $bitrate 码率
 * @property string $oss_key        oss名称/文件名
 * @property int $oss_upload_status        上传状态：0未上传，1上传中，2已上传
 * @property string $created_by 上传人
 * @property string $deleted_by 删除人ID
 * @property string $deleted_at 删除时间
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class Uploadfile extends ActiveRecord {

    /** 否 */
    const TYPE_NO_CHOICE = 0;

    /** 是 */
    const TYPE_YES_CHOICE = 1;

    /* 未上传 */
    const OSS_UPLOAD_STATUS_NO = 0;
    /* 已上传 */
    const OSS_UPLOAD_STATUS_YES = 1;

    /** 类型 */
    public static $TYPES = [
        self::TYPE_NO_CHOICE => '否',
        self::TYPE_YES_CHOICE => '是',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%uploadfile}}';
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
    public function rules() {
        return [
            [['id'], 'required'],
            [['download_count', 'del_mark', 'size', 'is_del', 'is_fixed', 'is_link', 'width', 'height', 'level', 'bitrate',
            'oss_upload_status', 'deleted_at', 'created_at', 'updated_at'], 'integer'],
            [['duration'], 'number'],
            [['id', 'customer_id', 'created_by', 'deleted_by'], 'string', 'max' => 32],
            [['name', 'path', 'thumb_path', 'oss_key'], 'string', 'max' => 255],
            [['app_id'], 'string', 'max' => 50],
            [['id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'path' => Yii::t('app', 'Path'),
            'thumb_path' => Yii::t('app', 'Thumb Path'),
            'app_id' => Yii::t('app', 'App ID'),
            'download_count' => Yii::t('app', 'Download Count'),
            'del_mark' => Yii::t('app', 'Del Mark'),
            'size' => Yii::t('app', 'Size'),
            'is_del' => Yii::t('app', 'Is Del'),
            'is_fixed' => Yii::t('app', 'Is Fixed'),
            'is_link' => Yii::t('app', 'Is Link'),
            'width' => Yii::t('app', 'Width'),
            'height' => Yii::t('app', 'Height'),
            'level' => Yii::t('app', 'Level'),
            'duration' => Yii::t('app', 'Duration'),
            'bitrate' => Yii::t('app', 'Bitrate'),
            'oss_key' => Yii::t('app', 'OSS Key'),
            'oss_upload_status' => Yii::t('app', 'OSS Upload Status'),
            'created_by' => Yii::t('app', 'Created By'),
            'deleted_by' => Yii::t('app', 'Deleted By'),
            'deleted_at' => Yii::t('app', 'Deleted At'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * 获取文件后缀
     * @return string 后缀名
     */
    public function getExt() {
        $thisinfo = pathinfo($this->path);
        return strtolower($thisinfo['extension']);
    }

    /**
     * 上传到阿里云
     * 
     * @param string $key           文件名称，默认=customer_id/user_id/file_id.[ext]
     * @return array [success,msg]
     */
    public function uploadOSS($key = null) {
        $thumb_key = '';
        if ($key == null) {
            /* @var $user User */
            $user = User::findOne(['id' => $this->created_by]);
            if (!$user) {
                return ['success' => false, 'msg' => '文件缺少创建人数据！'];
            } else if ($user->customer_id == null) {
                return ['success' => false, 'msg' => '用户未加入任何品牌！'];
            } else if (!file_exists($this->path)) {
                return ['success' => false, 'msg' => '找不到文件！'];
            }
            //生成文件名，当oss_key不为空时，将使用oss_key作为文件名
            $filename = "brand/{$user->customer_id}/{$user->id}/". pathinfo($this->path,PATHINFO_BASENAME);
            //设置文件名
            $object_key = $this->oss_key == '' ? $filename : $this->oss_key;
        } else {
            $object_key = $key;
        }
        $thumb_key = pathinfo($object_key, PATHINFO_DIRNAME) . '_thumb.jpg';

        try {
            //上传文件
            Aliyun::getOss()->multiuploadFile($object_key, $this->path);
            //上传缩略图
            if ($this->thumb_path != '') {
                Aliyun::getOss()->multiuploadFile($thumb_key, $this->thumb_path);
                @unlink($this->thumb_path);
                $this->thumb_path = $thumb_key . "?rand=" . rand(0, 999);
            }
            //更新数据
            $this->oss_upload_status = Uploadfile::OSS_UPLOAD_STATUS_YES;
            $this->oss_key = $object_key;

            $this->save(false, ['oss_upload_status', 'oss_key', 'thumb_path']);
            //删除本地文件
            @unlink($this->path);
            return ['success' => true];
        } catch (OssException $ex) {
            return ['success' => false, 'msg' => $ex->getMessage()];
        } catch (Exception $ex) {
            return ['success' => false, 'msg' => $ex->getMessage()];
        }
    }

    /**
     * 获取已上传的实体文件信息
     * @param string $fileId    文件id
     * @return ActiveQuery  ['id', 'name', 'path', 'thumb_path', 'size']
     */
    public static function getUploadfileByFileId($fileId) {
        //查询实体文件
        $uploadFile = (new Query())->select([
                    'Uploadfile.id', 'Uploadfile.name', 'Uploadfile.oss_key', 'Uploadfile.size'
                ])->from(['Uploadfile' => self::tableName()]);
        //条件查询
        $uploadFile->where([
            'Uploadfile.id' => $fileId,
            'Uploadfile.is_del' => 0
        ]);
        $file = $uploadFile->one();
        if (!empty($file)) {
            //重置path、thumb_path
            $file['path'] = $file['thumb_path'] = Aliyun::absolutePath($file['oss_key']);
            return [$file];
        } else {
            return [];
        }
    }

}
