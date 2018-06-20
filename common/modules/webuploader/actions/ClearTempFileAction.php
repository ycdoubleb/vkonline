<?php

namespace common\modules\webuploader\actions;

/**
 * 删除临时文件
 *
 * @author Administrator
 */
class ClearTempFileAction {

    public function run($targetDir) {
        $maxFileAge = 24 * 3600; // Temp file age in seconds
        if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
            return new UploadResponse(UploadResponse::CODE_DIR_NOT_FOUND, null, null, ['path' => $targetDir]);
        }
        $paths = [];
        while (($file = readdir($dir)) !== false) {
            $tmpfilePath = $targetDir . '/' . $file;
            // Remove temp file if it is older than the max age and is not the current file
            if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                $paths [] = $tmpfilePath;
                @unlink($tmpfilePath);
            }
        }
        closedir($dir);
        UploadfileChunk::deleteAll(['chunk_path' => $paths]);
    }

}
