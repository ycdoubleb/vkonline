<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    /** 密码登录场景 */
    const SCENARIO_PASS = 'pass';
    /** 短信登录场景 */
    const SCENARIO_SMS = 'sms';
    
    public $username;
    public $phone;
    public $password;
    public $rememberMe = true;
    public $userClass;

    private $_user;

    public function init() {
        parent::init();
        !empty($this->userClass) ? : $this->userClass = User::class;
    }
    
    public function scenarios() {
        return [
            self::SCENARIO_PASS => ['username', 'password'],
            self::SCENARIO_SMS => ['phone'],
            self::SCENARIO_DEFAULT => ['username', 'password', 'phone'],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required', 'on' => [self::SCENARIO_PASS]],
            [['phone'], 'required', 'on' => [self::SCENARIO_SMS]],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword', 'on' => [self::SCENARIO_PASS]],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels() 
    {
        return [
            'username' => Yii::t('app', 'Account Number'),
            'phone' => Yii::t('app', 'Phone'),
            'password' => Yii::t('app', 'Password Hash'),
        ];
    }
    
    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, Yii::t('app', 'Incorrect username or password'));
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $hasLogin = Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
            if($hasLogin && $this->userClass == User::class){
                $this->_user->generateAccessToken();
                $this->_user->save(false);
            }
            return $hasLogin;
        }
        
        return false;
    }
    
    /**
     * 短信登录验证
     * @param type $phone   输入的唯一号码
     * @return boolean  whether the user is logged in successfully
     */
    public function smsLogin($phone)
    {
        if (!empty($phone)) {
            $user = User::findOne(['phone' => $phone, 'status' => self::STATUS_ACTIVE]);
            $hasLogin = Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
            if($hasLogin && $this->userClass == User::class){
                $user->generateAccessToken();
                $user->save(false);
            }
            return $hasLogin;
        }
        
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        $User = $this->userClass;
        if ($this->_user === null) {
                $this->_user = $User::findByUsername($this->username);
            }

        return $this->_user;
    }
}
