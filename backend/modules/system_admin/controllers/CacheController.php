<?php

namespace backend\modules\system_admin\controllers;

use Yii;
use yii\caching\Cache;
use yii\di\Instance;
use yii\web\Controller;

class CacheController extends Controller {

    public function actionIndex() {

        $dirs = [
            Yii::getAlias('@backend') . '/runtime/cache',
            Yii::getAlias('@backend') . '/web/assets',
            Yii::getAlias('@frontend') . '/runtime/cache',
            Yii::getAlias('@frontend') . '/web/assets',
            Yii::getAlias('@console') . '/runtime/cache',
        ];
        foreach ($dirs as $path) {
            $this->do_rmdir($path, false);
        }
        return $this->render('index');
    }

    /* 清空/删除 文件夹
     * @param string $dirname 文件夹路径
     * @param bool $self 是否删除当前文件夹
     * @return bool
     */

    private function do_rmdir($dirname, $self = true) {
        if (!file_exists($dirname)) {
            return false;
        }
        if (is_file($dirname) || is_link($dirname)) {
            return unlink($dirname);
        }
        $dir = dir($dirname);
        if ($dir) {
            while (false !== $entry = $dir->read()) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                $this->do_rmdir($dirname . '/' . $entry);
            }
        }
        $dir->close();
        $self && rmdir($dirname);
    }

}
