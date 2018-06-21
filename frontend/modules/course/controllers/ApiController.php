<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace frontend\modules\course\controllers;

use common\components\BaseApiController;
use frontend\modules\course\actions\AddCommentAction;
use frontend\modules\course\actions\AddCommentPraiseAction;
use frontend\modules\course\actions\AddFavoriteAction;
use frontend\modules\course\actions\DelFavoriteAction;
use frontend\modules\course\actions\GetCommentAction;
use frontend\modules\course\actions\GetPlayRankAction;
use frontend\modules\course\actions\GetRecommendAction;
use frontend\modules\course\actions\SearchCourseAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

/**
 * Description of ApiController
 *
 * @author Administrator
 */
class ApiController extends BaseApiController  {
    
    //public $enableCsrfValidation = false;
    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'search-course' => ['get'],
                    'add-favorite' => ['get'],
                    'del-favorite' => ['get'],
                    'add-comment' => ['post'],
                    'add-comment-praise' => ['post'],
                    'get-play-rank' => ['get'],
                ],
            ],
        ];
    }
    
    public function actions() {
        return [
            'search-course' =>['class' => SearchCourseAction::class],
            'add-favorite' =>['class' => AddFavoriteAction::class],
            'del-favorite' =>['class' => DelFavoriteAction::class],
            'add-comment' =>['class' => AddCommentAction::class],
            'get-comment' =>['class' => GetCommentAction::class],
            'add-comment-praise' =>['class' => AddCommentPraiseAction::class],
            'get-recommend' =>['class' => GetRecommendAction::class],
            'get-play-rank' =>['class' => GetPlayRankAction::class],
        ];
    }
}
