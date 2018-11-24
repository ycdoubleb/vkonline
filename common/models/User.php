<?php

namespace common\models;

use common\models\vk\Customer;
use common\models\vk\UserBrand;
use common\utils\SecurityUtil;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Exception;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property string $id
 * @property string $customer_id            所属客户id
 * @property string $username               用户名
 * @property string $nickname               昵称或者真实名称
 * @property integer $type                  用户类型：1散户 2企业用户
 * @property string $password_hash          密码
 * @property string $password_reset_token   密码重置口令
 * @property int $sex                       姓别：0保密 1男 2女
 * @property string $phone                  电话
 * @property string $email                  邮箱
 * @property string $avatar                 头像
 * @property int $status                    状态：0 停用 10启用
 * @property bigint $max_store              最大存储空间（最小单位为B）
 * @property string $des                    简介
 * @property string $auth_key               认证
 * @property int $is_official 是否为官网资源：0否 1是
 * @property string $access_token           访问令牌
 * @property string $access_token_expire_time           访问令牌到期时间
 * @property string $created_at             创建时间
 * @property string $updated_at             更新时间
 * @property string $password write-only password
 * 
 * @property Customer $customer 客户
 */
class User extends BaseUser implements IdentityInterface {

    public $byte;
    
    //自由用户
    const TYPE_FREE = 1;
    //团体用户
    const TYPE_GROUP = 2;
    

    /** 空间大小 MB */
    const MBYTE = 1024 * 1024;
    /** 空间大小 GB */
    const GBYTE = 1024 * 1024 * 1024;
    
    /**
     * 账号
     * @var array 
     */
    public static $statusIs = [
        self::STATUS_STOP => '停用',
        self::STATUS_ACTIVE => '启用',
    ];
    
    /**
     * 用户类型map
     * @var array 
     */
    public static $typeNames = [
        self::TYPE_FREE => '自由用户',
        self::TYPE_GROUP => '团体用户',
    ];
    
    /**
     * 限定最大空间
     * @var array 
     */
    public static $byteName = [
         self::MBYTE => 'MB',    
         self::GBYTE => 'GB',    
    ];
    
