<?php

namespace common\models;

use Yii;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%admin_user}}".
 *
 * @property string $id
 * @property string $username 用户登录名
 * @property string $nickname 昵称
 * @property string $auth_key 验证
 * @property string $password_hash 密码
 * @property string $password_reset_token 密码重置令牌
 * @property int $sex 性别：0保密 1男 2女
 * @property string $email 邮箱地址
 * @property string $avatar 头像
 * @property string $guid 企业微信
 * @property string $phone 电话
 * @property int $status 状态：0停用 10启用
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 */
class AdminUser extends User implements IdentityInterface
{
    public function scenarios() 
    {
        return [
            self::SCENARIO_CREATE => 
                ['username','nickname','sex','email','password_hash','password2','email','guid','phone','avatar'],
            self::SCENARIO_UPDATE => 
                ['username','nickname','sex','email','password_hash','password2','email','guid','phone','avatar'],
            self::SCENARIO_DEFAULT => ['username','nickname']
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%admin_user}}';
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), ['guid' => Yii::t('app', 'Guid'),]);
    }
    
//    public function afterFind() {
//        
//        //parent::afterFind();
//    }
}
