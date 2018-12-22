<?php

namespace apiend\modules\v1\controllers;

use apiend\controllers\ApiController;
use apiend\modules\v1\actions\user_category\GetAudioDetailAction;
use apiend\modules\v1\actions\user_category\GetCategoryDetailAction;
use apiend\modules\v1\actions\user_category\GetCategoryListAction;
use apiend\modules\v1\actions\user_category\GetDocumentDetailAction;
use apiend\modules\v1\actions\user_category\GetImageDetailAction;
use apiend\modules\v1\actions\user_category\GetMediaDetailAction;
use apiend\modules\v1\actions\user_category\GetVideoDetailAction;
use apiend\modules\v1\actions\user_category\SearchAction;
use apiend\modules\v1\actions\user_category\SearchAudioAction;
use apiend\modules\v1\actions\user_category\SearchDocumentAction;
use apiend\modules\v1\actions\user_category\SearchImageAction;
use apiend\modules\v1\actions\user_category\SearchVideoAction;

/**
 * 用户目录 API
 *
 * @author Administrator
 */
class UserCategoryController extends ApiController{
    public function behaviors() {
        $behaviors = parent::behaviors();
        /* 设置不需要令牌认证的接口 */
        $behaviors['authenticator']['optional'] = [
            //'check-phone-registered',
        ];
        $behaviors['verbs']['actions'] = [
            'get-category-list' =>                          ['get'],
            'search' =>                                     ['get'],
            'get-category-detail' =>                        ['get'],
            'get-media-detail' =>                           ['get'],
            'search-video' =>                               ['get'],
            'get-video-detail' =>                           ['get'],
            'search-audio' =>                               ['get'],
            'get-audio-detail' =>                           ['get'],
            'get-document-detail' =>                        ['get'],
            'search-image' =>                               ['get'],
            'get-image-detail' =>                           ['get'],
        ];
        return $behaviors;
    }

    /**
     * {@inheritdoc}
     */
    public function actions() {
        return [
            'get-category-list' =>                          ['class' => GetCategoryListAction::class],
            'search' =>                                     ['class' => SearchAction::class],
            'get-category-detail' =>                        ['class' => GetCategoryDetailAction::class],
            'get-media-detail' =>                           ['class' => GetMediaDetailAction::class],
            'search-video' =>                               ['class' => SearchVideoAction::class],
            'get-video-detail' =>                           ['class' => GetVideoDetailAction::class],
            'search-audio' =>                               ['class' => SearchAudioAction::class],
            'get-audio-detail' =>                           ['class' => GetAudioDetailAction::class],
            'search-document' =>                            ['class' => SearchDocumentAction::class],
            'get-document-detail' =>                        ['class' => GetDocumentDetailAction::class],
            'search-image' =>                               ['class' => SearchImageAction::class],
            'get-image-detail' =>                           ['class' => GetImageDetailAction::class],
        ];
    }
}
