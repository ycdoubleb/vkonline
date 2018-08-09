<?php

namespace frontend\modules\callback\controllers;

use common\models\User;
use common\models\UserAuths;
use frontend\OAuths\weiboAPI\OAuthException;
use frontend\OAuths\weiboAPI\SaeTClientV2;
use frontend\OAuths\weiboAPI\SaeTOAuthV2;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotAcceptableHttpException;

/**
 * WeiboCallback controller for the `callback` module
 */
class WeiboCallbackController extends Controller
{
    public static $weiboConfig = 'weiboLogin';
    
    /**
     * 授权成功的回调地址
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $weiboConfig = Yii::$app->params[self::$weiboConfig];   //获取配置信息
        $weibo = new SaeTOAuthV2($weiboConfig['WB_AKEY'], $weiboConfig['WB_SKEY']);
        
        if (isset($_REQUEST['code'])) {
            $keys = array();
            $keys['code'] = $_REQUEST['code'];
            $keys['redirect_uri'] = $weiboConfig['WB_CALLBACK_URL'];
            try {
                $token = $weibo->getAccessToken( 'code', $keys ) ;
            } catch (OAuthException $e) {
                
            }
        }
        
        if(!isset($token)){
            return $this->redirect('/site/login');
        }
        if ($token) {
            $_SESSION['token'] = $token;
            setcookie('weibojs_'.$weibo->client_id, http_build_query($token));
            $user_message = $this->getWeiboUserInfos();  //获取用户等基本信息
            if($user_message['error_code'] == 10023){
                \Yii::$app->getSession()->setFlash('error', '用户请求超过上限!');
                return $this->goHome();
            }
            
            $userAuths = UserAuths::findOne(['identifier' => $user_message['id']]);     //是否已绑定
            if(!empty(\Yii::$app->user->id)){
                $userModel = User::findOne(['id' => \Yii::$app->user->id]);
                if(!empty($userAuths)){
                    \Yii::$app->getSession()->setFlash('error', '绑定失败！一个微博账号只能绑定一个用户');
                    return $this->goBack();
                } else {
                    //保存微博用户数据
                    $results = $this->bindingWeiboUser($userModel, $user_message, $_SESSION['token']['access_token']);
                    if($results['code'] == 400){
                        throw new NotAcceptableHttpException('绑定出错！请重新绑定');
                    } else {
                        $user = new User(['id' => $userModel->id]);
                        Yii::$app->getUser()->login($user);
                        \Yii::$app->getSession()->setFlash('success', '绑定成功！');
                        return $this->goHome();
                    }
                }
            }
            if($userAuths == null){
                $model = new User();
                return $this->render('index', [
                    'model' => $model
                ]);
            }
            $user = new User(['id' => $userAuths->user_id]);
            Yii::$app->getUser()->login($user);
            return $this->goHome();
        } else {
            return $this->redirect('/site/login');
        }
    }
    
    /**
     * 授权失败的回调地址
     * @return string
     */
    public function actionFail()
    {
        $weiboConfig = Yii::$app->params[self::$weiboConfig];   //获取配置信息
        $weibo = new SaeTOAuthV2($weiboConfig['WB_AKEY'], $weiboConfig['WB_SKEY']);
        
        return $this->render('fail', [
            'weibo_url' => $weibo->getAuthorizeURL($weiboConfig['WB_CALLBACK_URL']), //微博登录回调地址
        ]);
    }

    /**
     * 绑定用户
     * @return array
     * @throws NotAcceptableHttpException
     */
    public function actionBindingUser()
    {
        \Yii::$app->getResponse()->format = 'json';
        $post = Yii::$app->request->post();
        $username = ArrayHelper::getValue($post, 'User.username');
        $password = ArrayHelper::getValue($post, 'User.password_hash');

        $userModel = User::findOne(['username' => $username]);
        if($userModel != null){
            if(Yii::$app->security->validatePassword($password,$userModel->password_hash)){
                $user_message = $this->getWeiboUserInfos();  //获取用户等基本信息
                if($user_message['error_code'] == 10023){
                    \Yii::$app->getSession()->setFlash('error', '用户请求超过上限!');
                    return $this->goHome();
                }
                //保存微博用户数据
                $results = $this->bindingWeiboUser($userModel, $user_message, $_SESSION['token']['access_token']);
                if($results['code'] == 400){
                    throw new NotAcceptableHttpException('绑定出错！请重新登录');
                }
                return [
                    'code' => 200,
                    'message' => '绑定成功！'
                ];
            }
            return [
                'code' => 417,
                'message' => '账号密码不存在或错误！'
            ];
        }
        
        return [
            'code' => 417,
            'message' => '账号密码不存在或错误！'
        ];
    }

