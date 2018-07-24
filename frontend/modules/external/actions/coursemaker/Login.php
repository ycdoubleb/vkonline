<?php

namespace frontend\modules\external\actions\coursemaker;

use frontend\modules\external\models\coursemaker\CoursemakerResponse;
use common\models\User;
use common\models\vk\CourseToolActLog;
use common\models\vk\CourseToolUser;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;

/**
 * CourseMaker 为平台用户登录接口作记录
 *
 * @author Administrator
 */
class Login extends Action {

    public function run() {
        ;
        $post = Yii::$app->request->post();
        $user_id = ArrayHelper::getValue($post, 'user_id', null);
        if ($user_id == null) {
            return new CoursemakerResponse(CoursemakerResponse::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'user_id']);
        }
        
        /* 检查用户是否存在 */
        $user = User::findOne(['id' => $user_id, 'status' => User::STATUS_ACTIVE]);
        if($user == null){
            return new CoursemakerResponse(CoursemakerResponse::CODE_USER_NOT_FOUND);
        }
        
        /* 检查是否重复注册 */
        $toolUser = CourseToolUser::findOne(['user_id' => $user_id , 'type' => CourseToolUser::COURSEMAKER, 'is_del' => 0]);
        
        if(!$toolUser){
            //新增工具账号
            $toolUser = new CourseToolUser();
            $toolUser->user_id = $user_id;
            $toolUser->type =  CourseToolUser::COURSEMAKER;
            $toolUser->open_id =  '';

            if(!$toolUser->save()){
                return new CoursemakerResponse(CoursemakerResponse::CODE_COMMON_SAVE_DB_FAIL,null,$toolUser->getErrorSummary(true));
            }
        }
        /* 添加登录记录 */
        $loginAct = new CourseToolActLog();
        $loginAct->tool_user_id = $toolUser->id;
        $loginAct->act = CourseToolActLog::ACT_LOGIN;
        
        if(!$loginAct->save()){
                return new CoursemakerResponse(CoursemakerResponse::CODE_COMMON_SAVE_DB_FAIL,null,$toolUser->getErrorSummary(true));
            }
        return new CoursemakerResponse(CoursemakerResponse::CODE_COMMON_OK);
    }

}
