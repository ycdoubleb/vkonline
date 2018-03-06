<?php

namespace common\modules\rbac\controllers;

use common\models\System;
use Yii;
use yii\db\Query;
use yii\web\Controller;

class DefaultController extends Controller
{
    
    public $layout = 'basedata';
    
    public function actionIndex($is_make = false)
    {
        if($is_make){
            $file_path = Yii::getAlias('@common/modules').'/rbac/RbacName.php';
            $authItems = $this->getAuthItem();
            //unlink ($file_path);            //删除文件
            $myfile = fopen($file_path, "w");   //打开或创建文件
            $rbacName = '';
            foreach ($authItems as $key => $value) 
                if($value['description'] != null)
                    $rbacName .= "\r\n\t".'/** '.$value['description'].' */'."\r\n\t".'const '. str_replace($value['type'] == 1 ? 'R_' : 'P_', $value['type'] == 1 ? 'ROLE_' : 'PERMSSION_', strtoupper($value['name'])).' = "'.$value['name'].'";';
            //组装生成文件内容
            $format = '<?php'."\r\n".'namespace common\modules\rbac;'."\r\n".'class RbacName{'.$rbacName."\r\n".'}';

            fwrite($myfile, $format);     //写入文件内容
            
            return $this->redirect(['index']);
        }
        
        return $this->render('index');
    }
    
    /**
     * 获取角色/权限
     * @return array
     */
    public function getAuthItem()
    {
        $authItem = (new Query())
                ->select(['Auth_item.name', 'Auth_item.type', 'Auth_item.description'])
                ->from(['Auth_item' => 'ccoa_auth_item'])
                //->leftJoin(['System' => System::tableName()], 'System.id = Auth_item.system_id')
                ->all();
        
        return $authItem;
    }
}
