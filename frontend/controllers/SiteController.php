<?php
namespace frontend\controllers;

use common\models\Banner;
use common\models\LoginForm;
use common\models\User;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\SearchLog;
use common\models\vk\UserBrand;
use common\models\vk\Video;
use common\models\vk\VisitLog;
use common\utils\DateUtil;
use Detection\MobileDetect;
use frontend\models\ContactForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\OAuths\weiboAPI\SaeTOAuthV2;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\InvalidParamException;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotAcceptableHttpException;
use yii\web\NotFoundHttpException;
use const YII_ENV_TEST;

/**
 * Site controller
 */
class SiteController extends Controller
{
    public static $weiboConfig = 'weiboLogin';      //微博登录的配置
    public static $sendYunSmsConfig = 'sendYunSms'; //发送短信的配置
    
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        /* 宣传 */
        $banners = Banner::find()
                ->where(['is_publish' => Banner::YES_PUBLISH])
                ->orderBy('sort_order')
                ->all();
        
        /* 搜索排行 */
        $hotSearch = (new Query())
                ->select(['keyword','count(*) as count'])
                ->from(SearchLog::tableName())
                //->where(['and',['>=','created_at', strtotime("first day of ".date('Y-m'))],['<','created_at', strtotime(date('Y').'-'.(date('m')+1))]])
                ->groupBy('keyword')
                ->orderBy(['count' => SORT_DESC])
                ->limit(20)
                ->all();
        
        /* 入驻伙伴 */
        $customers = (new Query())
                ->select(['id','name','logo'])
                ->from(Customer::tableName())
                ->where(['status' => Customer::STATUS_ACTIVE])
                ->orderBy('sort_order')
                ->limit(10)
                ->all();
        
        /* 不足5个，补齐 */
        $len = 5 - count($customers);
        if ($len > 0) {
            for ($i = 0; $i < $len; $i++) {
                $customers[] = [
                    'id' => null,
                    'name' => '虚位以待',
                    'logo' => '/upload/customer/wait.jpg?rand='.Yii::$app->version,
                ];
            }
        }

        return $this->render('index', [
            'banners' => $banners,
            'hotSearchs' => ArrayHelper::map($hotSearch, 'keyword', 'count'),
            'customers' => $customers,
        ]);
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $post = Yii::$app->request->post();     //form表单提交上来的数据
        $weiboConfig = Yii::$app->params[self::$weiboConfig];       //获取微博登录的配置
        $weibo = new SaeTOAuthV2($weiboConfig['WB_AKEY'], $weiboConfig['WB_SKEY']);

        $model = new LoginForm();
        $isPass = !empty($post) ? array_key_exists('username', $post['LoginForm']) : true;  //是否为密码登录true
        
