<?php
namespace frontend\models;

use common\models\User;
use Yii;
use yii\base\InvalidParamException;
use yii\base\Model;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
    public $password_hash;
    public $password2;

    /**
     * @var User
     */
    private $_user;


    /**
     * Creates a form model given a token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidParamException if token is empty or not valid
     */
    public function __construct($token, $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidParamException(Yii::t('app', 'Password reset token cannot be blank.'));
        }
        $this->_user = User::findByPasswordResetToken($token);
        if (!$this->_user) {
            throw new InvalidParamException(Yii::t('app', 'Wrong password reset token.'));
        }
        parent::__construct($config);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['password_hash', 'password2'], 'required'],
            [['password_hash'], 'string', 'min' => 6, 'max' => 64],
            [['password2'], 'compare', 'compareAttribute' => 'password_hash'],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'password_hash' => Yii::t('app', 'Password Hash'),
            'password2' => Yii::t('app', 'Password2'),
        ];
    }
    
    /**
     * Resets password.
     *
     * @return bool if password was reset.
     */
    public function resetPassword()
    {
        $user = $this->_user;
        $user->setPassword($this->password_hash);
        $user->removePasswordResetToken();

        return $user->save(false);
    }
}
