<?php

namespace backend\modules\frontend_admin\controllers;

use common\models\User;
use common\models\vk\Customer;
use common\models\vk\Video;
use common\models\vk\VideoAttachment;
use common\modules\webuploader\models\Uploadfile;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Default controller for the `frontend_admin` module
 */
class DefaultController extends Controller
{
    public static $totalSize = 10995116277760;
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $model = new Customer();

        return $this->render('index',[
            'model' => $model,
            'customerInfo' => $this->getCustomerInfo(),
            'totalUser' => count(User::find()->all()),
            'totalSize' => self::$totalSize,
            'usedSpace' => $this->getUsedSpace(),
        ]);
    }
    
    /**
     * 获取客户的信息（地址等）
     * @return array
     */
    public function getCustomerInfo()
    {
        $customerInfo = (new Query())
                ->select(['name', 'address', 'X(location)', 'Y(location)'])
                ->from(['Customer' => Customer::tableName()])
                ->all();

        return $customerInfo;
    }
    
    /**
     * 查询已使用的空间
     * @return array
     */
    public function getUsedSpace()
    {
        $files = $this->findCustomerFile()->all();
        $videoFileIds = ArrayHelper::getColumn($files, 'source_id');        //视频来源ID
        $attFileIds = ArrayHelper::getColumn($files, 'file_id');            //附件ID
        $fileIds = array_filter(array_merge($videoFileIds, $attFileIds));   //合并
        
        $query = (new Query())->select(['SUM(Uploadfile.size) AS size'])
            ->from(['Uploadfile' => Uploadfile::tableName()]);
        
        $query->where(['Uploadfile.is_del' => 0]);
        $query->where(['Uploadfile.id' => $fileIds]);
        
        return $query->one();
    }
    
    /**
     * 查找客户关联的文件
     * @param string $id
     * @return Query
     */
    protected function findCustomerFile()
    {
        
        $query = (new Query())->select(['Video.source_id', 'Attachment.file_id'])
            ->from(['Customer' => Customer::tableName()]);
        
        $query->leftJoin(['Video' => Video::tableName()], '(Video.customer_id = Customer.id AND Video.is_del = 0 AND Video.is_ref = 0)');
        $query->leftJoin(['Attachment' => VideoAttachment::tableName()], '(Attachment.video_id = Video.id AND Attachment.is_del = 0)');
                
        $query->groupBy('Video.source_id');
        
        return $query;
    }
}
