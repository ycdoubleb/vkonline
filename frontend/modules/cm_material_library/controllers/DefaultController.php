<?php

namespace frontend\modules\cm_material_library\controllers;

use common\components\aliyuncs\Aliyun;
use common\components\redis\RedisService;
use common\models\api\ApiResponse;
use common\models\Config;
use common\utils\ApiService;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Default controller for the `cm_material_library` module
 */
class DefaultController extends Controller 
{
    /**
     * redis 键名前缀
     * format：
     * cm_material:dir:{目录ID}: => {{obj }}
     * eg：cm_material:dir:0 => {"code":"0","msg":"OK","data":{
     *   "dir":{"id":"0","name":"\u6839\u76ee\u5f55","level":"0",
     *   "path":[{"id":"0","name":"\u6839\u76ee\u5f55"}]},
     *   "children":[{"id":"192","name":"\u4eba\u7269"},{"id":"191","name":"\u56fe\u6807"},{"id":"198","name":"\u5b66\u5386"}]
     *  }}
     */
    const REDIS_DIR_KEY = 'cm_material:dir:';
    
    /**
     * redis 键名前缀
     * format：
     * cm_material:type => {{obj }}
     * eg：cm_material:type=> {"code":"0","msg":"OK","data":[
     *  {"id":1,"name":"\u89c6\u9891","sign":"video"},
     *  {"id":2,"name":"\u97f3\u9891","sign":"audio"},
     *  {"id":3,"name":"\u56fe\u7247","sign":"image"},
     *  {"id":4,"name":"\u6587\u6863","sign":"document"}]}
     */
    const REDIS_TYPE_KEY = 'cm_material:type';
    
    private $api_server = "";

    public function init() 
    {
        ApiService::init(Yii::$app->params['mediacloud']['encryption']);
        $this->api_server = Yii::$app->params['mediacloud']['api_server'];
    }

    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $keyword = ArrayHelper::getValue($params, 'keyword');   //关键字
        $dir_id = ArrayHelper::getValue($params, 'dir_id');     //素材目录ID

        //目录详情
        $dirDetail = $this->getDirDetail($dir_id);
        //素材分类ID
        $mediaTypes = $this->getMediaType();
        $type_ids = ArrayHelper::getValue($params, 'type_id', $mediaTypes['type_id']);  //素材类型ID