    public function scenarios() {
        return [
            self::SCENARIO_CREATE =>
            ['customer_id', 'username', 'nickname', 'sex', 'email', 'password_hash', 'password2', 'phone', 'avatar', 'max_store', 'des', 'is_official', 'byte'],
            self::SCENARIO_UPDATE =>
            ['customer_id', 'username', 'nickname', 'sex', 'email', 'password_hash', 'password2', 'phone', 'avatar', 'max_store', 'des', 'is_official', 'byte'],
            self::SCENARIO_DEFAULT => 
            ['customer_id', 'username', 'nickname', 'sex', 'email', 'password_hash', 'password2', 'phone', 'avatar', 'max_store', 'des', 'is_official', 'byte'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName() {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['password_hash', 'password2'], 'required', 'on' => [self::SCENARIO_CREATE]],
            [['username', 'nickname', 'phone'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['username'], 'string', 'max' => 36, 'on' => [self::SCENARIO_CREATE]],
            [['username'], 'checkUsername', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['id', 'username'], 'unique'],
            [['password_hash'], 'string', 'min' => 6, 'max' => 64],
            [['created_at', 'updated_at', 'is_official', 'sex', 'type'], 'integer'],
            [['des'], 'string'],
            [['customer_id', 'id', 'auth_key'], 'string', 'max' => 32],
            [['username', 'nickname'], 'string', 'max' => 50],
            [['phone'], 'string', 'min' => 11, 'max' => 50],
//            [['password_hash'], 'string', 'max' => 64],
            [['password_reset_token', 'email', 'avatar'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 2],
            [['email'], 'email'],
            [['avatar'], 'image'],
            [['password2'], 'compare', 'compareAttribute' => 'password_hash'],
            [['avatar'], 'file', 'extensions' => 'jpg, png', 'mimeTypes' => 'image/jpeg, image/png'],
            [['max_store'], 'checkMaxStore', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]]
        ];
    }
    
    /**
     * 检查用户名是否为数字或字母及其组合
     * @param string $attribute username
     * @param string $params
     * @return boolean
     */
    public function checkUsername($attribute, $params)
    {
        $regex = '/[\x7f-\xff]/';
        if(preg_match($regex, $this->getAttribute($attribute))) {
            $this->addError($attribute, "用户名不能包含中文！"); 
            return false;
        } else {
            return true;
        }
    }

    /**
     * 检查用户的存储空间是否超过限制
     * @param bigint $attribute     max_store
     * @param string $params
     * @return boolean
     */
    public function checkMaxStore($attribute, $params)
    {
        if($this->type == self::TYPE_GROUP){  //团体用户才需要检测
            $format = $this->getAttribute($attribute) * $this->byte;
            $totalSize = Customer::findOne($this->customer_id);             //客户所拥有的存储空间

            if((string)$format > (string)$totalSize->good->data){
                $this->addError($attribute, "用户的存储空间大于客户所拥有的存储空间！");  
                return false;  
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return parent::attributeLabels() + [
            'customer_id' => Yii::t('app', '{The}{Customer}',['The' => Yii::t('app', 'The'),'Customer' => Yii::t('app', 'Customer'),]),
            'type' => Yii::t('app', 'Type'),
            'max_store' => Yii::t('app', '{Storage}{Space}',['Storage' => Yii::t('app', 'Storage'),'Space' => Yii::t('app', 'Space'),]),
            'is_official' => Yii::t('app', 'Is Official'), 
        ];
    }
    
    /**
     * 关联获取所属客户
     * @return ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(Customer::class, ['id' => 'customer_id']);
    }
    
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            //设置是否属于官网账号/企业用户or散户
            if($this->customer_id != null){
                if($this->customer_id == 0){
                    $this->is_official = 0;
                    $this->type = $this->type == 1 ? 1 : 2;        //企业用户
                } else {
                    $isOfficial = Customer::findOne(['id' => $this->customer_id]);
                    $this->is_official = $isOfficial->is_official;
                    $this->type = 2;        //企业用户
                }
            } else {
                $this->type = 1;        //散户
                $this->is_official = 0; //非官网用户
            }
            
            if ($this->scenario == self::SCENARIO_CREATE) {
                $this->max_store = $this->max_store * $this->byte;
            }else if ($this->scenario == self::SCENARIO_UPDATE) {
                if($this->max_store == $this->getOldAttribute('max_store')){
                    $this->max_store = $this->getOldAttribute('max_store');
                }else{
                    $this->max_store = $this->max_store * $this->byte;
                }
            }
            return true;
        }
        return false;
    }
    
    /**
     * 保存完成
     * @param bool $insert                  是否为插入数据
     * @param array $changedAttributes      更改的字段
     */
    public function afterSave($insert, $changedAttributes) {
        //保存后同步到 res.studying8.com 站点
        $url = Yii::$app->params['res']['host'] . Yii::$app->params['res']['synchronization_user_action'];
        $curl = new Curl();
        $curl->setPostParams([
            'encrypt' => SecurityUtil::encryption(['User' => $this->toArray(['id', 'username', 'nickname', 'password_hash', "sex", "phone", "email", "avatar", "status", "des"])]),
        ]);

        try{
            $response = $curl->post($url, false);
        } catch (Exception $ex) {
            $response['success'] = false;
            $response['data'] = ['msg' => $ex->getMessage()];
        }
        
        if ($response['success'] && $response['data']['code'] == "0") {
            Yii::info("同步用户 {$this->id} 成功！",__FUNCTION__);
        } else {
            Yii::info("同步用户 {$this->id} 失败！\n原因：{$response['data']['msg']}",__FUNCTION__);
        }
        
        parent::afterSave($insert, $changedAttributes);
    }
    
    /**
     * 查询用户绑定的品牌
     * @param string $user_id
     * @return type
     */
    public static function getUserBrand($user_id)
    {
        $userBrand = (new Query())
                ->select([
                    'UserBrand.id', 'UserBrand.user_id', 'UserBrand.brand_id',
                    'Customer.logo', 'Customer.name'
                ])
                ->from(['UserBrand' => UserBrand::tableName()])
                ->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = UserBrand.brand_id')
                ->where(['UserBrand.user_id' => $user_id, 'UserBrand.is_del' => 0])
                ->all();
        
        $moduleId = Yii::$app->controller->module->id;   //当前模块ID
        if($moduleId != 'frontend_admin'){    //后台不需排序
            //当前品牌排在最前面
            uasort($userBrand, function ($x, $y) {
                if ($x['brand_id'] === Yii::$app->user->identity->customer_id) {
                    return -1;
                } else {
                    return 1;
                }
            });
        }
        return $userBrand;
    }
    
}
