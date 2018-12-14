<?php


namespace apiend\controllers;

use apiend\components\auth\QueryParamHeaderAuth;
use Yii;
use yii\base\Controller;
use yii\base\ErrorException;
use yii\base\Event;
use yii\base\UserException;
use yii\db\Exception;
use yii\filters\VerbFilter;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Description of BaseApiController
 *
 * @author Administrator
 */
class ApiController extends Controller {
    
    /* 关掉 csrf 认证 */
    public $enableCsrfValidation = false;
    
    /**
     * 使用令牌认证
     * @return type
     */
    public function behaviors() {
        $behaviors = parent::behaviors();
        /* 使用令牌访问规则 */
        $behaviors['authenticator'] = [
            'class' => QueryParamHeaderAuth::className(),
            'optional' => [
                //'login',  //设置login接口可忽视该规则
            ],
        ];
        /* 路径规则 */
        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                //'login' => ['post'],  设置login接口使用POST提交
            ],
        ];
        return $behaviors;
    }

    public function beforeAction($action) {
        //绑定beforeSend事件，更改数据输出格式
        Yii::$app->getResponse()->on(Response::EVENT_BEFORE_SEND, [$this, 'beforeSend']);
        if (parent::beforeAction($action)) {
            // 5 minutes execution time
            @set_time_limit(5 * 60);
            
            Yii::$app->response->headers->set('Access-Control-Allow-Origin','*');
            Yii::$app->response->headers->set('Access-Control-Allow-Headers','Origin, X-Requested-With, Content-Type, Accept');
            Yii::$app->response->headers->set('Access-Control-Allow-Methods','GET, POST, PUT,DELETE');
            Yii::$app->response->headers->set('Expires','Mon, 26 Jul 1997 05:00:00 GMT');
            Yii::$app->response->headers->set('Last-Modified',gmdate("D, d M Y H:i:s") . " GMT");
            Yii::$app->response->headers->set('Cache-Control','no-store, no-cache, must-revalidate');
            Yii::$app->response->headers->add('Cache-Control','post-check=0, pre-check=0');
            Yii::$app->response->headers->set('Pragma','no-cache');

            if (!empty($_REQUEST['debug'])) {
                $random = rand(0, intval($_REQUEST['debug']));
                if ($random === 0) {
                    //header('HTTP/1.0 500 Internal Server Error');exit;
                }
            }
        };
        return parent::beforeAction($action);
    }
    
     /**
     * 更改数据输出格式
     * 默认情况下输出Json数据
     * 如果客户端请求时有传递$_GET['callback']参数，输入Jsonp格式
     * 请求正确时数据为  {code:0,meg:xxx,data:xxx}
     * 请求错误时数据为  {code:>0,meg:xxx,data:xxx}
     * @param Event $event
     */
    public function beforeSend($event)
    {
        /* @var $response Response */
        $response = $event->sender;
    
        //$isSuccessful = $response->isSuccessful;
        if ($response->statusCode>=400) {
            //异常处理
            if ($exception = Yii::$app->getErrorHandler()->exception) {
                $response->data = $this->convertExceptionToArray($exception);
            }
            //Model出错了
            if ($response->statusCode==422) {
                $messages=[];
                foreach ($response->data as $v) {
                    $messages[] = $v['message'];
                }
                //请求错误时数据为  {"success":false,"data":{"name":"Not Found","message":"页面未找到。","code":0,"status":404}}
                $response->data = [
                    'code'=> (string)$response->statusCode,
                    'msg'=> implode("  ", $messages),
                    'data'=>$response->data
                ];
            }
            $response->statusCode = 200;
        }
        elseif ($response->statusCode>=300) {
            $response->statusCode = 200;
            $response->data = $this->convertExceptionToArray(new ForbiddenHttpException(Yii::t('yii', 'Login Required')));
        }
    
        $response->format = Response::FORMAT_JSON;
        //jsonp 格式输出
        if (isset($_GET['callback'])) {
            $response->format = Response::FORMAT_JSONP;
            $response->data = [
                'callback' => $_GET['callback'],
                'data'=>$response->data,
            ];
        }
    }
    
    /**
     * 将异常转换为array输出
     * @see \yii\web\ErrorHandle
     * @param Exception $exception
     * @return multitype:string NULL Ambigous <string, \yii\base\string> \yii\web\integer \yii\db\array multitype:string NULL Ambigous <string, \yii\base\string> \yii\web\integer \yii\db\array
     */
    protected function convertExceptionToArray($exception)
    {
        if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
            $exception = new HttpException(500, Yii::t('yii', 'An internal server error occurred.'));
        }
        $response = [
            'msg' => $exception->getMessage(),
            'code' => property_exists($exception,'statusCode') ? (string)$exception->statusCode : "500",
        ];
        $array = [
            'name' => ($exception instanceof Exception2 || $exception instanceof ErrorException) ? $exception->getName() : 'Exception',
        ];
        if ($exception instanceof HttpException) {
            $array['status'] = property_exists($exception,'statusCode') ? (string)$exception->statusCode : "500";
        }
        if (YII_DEBUG) {
            $array['type'] = get_class($exception);
            if (!$exception instanceof UserException) {
                $array['file'] = $exception->getFile();
                $array['line'] = $exception->getLine();
                $array['stack-trace'] = explode("\n", $exception->getTraceAsString());
                if ($exception instanceof Exception2) {
                    $array['error-info'] = $exception->errorInfo;
                }
            }
        }
        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = $this->convertExceptionToArray($prev);
        }
        $response['data'] = $array;
        return $response;
    }

}