        return $this->render('index', [
            'keyword' => $keyword,      //关键字
            'dirPath' => $dirDetail['dirPath'],     //当前位置
            'dirs' => $dirDetail['childrens'],      //选择过滤的目录条件
            'type_id' => $type_ids,                 //选中的素材类型ID
            'mediaType' => $mediaTypes['type_name'],//过滤的素材类型条件
        ]);
    }
    
    /**
     * 素材列表数据
     * @return ApiResponse
     */
    public function actionPageList()
    {
        $params = Yii::$app->request->queryParams;
        $keyword = ArrayHelper::getValue($params, 'keyword');       //关键字
        $dir_id = ArrayHelper::getValue($params, 'dir_id', 0);      //素材目录ID
        $type_id = ArrayHelper::getValue($params, 'type_id', []);   //素材类型ID
        $page = ArrayHelper::getValue($params, 'page');     //页数
        $limit = ArrayHelper::getValue($params, 'limit', 40);   //截取条数
        //素材分类ID
        $mediaTypes = $this->getMediaType();
        //素材信息
        $materialDatas = $this->searchMedia($keyword, $dir_id, implode(',', $type_id), $page, $limit);
        $medias = []; $totalCount = 0;
        if($materialDatas['code'] == 0){
            $medias = $materialDatas['data']['list'];
            $page = $materialDatas['data']['page'];
            $totalCount = $materialDatas['data']['total_count'];
        }

        //如果是ajax请求，返回json
        if(\Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            foreach($medias as &$media){
                $media['cover_url'] = !empty($media['cover_url']) ? $media['cover_url'] : Aliyun::absolutePath('static/imgs/notfound.png');
                $media['icon'] = $this->getTypeIcon($media['type_id'], $mediaTypes['type_sign']);
                $media['file_id'] = base64_encode($media['url']);
            }
            try
            { 
                $data = [
                    'result' => $medias, 
                    'page' => $page,
                    'totalCount' => $totalCount,
                ];
                return new ApiResponse(ApiResponse::CODE_COMMON_OK, '请求成功！', $data);
            }catch (Exception $ex) {
                return new ApiResponse(ApiResponse::CODE_COMMON_UNKNOWN, '请求失败::' . $ex->getMessage());
            }
        }
    }
    
    /**
     * 素材预览模态框
     * @param int $id   素材ID
     * @return type
     */
    public function actionPreview($id)
    {
        $mediaDetail = $this->getMediaDetail($id);

        return $this->renderAjax('preview', [
            'mediaDetail' => $mediaDetail,
        ]);

    }

    /**
     * 获取素材库ID
     */
    private function getMediaLibraryID()
    {
        //从config缓存读取 mediacloud_cm_library_id
        return 2;
    }

    /**
     * 获取素材类型
     */
    private function getMediaType() 
    {
        //$redis_key 
        //format = cm_material:type
        //过期时间
        $time_out = 24*60*60;
        $redis_key = self::REDIS_TYPE_KEY;
        if (!RedisService::getRedis()->exists($redis_key)) {
            $mediaTypes = ApiService::get("{$this->api_server}/v1/media-type/list", []);
            //不存在，添加一条记录
            RedisService::getRedis()->setex($redis_key, $time_out, json_encode($mediaTypes));
        } else {
            $mediaTypes = json_decode(RedisService::getRedis()->get($redis_key), true);
        }
        //处理素材类型
        $type_name = []; $type_id = []; $type_sign = [];
        if($mediaTypes['code'] == 0){
            foreach ($mediaTypes['data'] as $key => $mediaType){
                $type_name += [$mediaType['id'] => $mediaType['name']];
                $type_sign += [$mediaType['id'] => $mediaType['sign']];
                $type_id += [$key => $mediaType['id']];
            }
        }

        return [
            'type_name' => $type_name,
            'type_sign' => $type_sign,
            'type_id' => $type_id,
        ];
    }

    /**
     * 获取目录详情
     * @param integer $dir_id   目录ID
     * @return array
     */
    private function getDirDetail($dir_id = 0) 
    {
        //$redis_key 
        //format = cm_material:dir:{目录ID}: => {{obj }}
        //过期时间
        $time_out = 24*60*60;
        $redis_key = self::REDIS_DIR_KEY . "$dir_id";
        if (!RedisService::getRedis()->exists($redis_key)) {
            $dirDetail = ApiService::get("{$this->api_server}/v1/dir/get-detail", ['dir_id' => $dir_id, 'category_id' => $this->getMediaLibraryID()]);
            //不存在，添加一条记录
            RedisService::getRedis()->setex($redis_key, $time_out, json_encode($dirDetail));
        } else {
            $dirDetail = json_decode(RedisService::getRedis()->get($redis_key), true);
        }
        //处理目录详情
        $childrens = []; $dirPath = [];
        if($dirDetail['code'] == 0){
            foreach ($dirDetail['data']['children'] as $key => $children) {
                $childrens += [
                    $children['id'] => $children['name']
                ];
            }
            foreach ($dirDetail['data']['dir']['path'] as $key => $value) {
                $dirPath += [
                    $key => [
                        'id' => $value['id'],
                        'name' => $value['name']
                    ]
                ];
            }
        }
        
        return [
            'childrens' => $childrens,  //选择过滤的目录条件
            'dirPath' => $dirPath,      //当前位置
        ];
    }

    /**
     * 搜索素材
     * @param string $keyword    关键字
     * @param string $dir_id     目录ID
     * @param string $type_id    分类ID
     * @param integer $page      页数
     * @param integer $limit     获取条数
     * @param integer $recursive 是否递归
     * @return array
     */
    private function searchMedia($keyword = "", $dir_id = 0, $type_id = "", $page = 1, $limit = 20, $recursive = 1) 
    {
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
     * @param integer $media_id 素材ID
     * @return array
     */
    private function getMediaDetail($media_id) 
    {
        $materialDetail = ApiService::get("{$this->api_server}/v1/media/get-detail", ['media_id' => $media_id]);
        $mediaTypes = $this->getMediaType();
        if($materialDetail['code'] == 0){
            $mediaDetail = [
                'id' => $materialDetail['data']['id'],
                'type_sign' => $mediaTypes['type_sign'][$materialDetail['data']['type_id']],
                'name' => $materialDetail['data']['name'],
                'cover_url' => !empty($materialDetail['data']['cover_url']) ? $materialDetail['data']['cover_url'] : Aliyun::absolutePath('static/imgs/notfound.png'),
                'url' => $materialDetail['data']['url'],
                'tags' => $materialDetail['data']['tags'],
            ];
        }

        return $mediaDetail;
    }
    
    /**
     * 根据类型ID获取类型图标
     * @param integer $type_id  类型ID
     * @param array $type_signs 类型标识
     * @return string
     */
    protected function getTypeIcon($type_id, $type_signs)
    {
        //icon配置
        $iconConfig = (new Query())->select(['config_name', 'config_value'])
                ->from(['Config' => Config::tableName()])
                ->andFilterWhere(['like', 'config_name', 'cm_material_icon_'])
                ->all();
        
        $type_sign = $type_signs[$type_id];
        $icon = '';
        foreach ($iconConfig as $config){
            if($config['config_name'] == "cm_material_icon_" . $type_sign){
                $icon = $config['config_value'];
            }
        }
        
        return $icon;
    }

}
