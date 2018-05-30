<?php

namespace common\modules\webuploader\controllers;

use common\modules\webuploader\models\Uploadfile;
use common\modules\webuploader\models\UploadfileChunk;
use common\utils\FfmpegUtil;
use Imagine\Image\ManipulatorInterface;
use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Default controller for the `webuploader` module
 */
class DefaultController extends Controller {

    public $enableCsrfValidation = false;

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex() {

        return $this->render('index');
    }

    /**
     * 获取文件列表
     */
    public function actionGetFiles($file_num = 10) {
        \Yii::$app->getResponse()->format = 'json';
        $results = Uploadfile::find()->limit($file_num)->orderBy('created_at desc')->asArray()->all();
        return [
            'code' => 0,
            'data' => $results,
        ];
    }

    public function beforeAction($action) {
        if (parent::beforeAction($action)) {

            header("Access-Control-Allow-Origin:*");
            header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");
            header("Access-Control-Allow-Methods: GET, POST, PUT,DELETE");
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");

            // Support CORS
            // header("Access-Control-Allow-Origin: *");
            // other CORS headers if any...
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                exit; // finish preflight CORS requests here
            }
            if (!empty($_REQUEST['debug'])) {
                $random = rand(0, intval($_REQUEST['debug']));
                if ($random === 0) {
                    header("HTTP/1.0 500 Internal Server Error");
                    exit;
                }
            }
        };
        return parent::beforeAction($action);
    }
    
    /**
     * 创建外链地址
     * 传外链地址，分析得详细数据返回
     * @param string $path 视频路径
     */
    public function actionUploadLink($video_path){
        Yii::$app->response->format = 'json';
        $authUrl = "http://eefile.gzedu.com/video/getVideoInfoByUrl.do?formMap.VIDEO_URL={$video_path}";
        //调用api获取视频详细数据
        $curl = new Curl();
        try{
            $response = simplexml_load_string($curl->get($authUrl));
            //获取不成功返回失败信息
            if((string)$response->CODE != 200){
                return [
                    'code' => (string)$response->CODE,
                    'mes' => (string)$response->MESSAGE,
                ];
            }
            //附件数据
            $dbFile = Uploadfile::findOne(['id' => (string)$response->VIDEO_ID]);
            if($dbFile == null)
                $dbFile = new Uploadfile(['id' => (string)$response->VIDEO_ID]);     //视频ID、md5_ID
            $dbFile->name = (string)$response->VIDEO_NAME;                       //视频名
            $dbFile->path = $video_path;                                               //视频路径
            $dbFile->is_link = 1;           //设置为外链
            $dbFile->del_mark = 0;          //重置删除标志
            $dbFile->created_by = Yii::$app->user->id;
            $dbFile->thumb_path = (string)$response->VIDEO_IMG;                  //视频截图
            $dbFile->size = (string)$response->VIDEO_SIZE;                       //视频大小b         
            //源文件数据
            $source = [
                'source_level' => $this->getVideoLevel(explode('x', (string)$response->VIDEO_RESOLUTION)[1]),     //视频质量等级
                'source_wh' => (string)$response->VIDEO_RESOLUTION,                 //视频分辨率
                'source_bitrate' => floatval($response->VIDEO_BIT_RATE)*1000,       //码率
                'source_duration' => floatval($response->VIDEO_TIME)/1000,          //视频长度
                'source_is_link' => 1,
            ];
            if ($dbFile->save()) {
                return [
                    'code' => 200,
                    'mes' => '',
                    'data' => [
                        'dbFile' => $dbFile->toArray(),
                        'source' => $source,
                    ]
                ];
            }
        } catch (\Exception $ex) {
            return [
                'code' => 500,
                'mes' => $ex->getMessage(),
                'data' => $ex->getTraceAsString(),
            ];
        }
    }

    /**
     * 上传文件
     */
    public function actionUpload() {
        /**
         * upload.php
         *
         * Copyright 2013, Moxiecode Systems AB
         * Released under GPL License.
         *
         * License: http://www.plupload.com/license
         * Contributing: http://www.plupload.com/contributing
         */
        // Make sure file is not cached (as it happens for example on iOS devices)
        // 5 minutes execution time
        @set_time_limit(5 * 60);
        // Uncomment this one to fake upload time
        // usleep(5000);
        // Settings
        // $targetDir = ini_get("upload_tmp_dir") . DIRECTORY_SEPARATOR . "plupload";
        //应用web路径，默认会放本应用的web下，通过设置root_path可改变目标路径
        $root_path = isset($_REQUEST["root_path"]) ? $_REQUEST["root_path"] . '/' : '';
        $dir_path = isset($_REQUEST["dir_path"]) ? '/' . $_REQUEST["dir_path"] : '';
        $targetDir = $root_path . 'upload/webuploader/upload_tmp';
        $uploadDir = $root_path . 'upload/webuploader/upload' . $dir_path;
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds
        // Create target dir
        $this->mkdir($targetDir);
        $this->mkdir($uploadDir);
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }

        // Chunking might be enabled
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
        //分片md5和文件md5
        $chunkMd5 = isset($_REQUEST["chunkMd5"]) ? $_REQUEST["chunkMd5"] : '';
        $fileMd5 = isset($_REQUEST["fileMd5"]) ? $_REQUEST["fileMd5"] : '';

        $filePath = $targetDir . '/' . $fileMd5;
        $uploadPath = $uploadDir . '/' . $fileName;
        /* @var $fileChunk UploadfileChunk 分片模型 */
        $fileChunk;
        //检查分片是否上传过
        if ($chunkMd5 != '') {
            $fileChunk = UploadfileChunk::findOne(['chunk_id' => $chunkMd5]);
            if ($fileChunk != null) {
                //分片上传过
                die('{"jsonrpc" : "2.0", "result" : ' . $chunkMd5 . ', "id" : "id"}');
            }
        }
        // Remove old temp files
        if ($cleanupTargetDir) {
            if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . '/' . $file;
                // If temp file is current file proceed to the next
                if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                    continue;
                }
                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                    @unlink($tmpfilePath);
                }
            }
            closedir($dir);
        }
        // Open temp file
        if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }
        if (!empty($_FILES)) {
            if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
            }
            // Read binary input stream and append it to temp file
            if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        } else {
            if (!$in = @fopen("php://input", "rb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
            }
        }
        while ($buff = fread($in, 4096)) {
            fwrite($out, $buff);
        }
        @fclose($out);
        @fclose($in);
        rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");

        //保存记录分片数据
        $fileChunk = new UploadfileChunk(['chunk_id' => $chunkMd5, 'file_id' => $fileMd5, 'chunk_path' => "{$filePath}_{$chunk}.part", 'chunk_index' => $chunk]);
        $fileChunk->save();
        // Return Success JSON-RPC response
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    /**
     * 上传文件前检查，通过md5判断文件有没有上传过，或者在上传过程中断了
     */
    public function actionCheckFile() {
        if (!isset($_REQUEST['fileMd5'])) {
            die('{"jsonrpc" : "2.0", "error" : {"code": 200, "message": "fileMd5 不能为空!"}, "id" : "id"}');
        }
        $fileMd5 = $_REQUEST['fileMd5'];
        $dbFile = Uploadfile::findOne(['id' => $fileMd5, 'is_del' => 0]);
        if ($dbFile) {
            die('{"jsonrpc" : "2.0", "result" : ' . json_encode($dbFile->toArray()) . ', "id" : "id", "exist": 1}');
        } else {
            $fileChunks = ArrayHelper::map(UploadfileChunk::find()->select(['chunk_id', 'chunk_index'])->where(['file_id' => $fileMd5])->all(), 'chunk_id', 'chunk_index');
            if ($fileChunks != null && count($fileChunks) > 0) {
                //上传过程中断...
                die('{"jsonrpc" : "2.0", "result" : ' . json_encode($fileChunks) . ', "id" : "id", "uploading": 1}');
            }
        }
        //文件从未上传过
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    /**
     * 检查分片是否存在
     */
    public function actionCheckChunk() {
        // 5 minutes execution time
        @set_time_limit(5 * 60);
        //分片md5和文件md5
        $chunkMd5 = isset($_REQUEST["chunkMd5"]) ? intval($_REQUEST["chunkMd5"]) : '';
        /* @var $fileChunk UploadfileChunk 分片模型 */
        $fileChunk;
        //检查分片是否上传过
        if ($md5 != '') {
            $fileChunk = UploadfileChunk::findOne(['chunk_id' => $chunkMd5]);
            if ($fileChunk != null) {
                //分片上传过
                die('{"jsonrpc" : "2.0", "result" : ' . $chunkMd5 . ', "id" : "id", "exist": 1}');
            }
        }
        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
    }

    /**
     * 合并分片
     */
    public function actionMergeChunks() {
        //应用
        $app_id = isset($_REQUEST["app_id"]) ? $_REQUEST["app_id"] : '';
        //应用web路径，默认会放本应用的web下，通过设置root_path可改变目标路径
        $root_path = isset($_REQUEST["root_path"]) ? $_REQUEST["root_path"] . '/' : '';
        $dir_path = isset($_REQUEST["dir_path"]) ? '/' . $_REQUEST["dir_path"] : '';
        $targetDir = $root_path . 'upload/webuploader/upload_tmp';
        $uploadDir = $root_path . 'upload/webuploader/upload' . $dir_path;
        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds
        // Create target dir
        $this->mkdir($targetDir);
        $this->mkdir($uploadDir);
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        } elseif (!empty($_FILES)) {
            $fileName = $_FILES["file"]["name"];
        } else {
            $fileName = uniqid("file_");
        }
        // 5 minutes execution time
        @set_time_limit(5 * 60);
        // Chunking might be enabled
        //文件md5
        $fileMd5 = isset($_REQUEST["fileMd5"]) ? $_REQUEST["fileMd5"] : '';
        //文件大小
        $fileSize = isset($_REQUEST["size"]) ? (integer) $_REQUEST["size"] : 0;
        //文件路径
        $uploadPath = $uploadDir . '/' . $fileMd5 . strrchr($fileName, '.');

        if ($fileMd5 == '') {
            die('{"jsonrpc" : "2.0", "error" : {"code": 200, "message": "fileMd5 不能为空!"}, "id" : "id"}');
        } else {
            //查出所有分片记录
            $fileChunks = UploadfileChunk::find()->where(['file_id' => $fileMd5])->orderBy('chunk_index')->all();
            if ($fileChunks == null) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 201, "message": "' . "找不到对应分片！fileMd5=$fileMd5" . '"}, "id" : "id"}');
            } else {
                /* @var $fileChunk UploadfileChunk  */
                foreach ($fileChunks as $fileChunk) {
                    if (!file_exists($fileChunk->chunk_path)) {
                        die('{"jsonrpc" : "2.0", "error" : {"code": 203, "message": "分片文件不存在:' . $fileChunk->chunk_path . '"}, "id" : "id"}');
                    }
                }
                if (!$out = @fopen($uploadPath, "wb")) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                }
                if (flock($out, LOCK_EX)) {
                    //合并分片
                    foreach ($fileChunks as $fileChunk) {
                        if (!$in = @fopen($fileChunk->chunk_path, "rb")) {
                            break;
                        }
                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);
                        }
                        @fclose($in);
                        //@unlink($fileChunk->chunk_path);
                    }
                    flock($out, LOCK_UN);
                }
                @fclose($out);
                /**
                 * 创建缩略图
                 */
                $makeThumb = isset($_REQUEST["makeThumb"]) ? (integer) $_REQUEST["makeThumb"] : 0;
                $thumbPath = '';
                if ($makeThumb) {
                    try{
                        $thumbPath = $this->createThumb($uploadPath, 
                            ArrayHelper::getValue($_REQUEST, 'thumbWidth', 128), 
                            ArrayHelper::getValue($_REQUEST, 'thumbHeight', null), 
                            ArrayHelper::getValue($_REQUEST, 'thumbMode', ManipulatorInterface::THUMBNAIL_OUTBOUND));
                    } catch (\Exception $ex) {
                        Yii::error('fail make thumb!'.$ex->getMessage());
                    }
                }
                /*
                 * 写入数据库
                 */
                $dbFile = new Uploadfile(['id' => $fileMd5]);
                $dbFile->name = $fileName;
                $dbFile->path = $uploadPath;
                $dbFile->del_mark = 0;          //重置删除标志
                $dbFile->is_fixed = isset($_REQUEST['is_fixed']) ? $_REQUEST['is_fixed'] : 1;          //设置永久标志
                $dbFile->created_by = Yii::$app->user->id;
                $dbFile->thumb_path = $thumbPath;
                $dbFile->size = $fileSize;
                $dbFile->app_id = $app_id;
                if ($dbFile->save()) {
                    //删除临时文件
                    foreach ($fileChunks as $fileChunk) {
                        @unlink($fileChunk->chunk_path);
                    }
                    //删除数据库分片数据记录
                    Yii::$app->db->createCommand()->delete(UploadfileChunk::tableName(), ['file_id' => $fileMd5])->execute();
                    // Return Success JSON-RPC response
                    die('{"jsonrpc" : "2.0", "result" : ' . json_encode($dbFile->toArray()) . ', "id" : "id"}');
                } else {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 204, "message": "保存文件失败！' . json_encode($dbFile->errors) . '"}, "id" : "id"}');
                }
            }
        }
        die('{"jsonrpc" : "2.0", "error" : {"code": 209, "message": "未知错误"}, "id" : "id"}');
    }

    /**
     * 下载文件
     * @param type $file_id
     */
    public function actionDownload($file_id) {
        /* @var $file Uploadfile */
        $file = Uploadfile::findOne(['id' => $file_id, 'is_del' => 0]);
        if ($file) {
            $file->download_count ++;
            //保存
            $file->save();
            try {
                //$this->download($file->path, $file->name,true);
                Yii::$app->getResponse()->sendFile($file->path, $file->name);
            } catch (\Exception $ex) {
                throw new NotFoundHttpException($ex->getMessage());
            }
        } else {
            throw new NotFoundHttpException('文件不存在！');
        }
    }

    /** 下载 
     * @param String  $file   要下载的文件路径 
     * @param String  $name   文件名称,为空则与下载的文件名称一样 
     * @param boolean $reload 是否开启断点续传 
     */
    public function download($file, $name = '', $reload = false) {
        $_speed = 512;
        if (file_exists($file)) {
            if ($name == '') {
                $name = basename($file);
            }

            $fp = fopen($file, 'rb');
            $file_size = filesize($file);
            $ranges = $this->getRange($file_size);

            header('cache-control:public');
            header('content-type:application/octet-stream');
            header('content-disposition:attachment; filename=' . $name);

            if ($reload && $ranges != null) { // 使用续传  
                header('HTTP/1.1 206 Partial Content');
                header('Accept-Ranges:bytes');

                // 剩余长度  
                header(sprintf('content-length:%u', $ranges['end'] - $ranges['start']));

                // range信息  
                header(sprintf('content-range:bytes %s-%s/%s', $ranges['start'], $ranges['end'], $file_size));

                // fp指针跳到断点位置  
                fseek($fp, sprintf('%u', $ranges['start']));
            } else {
                header('HTTP/1.1 200 OK');
                header('content-length:' . $file_size);
            }

            while (!feof($fp)) {
                echo fread($fp, round($_speed * 1024, 0));
                ob_flush();
                //sleep(1); // 用于测试,减慢下载速度  
            }

            ($fp != null) && fclose($fp);
        } else {
            return '';
        }
    }

    /**
     * 创建文件缩略图，只会对视频和图片生成缩略图
     * @param string $filepath      文件路径
     * @param type $width           缩略图宽度
     * @param type $height          缩略图高度
     * @param type $mode            模式：outbound填满高宽，inset等比缩放
     * @return string   生成缩略图路径
     */
    private function createThumb($filepath, $width = 128, $height = null, $mode = ManipulatorInterface::THUMBNAIL_OUTBOUND) {
        //需要生成缩略图的文件
        $filter = [
            'video' => ['mp4', 'flv', 'wmv', 'mov', 'avi', 'mpg', 'rmvb', 'rm', 'mkv'],
            'image' => ['jpg', 'jpeg', 'png', 'gif'],
        ];

        $fileinfo = pathinfo($filepath);
        $type = '';
        foreach ($filter as $key => $filters) {
            if (in_array(strtolower($fileinfo['extension']), $filters)) {
                $type = $key;
                break;
            }
        }
        if ($type == '') {
            //其它文件不创建缩略图，返回''
            return "imgs/upload_filetype_icons/".$this->getExt($fileinfo['extension']).'.png';
        }
        $thumbpath = $fileinfo['dirname'] . '/thumbs/' . $fileinfo['filename'] . '.jpg';
        $this->mkdir($fileinfo['dirname'] . '/thumbs/');
        Image::$thumbnailBackgroundColor = '000';
        switch ($type) {
            case 'video':
                //创建视频缩略图
                //先截屏视频，再创建缩略图
                $filepath = FfmpegUtil::createVideoImageByUfileId($fileinfo['filename'], $filepath, $fileinfo['dirname'] . '/thumbs/');
            case 'image':
                //创建图片缩略图
                Image::thumbnail($filepath, $width, $height, $mode)->save($thumbpath);
                break;
        }
        return $thumbpath;
    }

    /**
     * 获取对应文件类型图标
     * @param suffix        文件后缀
     * @returns {string}
     * @private
     */
    private function getExt($suffix) {
        //无法生成缩略图的文件图标
        $exts = [
            'doc' => ['doc', 'docx'],
            'xls' => ['xls', 'xlsx'],
            'ppt' => ['ppt', 'pptx'],
            'ai' => ['ai'],
            'audio' => ['mp3', 'wma'],
            'gif' => ['gif'],
            'jpg' => ['jpg', 'jpeg'],
            'pdf' => ['pdf'],
            'psd' => ['psd'],
            'video' => ['mp4', 'avi', 'mpg', 'wmv', 'rmvb', 'rm', 'mov'],
            'zip' => ['zip', 'rar', 'tar', 'gz'],
        ];
        $ext = 'other';
        $suffix = strtolower($suffix);
        foreach ($exts as $key => $ext_arr) {
            if (in_array($suffix, $ext_arr)) {
                return $key;
            }
        }
        return $ext;
    }

    /** 获取header range信息 
     * 
     * 1、bytes=100-200     第100到第200字节
     * 2、bytes=-1000       最后的1000个字节
     * 3、bytes=500-        第500字节到文件末尾
     * @param  int   $file_size 文件大小 
     * @return Array 
     */
    private function getRange($file_size) {
        if (isset($_SERVER['HTTP_RANGE']) && !empty($_SERVER['HTTP_RANGE'])) {
            $range = $_SERVER['HTTP_RANGE'];
            $range = preg_replace('/[\s|,].*/', '', $range);
            $range = explode('-', substr($range, 6));
            if (count($range) < 2) {
                $range[1] = $file_size;
            }
            $range = array_combine(array('start', 'end'), $range);
            if (empty($range['start'])) {
                $range['start'] = 0;
            }
            if (empty($range['end'])) {
                $range['end'] = $file_size;
            }
            return $range;
        }
        return null; //['start' => 0,'end' => $file_size];
    }

    /**
     * 创建目录
     * @param string $path
     */
    private function mkdir($path) {
        if (!file_exists($path)) {
            if (!(@mkdir($path, 0777, true))) {
                throw new HttpException(500, '创建目录失败');
            }
        }
    }

    /**
     * 只读文件起始到指定字节数
     * @param type $file
     * @return type
     */
    private function md5($file) {
        $fragment = 65536;
        $rh = fopen($file, 'rb');
        $size = filesize($file);
        $part1 = fread($rh, $size > $fragment ? $fragment : $size);
        fclose($rh);
        return md5($part1);
    }

    /**
     * 
     * @param type $file
     * @return type
     */
    private function mymd5($file) {
        $fragment = 65536;
        $rh = fopen($file, 'rb');
        $size = filesize($file);
        $part1 = fread($rh, $fragment);
        fseek($rh, $size - $fragment);
        $part2 = fread($rh, $fragment);
        fclose($rh);
        return md5($part1 . $part2);
    }
    
    /**
     * 返回视频质量：1=480P 1=720P 2=1080P
     * @param integer $height   视频高度
     * @return integer
     */
    private function getVideoLevel($height) {
        $levels = [480, 720, 1080];
        foreach ($levels as $index => $level) {
            if ($height <= $level) {
                return $index + 1;
            }
        }
        return 3;
    }

}