    /**
     * 直接完成注册
     * @return array
     * @throws NotAcceptableHttpException
     */
    public function actionSignup()
    {
        $user_message = $this->getWeiboUserInfos();  //获取用户等基本信息
        $params = Yii::$app->request->queryParams;
        $type = ArrayHelper::getValue($params, 'type');
        if($user_message['error_code'] == 10023){
            \Yii::$app->getSession()->setFlash('error', '用户请求超过上限!');
            return $this->goHome();
        }
        
        $userAuths = UserAuths::findOne(['identifier' => $user_message['id']]);
        if($type == 2){
            if($userAuths == null){
                //保存微博用户数据
                $results = $this->saveWeiboUser($user_message, $_SESSION['token']['access_token']);
                if($results['code'] == 400){
                    throw new NotAcceptableHttpException('数据保存出错！请重新登录');
                }
                $userId = $results['user_id'];
                $user = new User(['id' => $userId]);
                Yii::$app->getUser()->login($user);
                return $this->redirect("/user/default/index?id=$userId");
            }
            $user = new User(['id' => $userAuths->user_id]);
            Yii::$app->getUser()->login($user);
            return $this->goHome();
        } elseif ($type == 1) {
            $user = new User(['id' => $userAuths->user_id]);
            Yii::$app->getUser()->login($user);
            return $this->goHome();
        }
    }

    /**
     * 绑定已有的用户
     * @param model $userModel          用户模型
     * @param array $user_message       微博用户信息
     * @param string $access_token      微博用户密钥
     * @return array
     */
    public function bindingWeiboUser($userModel, $user_message, $access_token)
    {
        $authValues = [
            'user_id' => $userModel->id,
            'identity_type' => 'weibo',
            'identifier' => $user_message['id'],
            'credential' => $access_token,
        ];
        /** 添加$authValues数组到表里 */
        $authNum = Yii::$app->db->createCommand()->insert(UserAuths::tableName(), $authValues)->execute();

        if($authNum > 0){
            return ['code' => 200];
        } else {
            return ['code' => 400];
        }
    }
    
    /**
     * 保存第三方登录数据
     * @param array $user_message       微博用户信息
     * @param string $access_token      微博用户密钥
     * @return array
     */
    public function saveWeiboUser($user_message, $access_token)
    {
        $userValues = [
            'id' => md5(time() . rand(1, 99999999)),
            'username' => $user_message['screen_name'],
            'nickname' => $user_message['name'],
            'password_hash' => Yii::$app->security->generatePasswordHash($access_token),
            'type' => User::TYPE_FREE,
            'sex' => $user_message['gender'] == 'm' ? '1' : '2',
            'avatar' => $user_message['avatar_large'],
            'is_official' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ];
        
        /** 添加$userValues数组到表里 */
        $userNum = Yii::$app->db->createCommand()->insert(User::tableName(), $userValues)->execute();
        
        if($userNum > 0){
            $userModel = User::findOne(['username' => $user_message['screen_name']]);
            $authValues = [
                'user_id' => $userModel->id,
                'identity_type' => 'weibo',
                'identifier' => $user_message['id'],
                'credential' => $access_token,
            ];
            /** 添加$authValues数组到表里 */
            $authNum = Yii::$app->db->createCommand()->insert(UserAuths::tableName(), $authValues)->execute();
            
            if($authNum > 0){
                return ['code' => 200, 'user_id' => $userModel->id];
            } else {
                return ['code' => 400];
            }
        } else {
            return ['code' => 400];
        }
    }
    
    /**
     * 获取微博用户等基本信息
     * @return array
     */
    public function getWeiboUserInfos()
    {
        $weiboConfig = Yii::$app->params[self::$weiboConfig];   //获取配置信息
        $userInfos = new SaeTClientV2($weiboConfig['WB_AKEY'], $weiboConfig['WB_SKEY'], $_SESSION['token']['access_token']);
        
        $ms  = $userInfos->home_timeline(); // done
        $uid_get = $userInfos->get_uid();
        $uid = $uid_get['uid'];
        $user_message = $userInfos->show_user_by_id($uid);  //根据ID获取用户等基本信息
        
        return $user_message;
    }
}
