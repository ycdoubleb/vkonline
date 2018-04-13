<?php
namespace frontend\controllers;

use common\models\Banner;
use common\models\LoginForm;
use common\models\User;
use common\models\vk\Category;
use common\models\vk\Course;
use common\models\vk\CourseNode;
use common\models\vk\Customer;
use common\models\vk\Video;
use common\utils\ChoiceUtils;
use frontend\models\ContactForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use Yii;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
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
        
        $url = \Yii::$app->request->hostInfo;   //获取当前域名
        $customerLogo = Customer::find()->select(['logo'])->where(['domain' => $url])->asArray()->one();
        
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
            if ($user = $model->signup()) {
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
}
