<?php

namespace frontend\OAuths\qqAPI\core;

use Yii;

/* PHP SDK
 * @version 2.0.0
 * @author connect@qq.com
 * @copyright © 2013, Tencent Corporation. All rights reserved.
 */

class Recorder{
    public static $qqConfig = 'qqLogin';
    private static $data;
    private $inc;
    private $error;

    public function __construct(){
        $this->error = new ErrorCase();
       
        //-------读取配置文件
        //$incFileContents = file(dirname(dirname(__FILE__))."/comm/inc.php");
        //$incFileContents = $incFileContents[1];
        //$this->inc = json_decode($incFileContents);
        
        //-------获取配置信息（从frontend/config/params.php 里面获取）
        $qqConfig = json_encode(Yii::$app->params[self::$qqConfig]); 
        $this->inc = json_decode($qqConfig);    //保证得到的结果为对象object
        if(empty($this->inc)){
            $this->error->showError("20001");
        }

        if(empty($_SESSION['QC_userData'])){
            self::$data = array();
        }else{
            self::$data = $_SESSION['QC_userData'];
        }
    }

    public function write($name,$value){
        self::$data[$name] = $value;
        $_SESSION['QC_userData'][$name] = $value;
    }

    public function read($name){
        if(empty(self::$data[$name])){
            return null;
        }else{
            return self::$data[$name];
        }
    }

    public function readInc($name){
        if(empty($this->inc->$name)){
            return null;
        }else{
            return $this->inc->$name;
        }
    }

    public function delete($name){
        unset(self::$data[$name]);
    }

    function __destruct(){
        $_SESSION['QC_userData'] = self::$data;
    }
}
