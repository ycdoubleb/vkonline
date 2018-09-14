<?php

namespace common\models\vk;

use common\components\aliyuncs\Aliyun;
use common\models\AdminUser;
use common\models\Region;
use common\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%customer}}".
 *
 * @property string $id
 * @property string $name           名称
 * @property string $short_name     简称
 * @property string $domain         域名，不带http
 * @property string $logo           logo
 * @property int $status            状态：0停用 1试用 10 正常
 * @property string $des            描述
 * @property string $expire_time    到期时间
 * @property string $renew_time     上次续费时间
 * @property string $good_id        套餐ID
 * @property string $invite_code    邀请码
 * @property string $province       省
 * @property string $city           市
 * @property string $district       区
 * @property string $twon           镇
 * @property string $address        详细地址
 * @property string $location       位置
 * @property int $sort_order        排序
 * @property string $created_by     创建人
 * @property int $is_official       是否为官网资源：0否 1是
 * @property string $created_at     创建时间
 * @property string $updated_at     更新时间
 * 
 * @property CustomerAdmin $customer    获取客户的管理员
 * @property Good $good                 获取客户的套餐
 * @property AdminUser $gadminUser      获取客户的创建者
 */
class Customer extends ActiveRecord
{
    //停用的客户
    const STATUS_STOP = 0;
    //正常的客户
    const STATUS_ACTIVE = 10;
    
    public static $statusUser = [
        self::STATUS_STOP => '停用',
        self::STATUS_ACTIVE => '正常',
    ];

        /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%customer}}';
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
//            [['id'], 'required'],
            [['expire_time', 'renew_time', 'good_id', 'province', 'city', 'district', 'twon',
               'status', 'created_at', 'updated_at', 'is_official', 'sort_order'], 'integer'],
            [['location'], 'string'],
            [['id', 'created_by'], 'string', 'max' => 32],
            [['name', 'domain', 'logo', 'des', 'address'], 'string', 'max' => 255],
            [['short_name'], 'string', 'max' => 20],
//            [['status'], 'string', 'max' => 1],
            [['invite_code'], 'string', 'max' => 6],
            [['short_name'], 'string', 'max' => 50],
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
            'short_name' => Yii::t('app', 'Short Name'),
            'domain' => Yii::t('app', 'Domain'),
            'sort_order' => Yii::t('app', 'Sort Order'),
            'logo' => Yii::t('app', 'Logo'),
            'status' => Yii::t('app', 'Status'),
            'des' => Yii::t('app', 'Des'),
            'expire_time' => Yii::t('app', '{Expire}{Time}',[
                'Expire' => Yii::t('app', 'Expire'),
                'Time' => Yii::t('app', 'Time'),
            ]),
            'renew_time' => Yii::t('app', '{Renew}{Time}',[
                'Renew' => Yii::t('app', 'Renew'),
                'Time' => Yii::t('app', 'Time'),
            ]),
            'good_id' => Yii::t('app', 'Good ID'),
            'invite_code' => Yii::t('app', 'Invite Code'),
            'province' => Yii::t('app', 'Province'),
            'city' => Yii::t('app', 'City'),
            'district' => Yii::t('app', 'District'),
            'twon' => Yii::t('app', 'Twon'),
            'address' => Yii::t('app', 'Address'),
            'location' => Yii::t('app', 'Location'),
            'created_by' => Yii::t('app', 'Created By'),
            'is_official' => Yii::t('app', 'Is Official'), 
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    
    /**
     * 关联获取客户的管理员
     * @return ActiveQuery
     */
    public function getCustomerAdmin()
    {
        return $this->hasOne(CustomerAdmin::class, ['customer_id' => 'id']);
    }
    
    /**
     * 关联获取客户的套餐
     * @return ActiveQuery
     */
    public function getGood()
    {
        return $this->hasOne(Good::class, ['id' => 'good_id']);
    }
    
    /**
     * 关联获取开始结束时间
     * @return ActiveQuery
     */
    public function getStaEndTime()
    {
        return $this->hasOne(CustomerActLog::class, ['customer_id' => 'id'])->orderBy('id desc');
    }


    /**
     * 关联获取创建人
     * @return ActiveQuery
     */
    public function getUserName()
    {
        return $this->hasOne(AdminUser::class, ['id' => 'created_by']);
    }
    
    /**
     * 
     * @param type $insert
     * @return boolean
     */
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)){
            //设置ID
            if (!$this->id) {
                $this->id = md5(time() . rand(1, 99999999));
            }
            //设置创建人
            if(!$this->created_by){
                $this->created_by = Yii::$app->user->id;
            }
            //设置客户简称
            if(!$this->short_name){
                $this->short_name = $this->name;
            }
            //设置邀请码
            if(!$this->invite_code){
                $str='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';  
                $randStr = str_shuffle($str);       //打乱字符串  
                $this->invite_code = substr($randStr,0,6); //substr(string,start,length);返回字符串的一部分 
            }
            //创建时默认设置为停用状态
            if($this->isNewRecord){
                $this->status = self::STATUS_STOP;
            }
            //更新成员的‘is_official’状态
            if(!$this->isNewRecord){
                User::updateAll(['is_official' => $this->is_official], ['customer_id' => $this->id]);
            }
            //拿到经纬度并处理
            $location = ArrayHelper::getValue(Yii::$app->request->post(), 'Customer.location');
            if($location != null){
                $this->location = new Expression("GeomFromText('POINT($location)')");
            }
            //Logo上传
            $upload = UploadedFile::getInstance($this, 'logo');
            if($upload !== null){
                //获取后缀名，默认为 png 
                $ext = pathinfo($upload->name,PATHINFO_EXTENSION);
                $img_path = "upload/customer/{$this->id}.{$ext}";
                //上传到阿里云
                Aliyun::getOss()->multiuploadFile($img_path, $upload->tempName);
                $this->logo = $img_path . '?rand=' . rand(0, 9999); 
            }
            if(trim($this->logo) == ''){
                $this->logo = $this->getOldAttribute('logo');
            }
            return true;
        }
        return false;
    }
        
    public function afterFind()
    {
        $this->logo = Aliyun::absolutePath(!empty($this->logo) ? $this->logo : 'static/imgs/notfound.png');
    }
    
    /**
     * 检查目标路径是否存在，不存即创建目标
     * @param type $uploadpath  目标路径
     * @return type
     */
    private function fileExists($uploadpath){
        if(!file_exists($uploadpath)){
            mkdir($uploadpath);
        }
        return $uploadpath;
    }
            
    /**
     * 获取省/市/区/镇
     * @param integer $parent_id
     * @return array
     */
    public function getCityList($parent_id)
    {  
        $model = Region::findAll(['parent_id' => $parent_id]);  
        return ArrayHelper::map($model, 'id', 'name');  
    }
    
}
