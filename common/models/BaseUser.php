<?php

namespace common\models;

use common\components\aliyuncs\Aliyun;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\web\UploadedFile;

/**
 * User 基类，集合公共方法、属性
 *
 * @author Administrator
 */
class BaseUser extends ActiveRecord implements IdentityInterface{
    //--------------------------------------------------------------------------
    // 
    // 属性
    // 
    //--------------------------------------------------------------------------
    /** 创建场景 */
    const SCENARIO_CREATE = 'create';
    /** 更新场景 */
    const SCENARIO_UPDATE = 'update';
    
    /** 性别 保密 */
    const SEX_SECRECY = 0;
    /** 性别 男 */
    const SEX_MALE = 1;
    /** 性别 女 */
    const SEX_WOMAN = 2;
    
    //已停账号
    const STATUS_STOP = 0;
    //活动账号
    const STATUS_ACTIVE = 10;
    
     /* 重复密码验证 */
    public $password2;
    
    /**
     * 性别
     * @var array 
     */
    public static $sexName = [
        self::SEX_SECRECY => '保密',
        self::SEX_MALE => '男',
        self::SEX_WOMAN => '女',
    ];
    
    //--------------------------------------------------------------------------
    // 
    // 方法
    // 
    //--------------------------------------------------------------------------
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('app', 'ID'),
            'username' => Yii::t('app', 'Account Number'),
            'nickname' => Yii::t('app', '{True}{Name}',['True' => Yii::t('app', 'True'),'Name' => Yii::t('app', 'Name'),]),
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password2' => Yii::t('app', 'Password2'),
            'password_reset_token' => Yii::t('app', 'Password Reset Token'),
            'sex' => Yii::t('app', 'Sex'),
            'phone' => Yii::t('app', 'Phone'),
            'email' => Yii::t('app', 'Email'),
            'avatar' => Yii::t('app', 'Avatar'),
            'status' => Yii::t('app', 'Status'),
            'des' => Yii::t('app', 'Des'),
            'auth_key' => Yii::t('app', 'Auth Key'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function behaviors() {
        return [
            TimestampBehavior::class,
        ];
    }
    
    public function beforeSave($insert) {
        if (parent::beforeSave($insert)) {
            //设置ID
            if (!$this->id) {
                $this->id = md5(time() . rand(1, 99999999));
            }
            
            //上传头像
            $upload = UploadedFile::getInstance($this, 'avatar');
            if ($upload != null) {
                //获取后缀名，默认为 png 
                $ext = pathinfo($upload->name,PATHINFO_EXTENSION);
                $img_path = "upload/avatars/{$this->username}.{$ext}";
                //上传到阿里云
                Aliyun::getOss()->multiuploadFile($img_path, $upload->tempName);
                $this->avatar = $img_path . '?rand=' . rand(0, 9999);                
            }
            
            if ($this->scenario == self::SCENARIO_CREATE) {
                $this->setPassword($this->password_hash);
                $this->generateAuthKey();
                //设置默认头像
                if (trim($this->avatar) == '' || !isset($this->avatar)){
                    $this->avatar = ($this->sex == null) ? 'upload/avatars/default.jpg' :
                            'upload/avatars/default/' . ($this->sex == 1 ? 'man' : 'women') . rand(1, 25) . '.jpg';
                }
            }else if ($this->scenario == self::SCENARIO_UPDATE) {
                if (trim($this->password_hash) == ''){
                    $this->password_hash = $this->getOldAttribute('password_hash');
                }else{
                    $this->setPassword($this->password_hash);
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
    
    /*
     * 数据查找后
     */
    public function afterFind(){
        $this->avatar = Aliyun::absolutePath(!empty($this->avatar) ? $this->avatar : 'upload/avatars/default.jpg');
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
    public function generateAccessToken($new_force = false) {
        /* 强制或者过期 */
        if ($new_force || $this->access_token == "" || (time() - $this->access_token_expire_time > Yii::$app->params['user.passwordAccessTokenExpire'])) {
            $this->access_token = Yii::$app->security->generateRandomString();
        }
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
