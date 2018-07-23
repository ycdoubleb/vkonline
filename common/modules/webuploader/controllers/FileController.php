<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\modules\webuploader\controllers;

use common\modules\webuploader\actions\DownloadAction;
use yii\web\Controller;

/**
 * Description of FileController
 *
 * @author Administrator
 */
class FileController extends Controller{
    public function actions() {
        return array_merge(parent::actions(),[
            'download' => ['class' => DownloadAction::class],
        ]);
    }
}
