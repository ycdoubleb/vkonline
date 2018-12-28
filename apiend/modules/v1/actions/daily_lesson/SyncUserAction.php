<?php

namespace apiend\modules\v1\actions\daily_lesson;

use apiend\models\Response;
use apiend\modules\v1\actions\BaseAction;
use common\models\Region;
use common\models\UserProfile;
use common\models\vk\Customer;
use common\models\vk\CustomerAdmin;
use common\models\vk\Teacher;
use dailylessonend\models\DailyLessonUser;
use Exception;
use yii\helpers\ArrayHelper;

/**
 * 同步用户：新用户，更新旧用户
 * 需要同步 创建/更新 用户品牌，品牌管理员，老师
 *
 * @author Administrator
 */
class SyncUserAction extends BaseAction {

    public function run() {
        if (!$this->verify()) {
            return $this->verifyError;
        }
        $params = $this->getSecretParams();
        ;
        $notfounds = $this->checkRequiredParams($params, ['id', 'username', 'nickname', 'password_hash', 'phone',]);
        if (count($notfounds) > 0) {
            return new Response(Response::CODE_COMMON_MISS_PARAM, null, null, ['param' => implode(',', $notfounds)]);
        }

        $tran = \Yii::$app->db->beginTransaction();
        try {
            //校验数据
            $params = $this->validateParams($params);
            // 创建/更新 用户
            $user = $this->saveUser($params);
            // 创建/更新品牌，为每个用户创建一个品牌
            $brand = $this->saveBrand($user, $params);
            $user->customer_id = $brand->id;
            $user->save(false, ['customer_id']);

            // 创建/更新老师，为每个用户创建一个老师
            $this->saveTeacher($user, $params);
            // 初始用户目录
            $this->saveCategory($user, $params);

            $tran->commit();

            // 同步完成
            return new Response(Response::CODE_COMMON_OK);
        } catch (Exception $ex) {
            $tran->rollBack();
            return new Response(Response::CODE_COMMON_SAVE_DB_FAIL, null, $ex->getTraceAsString());
        }
    }

    /**
     * 效验数据，转换省市区镇代码转换
     * @param type $params
     */
    private function validateParams($params) {
        /* 转换id */
        $params['id'] = md5('dailylesson_' . $params['id']);
        /* 把字符转换成对应id */
        $names = [];
        if (isset($params['province']))
            $names['province'] = trim($params['province']);
        if (isset($params['city']))
            $names['city'] = trim($params['city']);
        if (isset($params['district']))
            $names['district'] = trim($params['district']);
        if (isset($params['twon']))
            $names['twon'] = trim($params['twon']);

        $regions = $this->findRegion($names);

        foreach ($names as $name => $value) {
            $params[$name] = isset($regions[$value]) ? $regions[$value] : 0;
        }
        return $params;
    }

    private function findRegion($names) {
        $result = Region::find()
                ->select(['id', 'name'])
                ->where(['name' => $names])
                ->all();
        return ArrayHelper::map($result, 'name', 'id');
    }

    /**
     * 创建/更新 用户
     * 
     * @param array $params
     * 
     * @throws Exception
     */
    private function saveUser($params) {
        $user_id = $params['id'];
        $user = DailyLessonUser::findOne(['id' => $user_id, 'status' => DailyLessonUser::STATUS_ACTIVE]);
        if (!$user) {
            $user = new DailyLessonUser([
                'id' => $user_id,
                'type' => DailyLessonUser::TYPE_PARTNER,
                'from' => 'dailylesson',
            ]);
        }
        //$user->loadDefaultValues();
        $user->setAttributes($params);
        if ($user->validate() && $user->save()) {
            /* 创建/更新 用户配置 */
            $profile = UserProfile::findOne(['user_id' => $user->id]);
            if (!$profile) {
                $profile = new UserProfile(['user_id' => $user->id]);
                $profile->loadDefaultValues();
            }
            $profile->setAttributes($params);
            if (!$profile->save()) {
                throw new Exception(implode("", $profile->getErrorSummary(true)));
            }
            return $user;
        } else {
            throw new Exception(implode("", $user->getErrorSummary(true)));
        }
    }

    /**
     * 创建/更新 品牌，为每个用户创建一个品牌
     * 
     * @param DailyLessonUser $user
     * @param array $params
     * @return Customer
     * 
     * @throws Exception
     */
    private function saveBrand($user, $params) {
        $is_new = false;
        $brand = Customer::findOne(['id' => md5($user->id)]);
        $brandAdmin;
        if (!$brand) {
            $is_new = true;
            $brand = new Customer([
                'id' => md5($user->id),
                'level' => Customer::LEVEL_1,
                'des' => '每日一课个人品牌',
                'expire_time' => strtotime("+2 years"),
                'renew_time' => time(),
            ]);
            /*
             * 设定当前用户为主管理员
             */
            $brandAdmin = new CustomerAdmin([
                'customer_id' => $brand->id,
                'user_id' => $user->id,
                'level' => CustomerAdmin::MAIN,
                'created_by' => $user->id,
            ]);
        }
        // 同步更新数据
        $brand->setAttributes([
            'name' => $user->nickname,
            'short_name' => $user->nickname,
            'company' => $user->profile->company,
            'province' => $user->profile->province,
            'city' => $user->profile->city,
            'district' => $user->profile->district,
            'twon' => $user->profile->twon,
            'address' => $user->profile->address,
        ]);

        if ($brand->validate() && $brand->save() && (!isset($brandAdmin) || $brandAdmin->save(false))) {
            if ($is_new) {
                $brand->status = Customer::STATUS_ACTIVE;
                $brand->save(false, ['status']);
            }
            return $brand;
        } else {
            throw new Exception(implode("", $brand->getErrorSummary(true)));
        }
    }

    /**
     * 创建/更新 老师
     * 
     * @param DailyLessonUser $user
     * @param array $params
     * 
     * @throws Exception
     */
    private function saveTeacher($user, $params) {
        $teacher_id = md5("{$user->id}_teacher");
        $teacher = Teacher::findOne(['id' => $teacher_id, 'created_by' => $user->id]);
        if (!$teacher) {
            $teacher = new Teacher([
                'id' => $teacher_id,
                'created_by' => $user->id,
                'customer_id' => $user->customer_id,
            ]);
        }
        $teacher->setAttributes([
            'name' => $user->nickname,
            'sex' => $user->sex,
            'avatar' => $user->avatar,
            'job_title' => $user->profile->job_title,
        ]);

        if ($teacher->validate() && $teacher->save()) {
            
        } else {
            throw new Exception(implode("", $teacher->getErrorSummary(true)));
        }
    }

    /**
     * 初始用户目录
     * 
     * @param DailyLessonUser $user
     * @param array $params
     * 
     * @throws Exception
     */
    private function saveCategory($user, $params) {
        //暂时不作初始操作
    }

}
