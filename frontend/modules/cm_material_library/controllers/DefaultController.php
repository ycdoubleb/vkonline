<?php

namespace frontend\modules\cm_material_library\controllers;

use common\utils\ApiService;
use Yii;
use yii\web\Controller;

/**
 * Default controller for the `cm_material_library` module
 */
class DefaultController extends Controller {

    private $api_server = "";

    public function init() {
        ApiService::init(Yii::$app->params['mediacloud']['encryption']);
        $this->api_server = Yii::$app->params['mediacloud']['api_server'];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex() {
        /*
        var_dump($this->getMediaType());
        var_dump($this->getDirDetail());
        var_dump($this->getDirDetail('192'));
        var_dump($this->searchMedia());
        var_dump($this->getMediaDetail('1'));
        * */
        $medias = $this->searchMedia()['data']['list'];
        foreach($medias as &$media){
            $media['url'] = base64_encode($media['url']);
        }
        //var_dump($medis);exit;
        return $this->render('index',['medias' => $medias]);
    }

    /**
     * 获取素材库ID
     */
    private function getMediaLibraryID() {
        //从config缓存读取 mediacloud_cm_library_id
        return 2;
    }

    /**
     * 获取素材类型
     */
    private function getMediaType() {
        return ApiService::get("{$this->api_server}/v1/media-type/list", []);
    }

    /**
     * 获取目录详情
     */
    private function getDirDetail($dir_id = 0) {
        return ApiService::get("{$this->api_server}/v1/dir/get-detail", ['dir_id' => $dir_id, 'category_id' => $this->getMediaLibraryID()]);
    }

    /**
     * 搜索素材
     */
    private function searchMedia($keyword = "", $dir_id = 0, $type_id = "", $page = 1, $limit = 20, $recursive = 1) {
        return ApiService::get("{$this->api_server}/v1/media/search", [
                    'dir_id' => $dir_id,
                    'category_id' => $this->getMediaLibraryID(),
                    'keyword' => $keyword,
                    'type_id' => $type_id,
                    'limit' => $limit,
                    'page' => $page,
                    'recursive' => $recursive,
        ]);
    }

    /**
     * 获取素材详情
     */
    private function getMediaDetail($media_id) {
        return ApiService::get("{$this->api_server}/v1/media/get-detail", ['media_id' => $media_id]);
    }

}
