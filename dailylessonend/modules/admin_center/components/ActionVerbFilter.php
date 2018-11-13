<?php

namespace dailylessonend\modules\admin_center\components;

use common\models\vk\CustomerAdmin;
use Yii;
use yii\base\ActionEvent;
use yii\filters\VerbFilter;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotAcceptableHttpException;

/**
 * 增加是否为管理员的判断
 * Description of ActionVerbFilter
 *  
 * @author Administrator
 */
class ActionVerbFilter extends VerbFilter
{
    /**
     * @param ActionEvent $event
     * @return bool
     * @throws MethodNotAllowedHttpException when the request method is not allowed.
     */
    public function beforeAction($event)
    {
        $is_admin = CustomerAdmin::findOne(['user_id' => Yii::$app->user->id]);
       
        if($is_admin == null){
            throw new NotAcceptableHttpException('没有权限查看该页面！');
        }
        $action = $event->action->id;
        if (isset($this->actions[$action])) {
            $verbs = $this->actions[$action];
        } elseif (isset($this->actions['*'])) {
            $verbs = $this->actions['*'];
        } else {
            return $event->isValid;
        }

        $verb = Yii::$app->getRequest()->getMethod();
        $allowed = array_map('strtoupper', $verbs);
        if (!in_array($verb, $allowed)) {
            $event->isValid = false;
            // https://tools.ietf.org/html/rfc2616#section-14.7
            Yii::$app->getResponse()->getHeaders()->set('Allow', implode(', ', $allowed));
            throw new MethodNotAllowedHttpException('Method Not Allowed. This URL can only handle the following request methods: ' . implode(', ', $allowed) . '.');
        }

        return $event->isValid;
    }
}
