<?php

namespace common\components\aliyuncs;

use OSS\Core\OssException;
use OSS\Core\OssUtil;
use OSS\OssClient;
use Yii;
use yii\base\Component;

/**
 * Oss 服务
 * 上传文件服务 
 * 
 * @property OssClient $ossClient
 *
 * @author Administrator
 */
class OssService extends Component {

    private $ossClient;

    public function __construct($config = array()) {
        parent::__construct($config);

        /* 初始OSS */
        $accessKeyId = Yii::$app->params['aliyun']['accessKeyId'];                 //获取阿里云账号的accessKeyId
        $accessKeySecret = Yii::$app->params['aliyun']['accessKeySecret'];         //获取阿里云账号的accessKeySecret

        $endpoint = Yii::$app->params['aliyun']['oss']['endPoint-internal'];                //获取阿里云oss的endPoint
        $this->ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint);           //实例化OssClient对象
    }

    /**
     * multipart上传统一封装，从初始化到完成multipart，以及出错后中止动作
     *
     * @param string $object object名称
     * @param string $file 需要上传的本地文件的路径
     * @param array $options Key-Value数组
     * @return null
     * @throws OssException
     */
    public function multiuploadFile($object, $file, $options = null) {
        $bucket = Yii::$app->params['aliyun']['oss']['bucket-input'];
        return $this->ossClient->multiuploadFile($bucket, $object, $file, $options);
    }

    /**
     * multipart上传统一封装，从初始化到完成multipart，以及出错后中止动作
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param string $file 需要上传的本地文件的路径
     * @param array $options Key-Value数组
     * @return null
     * @throws OssException
     */
    public function customeMultiuploadFile($bucket, $object, $file, $options = null) {

        $ossClient = $this->ossClient;
        /**
         *  step 1. 初始化一个分块上传事件, 也就是初始化上传Multipart, 获取upload id
         */
        try {
            $uploadId = $ossClient->initiateMultipartUpload($bucket, $object);
        } catch (OssException $e) {
            var_dump(__FUNCTION__ . ": initiateMultipartUpload FAILED\n");
            var_dump($e->getMessage() . "\n");
            return;
        }
        print(__FUNCTION__ . ": initiateMultipartUpload OK" . "\n");
        /*
         * step 2. 上传分片
         */
        $partSize = 5 * 1024 * 1024;
        $uploadFile = $file;
        $uploadFileSize = filesize($uploadFile);

        $t1 = microtime(true);
        $pieces = $ossClient->generateMultiuploadParts($uploadFileSize, $partSize);
        $t2 = microtime(true);
        var_dump('generateMultiuploadParts: ' . ($t2 - $t1));

        $responseUploadPart = array();
        $uploadPosition = 0;
        $isCheckMd5 = true;
        foreach ($pieces as $i => $piece) {
            $fromPos = $uploadPosition + (integer) $piece[$ossClient::OSS_SEEK_TO];
            $toPos = (integer) $piece[$ossClient::OSS_LENGTH] + $fromPos - 1;
            $upOptions = array(
                $ossClient::OSS_FILE_UPLOAD => $uploadFile,
                $ossClient::OSS_PART_NUM => ($i + 1),
                $ossClient::OSS_SEEK_TO => $fromPos,
                $ossClient::OSS_LENGTH => $toPos - $fromPos + 1,
                $ossClient::OSS_CHECK_MD5 => $isCheckMd5,
            );
            if ($isCheckMd5) {
                $t1 = microtime(true);
                $contentMd5 = OssUtil::getMd5SumForFile($uploadFile, $fromPos, $toPos);
                $t2 = microtime(true);
                var_dump("getMd5SumForFile: {$i} = $contentMd5 time=" . ($t2 - $t1));
                $upOptions[$ossClient::OSS_CONTENT_MD5] = $contentMd5;
            }
            //2. 将每一分片上传到OSS
            try {
                //$responseUploadPart[] = $ossClient->uploadPart($bucket, $object, $uploadId, $upOptions);
            } catch (OssException $e) {
                var_dump(__FUNCTION__ . ": initiateMultipartUpload, uploadPart - part#{$i} FAILED\n");
                var_dump($e->getMessage() . "\n");
                return;
            }
            var_dump(__FUNCTION__ . ": initiateMultipartUpload, uploadPart - part#{$i} OK\n");
        }
        $uploadParts = array();
        foreach ($responseUploadPart as $i => $eTag) {
            $uploadParts[] = array(
                'PartNumber' => ($i + 1),
                'ETag' => $eTag,
            );
        }
        return;
        /**
         * step 3. 完成上传
         */
        try {
            $ossClient->completeMultipartUpload($bucket, $object, $uploadId, $uploadParts);
        } catch (OssException $e) {
            var_dump(__FUNCTION__ . ": completeMultipartUpload FAILED\n");
            var_dump($e->getMessage() . "\n");
            return;
        }
        var_dump(__FUNCTION__ . ": completeMultipartUpload OK\n");
    }
    
     /**
     * 上传内存中的内容
     *
     * @param string $object objcet名称
     * @param string $content 上传的内容
     * @param array $options
     * @return null
     */
     public function putObject($object, $content, $options) {
        $bucket = Yii::$app->params['aliyun']['oss']['bucket-input'];
        return $this->ossClient->putObject($bucket, $object, $content, $options);
    }

    /**
     * 获得Object内容
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param array $options 该参数中必须设置ALIOSS::OSS_FILE_DOWNLOAD，ALIOSS::OSS_RANGE可选，可以根据实际情况设置；如果不设置，默认会下载全部内容
     * @return string
     */
    public function getObject($bucket, $object, $options = null){
        return $this->ossClient->getObject($bucket, $object, $options);
    }
    
    /**
     * 获取输入文件Object
     *
     * @param string $object object名称
     * @param string $options 具体参考SDK文档
     * @return array
     */
    public function getInputObject($object, $options = null) {
        return $this->getObject(Yii::$app->params['aliyun']['oss']['bucket-input'], $object, $options);
    }

    /**
     * 获取输出文件Object
     *
     * @param string $object object名称
     * @param string $options 具体参考SDK文档
     * @return array
     */
    public function getOutputObject($object, $options = null) {
        return $this->getObject(Yii::$app->params['aliyun']['oss']['bucket-output'], $object, $options);
    }

    /**
     * 获取Object的Meta信息
     *
     * @param string $bucket bucket名称
     * @param string $object object名称
     * @param string $options 具体参考SDK文档
     * @return array
     */
    public function getObjectMeta($bucket, $object, $options = null) {
        return $this->ossClient->getObjectMeta($bucket, $object, $options);
    }

    /**
     * 获取输入文件Object的Meta信息
     *
     * @param string $object object名称
     * @param string $options 具体参考SDK文档
     * @return array
     */
    public function getInputObjectMeta($object, $options = null) {
        return $this->getObjectMeta(Yii::$app->params['aliyun']['oss']['bucket-input'], $object, $options);
    }

    /**
     * 获取输出文件Object的Meta信息
     *
     * @param string $object object名称
     * @param string $options 具体参考SDK文档
     * @return array
     */
    public function getOutputObjectMeta($object, $options = null) {
        return $this->getObjectMeta(Yii::$app->params['aliyun']['oss']['bucket-output'], $object, $options);
    }
    
    /**
     * 删除多个文件
     * @param array $objects
     */
    public function deleteObjects($objects){
        return $this->ossClient->deleteObjects(Yii::$app->params['aliyun']['oss']['bucket-output'], $objects );
    }
    
    /**
     * 删除单个文件
     * @param string $object
     */
    public function deleteObject($object){
        return $this->ossClient->deleteObject(Yii::$app->params['aliyun']['oss']['bucket-output'], $object);
    }

}
