<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\webuploader\actions;

use common\modules\webuploader\models\Uploadfile;
use common\modules\webuploader\models\UploadfileChunk;
use common\modules\webuploader\models\UploadResponse;
use common\utils\FfmpegUtil;
use Exception;
use Imagine\Image\ManipulatorInterface;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;
use yii\web\HttpException;

/**
 * 合并分片
 * 1、合并分片为一个文件
 * 2、移动文件到upload文件
 * 3、删除分片数据和文件
 * 4、创建视频数据及缩略图
 * 5、创建文件数据
 *
 * @author Administrator
 */
class MergeChunksAction extends Action {

    public function run() {
        //应用
        $app_id = isset($_REQUEST["app_id"]) ? $_REQUEST["app_id"] : '';
        //应用web路径，默认会放本应用的web下，通过设置root_path可改变目标路径
        $root_path = isset($_REQUEST["root_path"]) ? $_REQUEST["root_path"] . '/' : '';
        $dir_path = isset($_REQUEST["dir_path"]) ? '/' . $_REQUEST["dir_path"] : '';
        $targetDir = $root_path . 'upload/webuploader/upload_tmp';
        $uploadDir = $root_path . 'upload/webuploader/upload' . $dir_path;
        // Create target dir
        $this->mkdir($targetDir);
        $this->mkdir($uploadDir);
        // Get a file name
        if (isset($_REQUEST["name"])) {
            $fileName = $_REQUEST["name"];
        }

        // Chunking might be enabled
        //文件md5
        $fileMd5 = isset($_REQUEST["fileMd5"]) ? $_REQUEST["fileMd5"] : '';
        //文件大小
        $fileSize = isset($_REQUEST["size"]) ? (integer) $_REQUEST["size"] : 0;
        //文件路径
        $uploadPath = $uploadDir . '/' . $fileMd5 . strrchr($fileName, '.');

        if ($fileMd5 == '') {
            return new UploadResponse(UploadResponse::CODE_COMMON_MISS_PARAM, null, null, ['param' => 'fileMd5']);
        } else {
            //查出所有分片记录
            $fileChunks = UploadfileChunk::find()->where(['file_id' => $fileMd5])->orderBy('chunk_index')->all();
            if ($fileChunks == null) {
                return new UploadResponse(UploadResponse::CODE_FILE_CHUNKS_NOT_FOUND);
            } else {
                /* @var $fileChunk UploadfileChunk  */
                $unFoundChunks = [];
                foreach ($fileChunks as $fileChunk) {
                    if (!file_exists($fileChunk->chunk_path)) {
                        $unFoundChunks [] = $fileChunk->chunk_path;
                        return new UploadResponse(UploadResponse::CODE_CHUNK_NOT_FOUND, null, null, ['chunkPath' => $fileChunk->chunk_path]);
                    }
                }
                //删除无用分片数据
                UploadfileChunk::deleteAll(['chunk_path' => $unFoundChunks]);

                if (!$out = @fopen($uploadPath, "wb")) {
                    return new UploadResponse(UploadResponse::CODE_OPEN_OUPUT_STEAM_FAIL);
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
                $makeThumb = isset($_REQUEST["makeThumb"]) ? (integer) $_REQUEST["makeThumb"] : 1;
                $thumbPath = '';
                if (false && $makeThumb) {
                    try {
                        $thumbPath = $this->createThumb(
                                $uploadPath, 
                                ArrayHelper::getValue($_REQUEST, 'thumbWidth', 128), 
                                ArrayHelper::getValue($_REQUEST, 'thumbHeight', null), 
                                ArrayHelper::getValue($_REQUEST, 'thumbMode', ManipulatorInterface::THUMBNAIL_OUTBOUND));
                    } catch (Exception $ex) {
                        Yii::error('fail make thumb!' . $ex->getMessage());
                    }
                }
                /**
                 * 记录视频 width,height,duration,level,bitrate
                 */
                $file_media_info = [];
                if (false && in_array(strtolower(pathinfo($uploadPath)['extension']), ['mp4', 'flv', 'wmv', 'mov', 'avi', 'mpg', 'rmvb', 'rm', 'mkv'])) {
                    try {
                        $file_media_info = FfmpegUtil::getVideoInfoByUfileId($uploadPath);
                    } catch (Exception $e) {
                        
                    };
                }

                /*
                 * 写入数据库
                 */
                $dbFile = new Uploadfile(array_merge($file_media_info, ['id' => $fileMd5]));
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
                    //上传到OSS
                    $result = $dbFile->uploadOSS();
                    if(!$result['success']){
                        return new UploadResponse(UploadResponse::CODE_UPLOAD_OSS_FAIL, null, $result['msg']);
                    }
                    
                    // Return Success JSON-RPC response
                    return new UploadResponse(UploadResponse::CODE_COMMON_OK, null, $dbFile->toArray());
                } else {
                    return new UploadResponse(UploadResponse::CODE_FILE_SAVE_FAIL, null, $dbFile->getErrorSummary(true));
                }
            }
        }
        return new UploadResponse(UploadResponse::CODE_COMMON_UNKNOWN);
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
            return "imgs/upload_filetype_icons/" . $this->getExt($fileinfo['extension']) . '.png';
        }
        $thumbpath = $fileinfo['dirname'] . '/thumbs/' . $fileinfo['filename'] . '.jpg?rand=' . rand(0, 1000);
        $this->mkdir($fileinfo['dirname'] . '/thumbs/');
        Image::$thumbnailBackgroundColor = '000';
        switch ($type) {
            case 'video':
                //创建视频缩略图
                //先截屏视频，再创建缩略图
                $filepath = FfmpegUtil::createVideoImageByUfileId($fileinfo['filename'], $filepath, $fileinfo['dirname'] . '/thumbs/');
                break;
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
    /*
      private function md5($file) {
      $fragment = 65536;
      $rh = fopen($file, 'rb');
      $size = filesize($file);
      $part1 = fread($rh, $size > $fragment ? $fragment : $size);
      fclose($rh);
      return md5($part1);
      }
     */
}
