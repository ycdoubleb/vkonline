<?php

namespace common\models;

use common\models\vk\Customer;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\UploadedFile;

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
class User extends ActiveRecord implements IdentityInterface {

    public $byte;
    
    /** 创建场景 */
    const SCENARIO_CREATE = 'create';

    /** 更新场景 */
    const SCENARIO_UPDATE = 'update';
    //已停账号
    const STATUS_STOP = 0;
    //活动账号
    const STATUS_ACTIVE = 10;
    
    //自由用户
    const TYPE_FREE = 1;
    //团体用户
    const TYPE_GROUP = 2;
    

    /** 性别 保密 */
    const SEX_SECRECY = 0;
    /** 性别 男 */
    const SEX_MALE = 1;
    /** 性别 女 */
    const SEX_WOMAN = 2;
    
    /** 空间大小 MB */
    const MBYTE = 1024 * 1024;
    /** 空间大小 GB */
    const GBYTE = 1024 * 1024 * 1024;
    
    /**
     * 性别
     * @var array 
     */
    public static $sexName = [
        self::SEX_SECRECY => '保密',
        self::SEX_MALE => '男',
        self::SEX_WOMAN => '女',
    ];
    
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
    
    /* 重复密码验证 */
    public $password2;

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
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules() {
        return [
            [['password_hash', 'password2'], 'required', 'on' => [self::SCENARIO_CREATE]],
            [['username', 'nickname', 'phone'], 'required', 'on' => [self::SCENARIO_CREATE, self::SCENARIO_UPDATE]],
            [['username'], 'string', 'max' => 36, 'on' => [self::SCENARIO_CREATE]],
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
     * 检查用户的存储空间是否超过限制
     * @param bigint $attribute     max_store
     * @param string $params
     * @return boolean
     */
    public function checkMaxStore($attribute, $params)
    {
        $format = $this->getAttribute($attribute) * $this->byte;
        $totalSize = Customer::findOne($this->customer_id);             //客户所拥有的存储空间

        if((string)$format > (string)$totalSize->good->data){
            $this->addError($attribute, "用户的存储空间大于客户所拥有的存储空间！");  
            return false;  
        } else {
            return true;
        }
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'customer_id' => Yii::t('app', '{The}{Customer}',['The' => Yii::t('app', 'The'),'Customer' => Yii::t('app', 'Customer'),]),
            'username' => Yii::t('app', 'Account Number'),
            'nickname' => Yii::t('app', '{True}{Name}',['True' => Yii::t('app', 'True'),'Name' => Yii::t('app', 'Name'),]),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password2' => Yii::t('app', 'Password2'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'type' => Yii::t('app', 'Type'),
            'sex' => Yii::t('app', 'Sex'),
            'phone' => Yii::t('app', 'Phone'),
            'email' => Yii::t('app', 'Email'),
            'avatar' => Yii::t('app', 'Avatar'),
            'status' => Yii::t('app', 'Status'),
            'max_store' => Yii::t('app', '{Storage}{Space}',['Storage' => Yii::t('app', 'Storage'),'Space' => Yii::t('app', 'Space'),]),
            'des' => Yii::t('app', 'Des'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'is_official' => Yii::t('app', 'Is Official'), 
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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
    
//    public function afterFind() {
//        
//        parent::afterFind();
//    }
    
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            //设置ID
            if (!$this->id) {
                $this->id = md5(time() . rand(1, 99999999));
            }
            //设置是否属于官网账号/企业用户or散户
            if($this->customer_id != null){
                $isOfficial = Customer::findOne(['id' => $this->customer_id]);
                $this->is_official = $isOfficial->is_official;
                $this->type = 2;        //企业用户
            } else {
                $this->type = 1;        //散户
                $this->is_official = 0; //非官网用户
            }
            
            //上传头像
            $upload = UploadedFile::getInstance($this, 'avatar');
            if ($upload != null) {
                $string = $upload->name;
                $array = explode('.', $string);
                //获取后缀名，默认为 jpg 
                $ext = count($array) == 0 ? 'jpg' : $array[count($array) - 1];
                $uploadpath = $this->fileExists(Yii::getAlias('@frontend/web/upload/avatars/'));
                $upload->saveAs($uploadpath . $this->username . '.' . $ext);
                $this->avatar = '/upload/avatars/' . $this->username . '.' . $ext . '?rand=' . rand(0, 1000);
            }
            
            if ($this->scenario == self::SCENARIO_CREATE) {
                $this->max_store = $this->max_store * $this->byte;
                $this->setPassword($this->password_hash);
                $this->generateAuthKey();
                //设置默认头像
                if (trim($this->avatar) == '' || !isset($this->avatar)){
                    $this->avatar = ($this->sex == null) ? '/upload/avatars/default.jpg' :
                            '/upload/avatars/default/' . ($this->sex == 1 ? 'man' : 'women') . rand(1, 25) . '.jpg';
                }
            }else if ($this->scenario == self::SCENARIO_UPDATE) {
                if (trim($this->password_hash) == ''){
                    $this->password_hash = $this->getOldAttribute('password_hash');
                }else{
                    $this->setPassword($this->password_hash);
                }
                if($this->max_store == $this->getOldAttribute('max_store')){
                    $this->max_store = $this->getOldAttribute('max_store');
                }else{
                    $this->max_store = $this->max_store * $this->byte;
                }
                if (trim($this->avatar) == ''){
                    $this->avatar = $this->getOldAttribute('avatar');
                }
            }

            if (trim($this->nickname) == ''){
                $this->nickname = $this->username;
            }

            return true;
        }
        return false;
    }
    
    /**
     * 检查目标路径是否存在，不存即创建目标
     * @param string $uploadpath    目录路径
     * @return string
     */
    protected function fileExists($uploadpath) {

        if (!file_exists($uploadpath)) {
            mkdir($uploadpath);
        }
        return $uploadpath;
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id) {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        $identity = self::find()
                ->where(['access_token' => $token , 'status' => self::STATUS_ACTIVE ])
                ->andWhere(['>=','access_token_expire_time',time()])->one();
        return $identity;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username) {
        return static::find()->where(['and', ['or', ['username' => $username], ['phone' => $username]], ['status' => self::STATUS_ACTIVE]])
                ->one();
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token) {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
                    'password_reset_token' => $token,
                    'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token) {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId() {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey() {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password) {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password) {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey() {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
    
    /**
     * 生成访问令牌
     */
    public function generateAccessToken() {
        $this->access_token = Yii::$app->security->generateRandomString();
        $this->access_token_expire_time = time() + Yii::$app->params['user.passwordAccessTokenExpire'];
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken() {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken() {
        $this->password_reset_token = null;
    }
   
}
