<?php

namespace frontend\modules\callback\controllers;

use common\components\OAuths\qqAPI\core\QC;
use common\models\User;
use common\models\UserAuths;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotAcceptableHttpException;

/**
 * QqCallback controller for the `callback` module
 */
class QqCallbackController extends Controller
{
    /**
     * 授权页面
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    /**
     * 授权成功的回调地址
     * Renders the index view for the module
     * @return string
     */
    public function actionCallback()
    {
        $qc = new QC();
        $access_token = $qc->qq_callback(); //access_token
        $open_id = $qc->get_openid();       //openid
        
        if($open_id){
            $qc = new QC($access_token, $open_id);
            $user_data = $qc->get_user_info(); //get_user_info()为获得该用户的信息，
            
            $userAuths = UserAuths::findOne(['identifier' => $open_id]);     //是否已绑定
            if(!empty(\Yii::$app->user->id)){
                $userModel = User::findOne(['id' => \Yii::$app->user->id]);
                if(!empty($userAuths)){
                    \Yii::$app->getSession()->setFlash('error', '绑定失败！一个QQ账号只能绑定一个用户');
                    return $this->goBack();
                } else {
                    //保存Qq用户数据
                    $results = $this->bindingQqUser($userModel, $open_id, $access_token);
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
                return $this->render('callback', [
                    'model' => $model,
                    'access_token' => $access_token,            //密钥
                    'open_id' => $open_id,                      //open_id
                    'nickname' => $user_data['nickname'],       //用户名
                    'gender' => $user_data['gender'],           //性别
                    'avatar' => $user_data['figureurl_qq_2'],   //头像
                ]);
            }
            $user = new User(['id' => $userAuths->user_id]);
            Yii::$app->getUser()->login($user);
            return $this->goHome();
        }
        return $this->redirect('/site/login');
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
        $params = Yii::$app->request->queryParams;
        $username = ArrayHelper::getValue($post, 'User.username');      //需要绑定的用户名
        $password = ArrayHelper::getValue($post, 'User.password_hash'); //密码
        $access_token = ArrayHelper::getValue($params, 'access_token'); //密钥
        $open_id = ArrayHelper::getValue($params, 'open_id');           //open_id

        $userModel = User::findOne(['username' => $username]);
        if($userModel != null){
            if(Yii::$app->security->validatePassword($password, $userModel->password_hash)){
                //保存微博用户数据
                $results = $this->bindingQqUser($userModel, $open_id, $access_token);
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
        $params = Yii::$app->request->queryParams;
        $type = ArrayHelper::getValue($params, 'type');
        $access_token = ArrayHelper::getValue($params, 'access_token'); //密钥
        $open_id = ArrayHelper::getValue($params, 'open_id');           //open_id
        $nickname = ArrayHelper::getValue($params, 'nickname');         //QQ用户名
        $gender = ArrayHelper::getValue($params, 'gender');             //性别
        $avatar = ArrayHelper::getValue($params, 'avatar');             //头像
        
        $userAuths = UserAuths::findOne(['identifier' => $open_id]);
        if($type == 2){
            if($userAuths == null){
                //保存QQ用户数据
                $results = $this->saveQqUser($open_id, $access_token, $nickname, $gender, $avatar);
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
     * @param string $open_id           qq用户openid
     * @param string $access_token      qq用户密钥
     * @return array
     */
    public function bindingQqUser($userModel, $open_id, $access_token)
    {
        $authValues = [
            'user_id' => $userModel->id,
            'identity_type' => 'qq',
            'identifier' => $open_id,
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
     * @param string $open_id       qq用户openid
     * @param string $access_token  qq用户密钥
     * @param type $nickname        qq用户名
     * @param type $gender          性别
     * @param type $avatar          头像
     * @return array
     */
    public function saveQqUser($open_id, $access_token, $nickname, $gender, $avatar)
    {
        $userValues = [
            'id' => md5(time() . rand(1, 99999999)),
            'username' => $nickname,
            'nickname' => $nickname,
            'password_hash' => Yii::$app->security->generatePasswordHash($access_token),
            'type' => User::TYPE_FREE,
            'sex' => $gender == '男' ? '1' : '2',
            'avatar' => $avatar,
            'is_official' => 0,
            'created_at' => time(),
            'updated_at' => time(),
        ];
        
        /** 添加$userValues数组到表里 */
        $userNum = Yii::$app->db->createCommand()->insert(User::tableName(), $userValues)->execute();
        
        if($userNum > 0){
            $userModel = User::findOne(['username' => $nickname]);
            $authValues = [
                'user_id' => $userModel->id,
                'identity_type' => 'qq',
                'identifier' => $open_id,
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
    
}
