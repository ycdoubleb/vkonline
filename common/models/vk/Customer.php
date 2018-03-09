<?php

namespace common\models\vk;

use common\models\AdminUser;
use common\models\Region;
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
 * @property string $name 名称
 * @property string $domain 域名，不带http
 * @property string $logo logo
 * @property int $status 状态：0停用 1试用 10 正常
 * @property string $des 描述
 * @property string $expire_time 到期时间
 * @property string $renew_time 上次续费时间
 * @property string $good_id 套餐ID
 * @property string $invite_code 邀请码
 * @property string $province 省
 * @property string $city 市
 * @property string $district 区
 * @property string $twon 镇
 * @property string $address 详细地址
 * @property string $location 位置
 * @property string $created_by 创建人
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
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
            TimestampBehavior::className()
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
               'status', 'created_at', 'updated_at'], 'integer'],
            [['location'], 'string'],
            [['id', 'created_by'], 'string', 'max' => 32],
            [['name', 'domain', 'logo', 'des', 'address'], 'string', 'max' => 255],
//            [['status'], 'string', 'max' => 1],
            [['invite_code'], 'string', 'max' => 6],
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
            'domain' => Yii::t('app', 'Domain'),
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
        return $this->hasOne(CustomerAdmin::className(), ['customer_id' => 'id']);
    }
    
    /**
     * 关联获取创建人
     * @return ActiveQuery
     */
    public function getUserName()
    {
        return $this->hasOne(AdminUser::className(), ['id' => 'created_by']);
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
            //拿到经纬度并处理
            $location = ArrayHelper::getValue(Yii::$app->request->post(), 'Customer.location');
            if($location != null){
                $this->location = new Expression("GeomFromText('POINT($location)')");
            }
            //Logo上传
            $upload = UploadedFile::getInstance($this, 'logo');
            $logo_name = md5(time());
            if($upload !== null){
                $string = $upload->name;
                $array = explode('.', $string);
                //获取后缀名，默认名为.jpg
                $ext = count($array) == 0 ? 'jpg' : $array[count($array)-1];
                $uploadpath = $this->fileExists(Yii::getAlias('@frontend/web/resources/customer/'));
                $upload->saveAs($uploadpath . $logo_name . '.' . $ext) ;
                $this->logo = '/resources/customer/' . $logo_name . '.' . $ext . '?r=' . rand(0, 10000);
            }
            if(trim($this->logo) == ''){
                $this->logo = $this->getOldAttribute('logo');
            }
            return true;
        }
        return false;
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