        if($isPass){
            $model->scenario = LoginForm::SCENARIO_PASS;    //设置密码登录场景

            if ($model->load(Yii::$app->request->post()) && $model->login()) {
                return $this->goBack();
            } else {
                $model->password = '';
            }
        } else {
            $model->scenario = LoginForm::SCENARIO_SMS;     //设置短信登录场景
            
            $phone = ArrayHelper::getValue($post, 'LoginForm.phone');   //获取输入的号码
            $code = ArrayHelper::getValue($post, 'LoginForm.code');     //获取输入的验证码
            if(empty($phone)){
                Yii::$app->getSession()->setFlash('error','手机号不能为空！');
            }elseif (empty($code)) {
                Yii::$app->getSession()->setFlash('error','验证码不能为空！');
            }
            //保存在sesson中的电话号码/验证码
            $sessonPhone = Yii::$app->session->get('code_phone', '');
            $sessonCode = Yii::$app->session->get('code_code', '');
            if($sessonPhone != $phone){
                Yii::$app->getSession()->setFlash('error','手机号与验证码不匹配！');
            } elseif ($code != $sessonCode) {
                Yii::$app->getSession()->setFlash('error','验证码错误！');
            } elseif ($model->smsLogin($phone)) {
                return $this->goBack();
            }
        }
        return $this->render('login', [
            'model' => $model,
            'weibo_url' => $weibo->getAuthorizeURL($weiboConfig['WB_CALLBACK_URL']), //微博登录回调地址
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {        
        Yii::$app->user->logout();
        
        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
    
    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new User();
        $model->scenario = User::SCENARIO_CREATE;
        $params = \Yii::$app->request->queryParams;     //参数
        $post = \Yii::$app->request->post();            //post传值
        $phone = ArrayHelper::getValue($post, 'User.phone');    //获取post传的号码
        $code = ArrayHelper::getValue($post, 'User.code');      //获取post传的验证码
        
        $weiboConfig = Yii::$app->params[self::$weiboConfig];       //获取微博登录的配置
        $weibo = new SaeTOAuthV2($weiboConfig['WB_AKEY'], $weiboConfig['WB_SKEY']);
        //保存在sesson中的电话号码/验证码
        $sessonPhone = Yii::$app->session->get('code_phone', '');
        $sessonCode = Yii::$app->session->get('code_code', '');
        if(!empty($phone)){
            if($sessonPhone != $phone || $sessonCode != $code){
                Yii::$app->getSession()->setFlash('error','号码或验证码错误！');
            } elseif ($model->load($post)) {
                if ($user = $this->signup($post)) {
                    if (Yii::$app->getUser()->login($user)) {
                        return $this->goHome();
                    }
                }
            }
        }
        
        return $this->render('signup', [
            'model' => $model,
            'code' => ArrayHelper::getValue($params, 'code'),
            'weibo_url' => $weibo->getAuthorizeURL($weiboConfig['WB_CALLBACK_URL']), //微博登录回调地址
        ]);
    }
    
    /**
     * 发送验证码的动作
     * @return array
     */
    public function actionSendSms()
    {
        $sendYunSmsConfig = Yii::$app->params[self::$sendYunSmsConfig];         //发送验证码配置
        $BINGDING_PHONE_ID = $sendYunSmsConfig['SMS_TEMPLATE_ID']['BINGDING_PHONE'];  //注册绑定手机号码/短信登录短信模板ID
        $RESET_PASSWORD_ID = $sendYunSmsConfig['SMS_TEMPLATE_ID']['RESET_PASSWORD'];  //重置密码短信模板ID
        
        \Yii::$app->getResponse()->format = 'json';
        $post = \Yii::$app->request->post();
        $phone = ArrayHelper::getValue($post, 'MOBILE');   //获取输入的电话号码
        $pathName = ArrayHelper::getValue($post, 'pathname');   //获取点击发送验证码时的路径
        $name = trim(strrchr($pathName, '/'),'/');

        //检查提交的号码是否存在
        $hasPhone = (new Query())->select(['id'])->from(['User' => User::tableName()])
                ->where(['status' => User::STATUS_ACTIVE,'phone' => $phone])
                ->one(); 
        if($name == 'signup'){      //注册页面
            if(empty($hasPhone)){
                $xmlResult = $this->sendSms($phone, $BINGDING_PHONE_ID);    //发送验证码功能
            } else {
                Yii::$app->session->setFlash('error', '号码错误或已存在！不能继续注册！！');
                return $this->goHome();
            }
        } elseif ($name == 'login') {   //登录页面
            if(!empty($hasPhone)){
                $xmlResult = $this->sendSms($phone, $BINGDING_PHONE_ID);    //发送验证码功能
            } else {
                Yii::$app->session->setFlash('error', '号码错误或不存在！');
                return $this->goHome();   
            }
        } elseif ($name == 'get-password') {    //重置密码页面
            if(!empty($hasPhone)){
                $xmlResult = $this->sendSms($phone, $RESET_PASSWORD_ID);    //发送验证码功能
            } else {
                Yii::$app->session->setFlash('error', '号码错误或不存在！');
                return $this->goHome();
            }
        }

        if($xmlResult == 1){
            return [
                'code' => 200,
                'message' => '发送成功'
            ];
        } else {
            return [
                'code' => 400,
                'message' => '发送失败'
            ];
        }
    }
        
    /**
     * 分享浏览入口
     */
    public function actionVisit(){
        //
        $md =new MobileDetect();
        //客户端信息
        $visit_agent = '';
        foreach ($md->getProperties() as $key => $v) {
            if (($result = $md->version($key)) != "") {
                $visit_agent .= "$key $result|";
            }
        }
        
        $params = Yii::$app->request->queryParams;
        //内容类型
        $item_type = ArrayHelper::getValue($params, 'item_type');
        //内容ID
        $item_id = ArrayHelper::getValue($params, 'item_id');
        //分享人
        $share_by = ArrayHelper::getValue($params, 'share_by');
        //用户IP
        $visit_ip = Yii::$app->request->userIP;
        //访问来源
        $income = ArrayHelper::getValue($params, 'income');

        $visitLog = new VisitLog([
            'item_type' => $item_type,
            'item_id' => $item_id,
            'share_by' => $share_by,
            'visit_ip' => $visit_ip,
            'visit_agent' => $visit_agent,
            'is_pc' => !$md->isMobile(),
            'income' => $income,
        ]);
        
        if($visitLog->save()){
            $paths = [
                VisitLog::TYPE_COURSE => "/course/default/view?id={$item_id}",
                VisitLog::TYPE_KNOWLEDGE => "/study_center/default/view?id={$item_id}",
            ];
            $this->redirect($paths[$item_type]);
        }else{
            throw new NotFoundHttpException('找不到对应分享！');
        }
    }

    /**
     * 重置密码请求（短信验证）
     * @return mix
     */
    public function actionGetPassword()
    {
        return $this->render('get-password');
    }

    /**
     * 重置密码（短信验证）
     * @return mix
     */
    public function actionSetPassword()
    {
        $sessonPhone = Yii::$app->session->get('code_phone');
        if(empty($sessonPhone)){
            return $this->goHome();
        }
        $model = User::findOne(['phone' => $sessonPhone]);        
        $model->password_hash = '';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->setPassword($model->password_hash);
            if($model->save(false)){
                Yii::$app->session->setFlash('success', Yii::t('app', 'New password saved.'));
//                unset(Yii::$app->session['code_timeOut']);  //销毁sesson
                return $this->goHome();
            }
        }
        
        return $this->render('set-password',[
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', Yii::t('app', 'Check your email for further instructions.'));

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', Yii::t('app', 'Sorry, we are unable to reset password for the provided email address.'));
            }
        }

        return $this->render('request-password-reset', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        
        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', Yii::t('app', 'New password saved.'));

            return $this->goHome();
        }

        return $this->render('reset-password', [
            'model' => $model,
        ]);
    }
    
    /**
     * 获取客户名
     * @return array
     */
    public function actionCustomer()
    {
        \Yii::$app->getResponse()->format = 'json';
        $post = \Yii::$app->request->post();
        $inviteCode = ArrayHelper::getValue($post, 'txtVal');   //获取输入的邀请码
        $customer = Customer::find()->select(['name'])->where(['invite_code' => $inviteCode])->asArray()->one(); //查找客户名
        
        if($customer != null){
            return [
                'code' => 200,
                'data' => [
                    'name' => ArrayHelper::getValue($customer, 'name'),
                ],
                'message' => ''
            ];
        } else {
            return [
                'code' => 404,
                'data' => [],
                'message' => '无效的邀请码'
            ];
        }
    }
    
    /**
     * 切换客户
     */
    public function actionSwitchCustomer()
    {
        /* 用户关联的所有品牌 */
        $customers = (new Query())->select(['Customer.id','Customer.name','Customer.logo'])
            ->from(['UserBrand' => UserBrand::tableName()])
            ->leftJoin(['Customer' => Customer::tableName()], 'Customer.id = UserBrand.brand_id')
            ->where(['user_id' => Yii::$app->user->id, 'is_del' => 0])
            ->orderBy('Customer.sort_order')
            ->all();
        
        if(Yii::$app->request->isPost){
            Yii::$app->getResponse()->format = 'json';
            $message = '';
            $is_success = false;
            try
            { 
                $relBrands = ArrayHelper::getColumn($customers, 'id');  //用户关联的所有品牌
                $userModel = User::findOne(Yii::$app->user->id);
                $userModel->customer_id = ArrayHelper::getValue(Yii::$app->request->post(), 'customer_id');
                
                if(in_array($userModel->customer_id, $relBrands)){
                    if($userModel->save(false, ['customer_id'])) {
                        $is_success = true;
                        $message = '切换成功！';
                    }
                }else{
                   $message = '切换失败::请正确选择和自己相关的品牌。'; 
                }
            }catch (Exception $ex) {
                $message = '切换失败::' . $ex->getMessage();
            }
            
            return [
                'code' => $is_success ? 200 : 401,
                'data' => [
                    'id' => $userModel->id,
                    'nickname' => $userModel->nickname
                ],
                'message' => $message
            ];
        }
        
        return $this->renderAjax('switch-customer', [
            'customers' => $customers
        ]);
    }

    /**
     * 检查号码是否已被注册
     * @return array
     */
    public function actionChickPhone()
    {
        \Yii::$app->getResponse()->format = 'json';
        $post = \Yii::$app->request->post();
        $phone = ArrayHelper::getValue($post, 'phone');   //获取输入的邀请码
        
        $hasPhone = (new Query())->select(['id'])->from(['User' => User::tableName()])
                ->where(['status' => User::STATUS_ACTIVE,'phone' => $phone])
                ->one();
        
        if(empty($hasPhone)){
            return [
                'code' => 200,
                'message' => '该号码未被注册'
            ];
        } else {
            return [
                'code' => 400,
                'message' => '该号码已被注册'
            ];
        }
    }
    
    /**
     * 验证输入的邀请码是否正确
     * @return array
     */
    public function actionProvingCode()
    {
        \Yii::$app->getResponse()->format = 'json';
        $post = \Yii::$app->request->post();
        $code = ArrayHelper::getValue($post, 'code');   //获取输入的邀请码
        
        //保存在sesson中的邀请码
        $params_code = Yii::$app->session->get('code_code', '');
        //保存在sesson中的过期时间
        $time_out = Yii::$app->session->get('code_timeOut', '');
        $now_time = time();      //当前时间
        
        if($time_out >= $now_time){
            if($params_code == $code){
                return [
                    'code' => 200,
                    'message' => '验证码正确'
                ];
            } else {
                return [
                    'code' => 400,
                    'message' => '验证码错误'
                ];
            }
        } else {
            return [
                'code' => 400,
                'message' => '验证码失效'
            ];
        }
    }
    
    /**
     * 检查手机号码和验证码是否匹配(重置密码)
     * @return array
     */
    public function actionCheckPhoneCode()
    {
        \Yii::$app->getResponse()->format = 'json';
        $post = \Yii::$app->request->post();
        $phone = ArrayHelper::getValue($post, 'phone');        //联系方式
        $code = ArrayHelper::getValue($post, 'code');  //验证码
        
        if(empty($phone)){
            Yii::$app->getSession()->setFlash('error','手机号不能为空！');
        }elseif (empty ($code)) {
            Yii::$app->getSession()->setFlash('error','验证码不能为空！');
        }
        //保存在sesson中的电话号码
        $sessonPhone = Yii::$app->session->get('code_phone', '');
        $sessonCode = Yii::$app->session->get('code_code', '');
        if($sessonPhone != $phone){
            Yii::$app->getSession()->setFlash('error','手机号与验证码不匹配！');
        } elseif ($code != $sessonCode) {
            Yii::$app->getSession()->setFlash('error','验证码错误！');
        } else {
            return [
                'code' => 200,
                'message' => '验证成功'
            ];
        }
        return [
            'code' => 400,
            'message' => '验证失败'
        ];
    }
    
    /**
     * 注册
     * @param type $post
     * @return type
     * @throws NotAcceptableHttpException
     */
    public function signup($post)
    {   
        $user = new User();
        if (!$user->validate()) {   //数据验证
            return null;
        }

        $cusId = ArrayHelper::getValue($post, 'User.customer_id');  //邀请码
        $username = ArrayHelper::getValue($post, 'User.username');  //用户名
        $phone = ArrayHelper::getValue($post, 'User.phone');        //联系方式
        $nickname = ArrayHelper::getValue($post, 'User.nickname');  //姓名
        $password_hash = ArrayHelper::getValue($post, 'User.password_hash');    //密码
        
        if($cusId != null){
            $customer = Customer::find()->select(['id'])->where(['invite_code' => $cusId])->asArray()->one();//客户ID
            if($customer != null){
                $customerId = ArrayHelper::getValue($customer, 'id');
            } else {
                throw new NotAcceptableHttpException('无效的邀请码！');
            }
        } else {
            $customerId = '';
        }
        //赋值
        $user->customer_id = $customerId;
        $user->username = $username;
        $user->phone = $phone;
        $user->nickname = $nickname;
        $user->avatar = ($user->sex == null) ? '/upload/avatars/default.jpg' :
                            '/upload/avatars/default/' . ($user->sex == 1 ? 'man' : 'women') . rand(1, 25) . '.jpg';
        $user->setPassword($password_hash);
        $user->generateAuthKey();
        
        $isTrue = $user->save();
        //customerId不为空并且创建用户成功时绑定品牌
        if($customerId != null && $isTrue){
            UserBrand::userBingding($user->id, $customerId, true);
        }
        
        return $isTrue ? $user : null;
    }
    
    /**
     * 发送验证码
     * @param integer $phone    电话号码
     * @param string $SMS_TEMPLATE_ID   短信模板
     * @return array
     */
    public function sendSms($phone, $SMS_TEMPLATE_ID)
    {
        $sendYunSmsConfig = Yii::$app->params[self::$sendYunSmsConfig];         //发送验证码配置
        $SMS_APP_ID = $sendYunSmsConfig['SMS_APP_ID'];                          //应用ID
        
        $str='0123456789876543210';  
        $randStr = str_shuffle($str);           //打乱字符串  
        //把生成的验证码和到期时间保存到sesson中
        Yii::$app->session->set('code_phone', $phone);
        Yii::$app->session->set('code_code', substr($randStr, 0, 4));//验证码【substr(string,start,length);返回字符串的一部分】
        Yii::$app->session->set('code_timeOut', time() + 30*60);
     
        
        $PARAMS = Yii::$app->session->get('code_code');
        //传递的参数【必须是以下xml格式】
        $xmlDatas = '<?xml version="1.0" encoding="UTF-8"?>' .
                '<tranceData>' .
                    "<MOBILE><![CDATA[$phone]]></MOBILE>" .
                    "<SMS_TEMPLATE_ID><![CDATA[$SMS_TEMPLATE_ID]]></SMS_TEMPLATE_ID>" .
                    "<SMS_APP_ID><![CDATA[$SMS_APP_ID]]></SMS_APP_ID>" .
                    '<PARAMS>' .
                        "<![CDATA[$PARAMS]]>" .
                    '</PARAMS>' .
                '</tranceData>';

        $url = 'http://eesms.gzedu.com/sms/sendYunSms.do';  //发送短信的请求地址
        $curl = new Curl();
        $response = $curl
                ->setOption(CURLOPT_HTTPHEADER, Array("Content-Type:text/xml; charset=utf-8"))
                ->setOption(CURLOPT_POSTFIELDS, $xmlDatas)->post($url); //提交发送
        //转换为simplexml对象
        $xmlResult = simplexml_load_string($response);//XML 字符串载入对象中
        
        return (string)$xmlResult->result;
    } 
    
    /**
     * 获取点赞排行靠前的课程
     * @param string $customerId
     * @param boolen $is_official
     * @return array
     */
    protected function getCourseRank($customerId, $is_official)
    {
        //查询课程
        $query = Course::find()->where(['is_publish' => 1]);
        $query->andFilterWhere(['customer_id' => !$is_official ? $customerId : null]);
        $query->andFilterWhere(['level' => !$is_official ? 
            [Course::INTRANET_LEVEL, Course::PUBLIC_LEVEL] : Course::PUBLIC_LEVEL]);
        $query->orderBy(['zan_count' => SORT_DESC])
            ->limit(6)->with('teacher');
        //获取课程
        $courses = $query->asArray()->all();
        //课程节点
        $nodes = $this->findVideoByCourseNode(ArrayHelper::getColumn($courses, 'id'));
        //已课程id为键值合并节点来获取该课程下的节点数
        $results = ArrayHelper::merge(ArrayHelper::index($courses, 'id'), ArrayHelper::index($nodes, 'course_id'));
                
        return array_values($results);
    }
    
    /**
     * 查询课程环节数据
     * @return Array
     */
    protected function findVideoByCourseNode($courseId){
        $query = Video::find()->select(['CourseNode.course_id', 'COUNT(Video.id) AS node_num'])
            ->from(['Video' => Video::tableName()]);
        $query->leftJoin(['CourseNode' => CourseNode::tableName()], '(CourseNode.id = Video.node_id AND CourseNode.is_del = 0)');
        $query->where(['Video.is_del' => 0, 'CourseNode.course_id' => $courseId]);
        $query->groupBy('CourseNode.course_id');
        
        return $query->asArray()->all();
    }
    
    /**
     * 获取搜索记录
     * @return array
     */
    protected function getSearchLog()
    {
        $date = DateUtil::getMonthSE(date('Y-m-d'));    //获取本月的开始日期和结束日期
        //查询出本月的搜索记录
        $hotSearch = (new Query())->select(['keyword', 'COUNT(keyword) AS keynum'])
            ->from(SearchLog::tableName())
            ->where(['between', 'created_at', strtotime($date['start']), strtotime($date['end'])])
            ->groupBy('keyword')->orderBy(['COUNT(keyword)' => SORT_DESC])->limit(10)->all();
        
        return ArrayHelper::map($hotSearch, 'keyword', 'keynum');
    }
}
