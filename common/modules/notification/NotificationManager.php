<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\notification;

use common\modules\notification\core\AppApi;
use yii\web\View;

class NotificationManager {

    /** view默认位置 */
    public static $viewPath = '@common/mail/';

    /** 定义应用$agent_id常量 = 1000007 */
    public static $agent_id = 1000007;

    /**
     * send 发送信息的函数
     * @param string|array $receivers       接收者，以‘|’分隔，包含中文需使用URL编码
     * @param string $title                 消息标题
     * @param string $url                   访问链接
     * @param string $content               消息内容
     */
    public static function send($receivers, $title, $url, $content) 
    {
        if (is_array($receivers))
            $receivers = implode('|', $receivers);

        $msg = array(
            'touser' => $receivers,
            'toparty' => '',
            'msgtype' => 'textcard',
            'agentid' => self::$agent_id,
            'textcard' => array(
                "title" => $title,
                "description" => preg_replace("/[\s]{2,}/","",trim($content)),
                "url" => $url,
            )
        );

        $api = new AppApi(self::$agent_id);
        return $api->sendMsgToUser($msg);
    }

    /**
     * 用视图模板渲染send
     * @param string $view                  视图模板
     * @param string $params                转进视图模板参数
     * @param string|array $receivers       接收者，以‘|’分隔，包含中文需使用URL编码
     * @param string $title                 消息标题
     * @param string $url                   访问链接
     * @return type
     */
    public static function sendByView($view, $params, $receivers, $title = '', $url = '') 
    {
        /** 用于渲染模板 */
        $render = new View();

        //$url = self::$viewPath;
        if (strpos($view, '@') == false)
            $view = self::$viewPath . $view;

        return self::send(
                $receivers, 
                $title,
                $url,
                $render->render($view, $params)
        );
    }

}
