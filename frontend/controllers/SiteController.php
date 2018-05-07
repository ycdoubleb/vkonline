<?php
namespace frontend\controllers;

use common\models\Banner;
use common\models\LoginForm;
use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\SearchLog;
use common\models\vk\Video;
use common\utils\ChoiceUtils;
use common\utils\DateUtil;
use frontend\models\ContactForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
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
        $categoryId = ArrayHelper::getValue(\Yii::$app->request->queryParams, 'id');
        $customerModel = ChoiceUtils::findCustomer();
        $bannerModel = Banner::findAll(['customer_id' => $customerModel->id, 'is_publish' => 1]);
        $classifys = ChoiceUtils::getChoiceCatsByLevel();
        $firstCateId = ArrayHelper::getValue(reset($classifys), 'id');
        $cateId = empty($categoryId) ? $firstCateId : $categoryId;
        $courses = ChoiceUtils::getChoiceCourseByCategoryId($cateId);
        $courseRanks = $this->getCourseRank($customerModel->id, $customerModel->is_official);
        
        return $this->render('index', [
            'bannerModel' => $bannerModel,
            'categorys' => Category::getCatsByLevel(),
            'hotSearchs' => $this->getSearchLog(),
            'classifys' => $classifys,
            'courses' => $courses,
            'courseRanks' => $courseRanks,
            'categoryId' => $cateId,
        ]);
    }
    
    /**
     * Displays squarepage.
     *
     * @return mixed
     */
    public function actionSquare()
    {
        if(\Yii::$app->user->identity->is_official){
            throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
        }
        ChoiceUtils::$isBelongToIndex = false;
        $categoryId = ArrayHelper::getValue(\Yii::$app->request->queryParams, 'id');
        $bannerModel = Banner::findAll(['is_official' => 1, 'is_publish' => 1]);
        $classifys = ChoiceUtils::getChoiceCatsByLevel();
        $firstCateId = ArrayHelper::getValue(reset($classifys), 'id');
        $cateId = empty($categoryId) ? $firstCateId : $categoryId;
        $courses = ChoiceUtils::getChoiceCourseByCategoryId($cateId);
        $courseRanks = $this->getCourseRank(null, 1);
        
        return $this->render('index', [
            'bannerModel' => $bannerModel,
            'categorys' => Category::getCatsByLevel(),
            'hotSearchs' => $this->getSearchLog(),
            'classifys' => $classifys,
            'courses' => $courses,
            'courseRanks' => $courseRanks,
            'categoryId' => $cateId,
            'isBelongToIndex' => false,
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
        
        $url = \Yii::$app->request->hostInfo;       //获取当前域名
        $hostUrl = trim(strrchr($url, '/'),'/');    //截取最后一个斜杠后面的内容
        $customerLogo = Customer::find()->select(['logo'])->where(['domain' => $hostUrl])->asArray()->one();

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        } else {
            $model->password = '';

            return $this->render('login', [
                'model' => $model,
                'customerLogo' => ArrayHelper::getValue($customerLogo, 'logo'),
            ]);
        }
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

        if ($model->load(Yii::$app->request->post())) {
            if ($user = $this->signup(Yii::$app->request->post())) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
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
                'message' => '<span style="color:#a94442">无效的邀请码</span>'
            ];
        }
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
//        $user->load($post);
//        var_dump($post);exit;
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
            $officialCus = Customer::find()->select(['id'])->where(['is_official' => 1])->asArray()->one(); //官网ID
            $customerId = ArrayHelper::getValue($officialCus, 'id');
        }
        //赋值
        $user->customer_id = $customerId;
        $user->username = $username;
        $user->phone = $phone;
        $user->nickname = $nickname;
        $user->setPassword($password_hash);
        $user->generateAuthKey();
        
        return $user->save() ? $user : null;
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
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
            }
        }

        return $this->render('requestPasswordResetToken', [
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
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    
    public function ActionGetRecommend(){
        
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
