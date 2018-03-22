<?php

namespace backend\models;

use spanjeta\modules\backup\helpers\MysqlBackup;
use Yii;
use const PHP_EOL;

/**
 * 拓展 MysqlBackup
 * 主要修改备份文件路径
 *
 * @author Administrator
 */
class SqlBackup extends MysqlBackup {

    /**
     * 获取指定表数据，拓展修改数据合并时分隔符为一个“;”
     * @param 表名 $tableName
     * @return boolean
     */
    public function getData($tableName) {
        $sql = 'SELECT * FROM ' . $tableName;
        $cmd = Yii::$app->db->createCommand($sql);
        $dataReader = $cmd->query();

        if ($this->fp)
            $this->writeComment('TABLE DATA ' . $tableName);
        foreach ($dataReader as $data) {
            $itemNames = array_keys($data);
            $itemNames = array_map("addslashes", $itemNames);
            $items = join('`,`', $itemNames);
            $itemValues = array_values($data);
            $itemValues = array_map("addslashes", $itemValues);
            $valueString = join("','", $itemValues);
            $valueString = "('" . $valueString . "'),";
            $values = "\n" . $valueString;

            if ($values != "") {
                $data_string = "INSERT INTO `$tableName` (`$items`) VALUES" . rtrim($values, ",") . ";" . PHP_EOL;
                if ($this->fp)
                    fwrite($this->fp, $data_string);
            }
        }

        if ($this->fp)
            fflush($this->fp);
        return true;
    }

    protected function getPath() {
        $this->_path = 'upload/db/';
        if (!file_exists($this->_path)) {
            @mkdir($this->_path, 0775, true);
        }
        return $this->_path;
    }

    protected function writeComment($string) {
        fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
        fwrite($this->fp, '-- ' . $string . PHP_EOL);
        fwrite($this->fp, '-- -------------------------------------------' . PHP_EOL);
    }

}
