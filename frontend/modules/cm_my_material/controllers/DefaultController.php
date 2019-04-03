<?php

namespace frontend\modules\cm_my_material\controllers;

use common\components\aliyuncs\Aliyun;
use common\models\api\ApiResponse;
use common\models\vk\TagRef;
use common\models\vk\Tags;
use common\models\vk\UserCategory;
use common\models\vk\Video;
use common\modules\webuploader\models\Uploadfile;
use common\utils\StringUtil;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

/**
 * Default controller for the `cm_my_material` module
 */
class DefaultController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        $params = Yii::$app->request->queryParams;
        $keyword = ArrayHelper::getValue($params, 'keyword');   //关键字
        $user_cat_id = ArrayHelper::getValue($params, 'user_cat_id');   //用户目录ID
        $type_ids = ArrayHelper::getValue($params, 'type_id', array_keys(Video::$typeMap));  //素材类型ID

        return $this->render('index', [
            'keyword' => $keyword,      //关键字
            'type_id' => $type_ids,
            'locationPathMap' => UserCategory::getUserCatLocationPath($user_cat_id),  //所属目录位置
            'userCategoryMap' => $user_cat_id == null ? UserCategory::getCatsByLevel() : UserCategory::getCatChildren($user_cat_id),    //返回所有目录结构
        ]);
    }
    
    /**
     * 素材列表数据
     * @return ApiResponse
     */
    public function actionPageList()
    {
        //素材数据
        $results = $this->searchMedia(Yii::$app->request->queryParams);
        $medias = []; $totalCount = 0;
        if(!empty($results)){
            $medias = $results['list'];
            $page = $results['page'];
            $totalCount = $results['total_count'];
        }
        $icons = [
            '1' => 'glyphicon glyphicon-facetime-video',
            '2' => 'glyphicon glyphicon-music',
            '3' => 'glyphicon glyphicon-picture',
            '4' => 'glyphicon glyphicon-file',
        ];
        //如果是ajax请求，返回json
        if(Yii::$app->request->isAjax){
            Yii::$app->getResponse()->format = 'json';
            //重设素材数据里面的元素值
            foreach($medias as &$media){
                $media['cat_path'] = $this->getCatLocationPath($media['user_cat_id']);
                $media['cover_url'] = $this->getMaterialCoverUrl($media);
                $media['icon'] = $icons[$media['type_id']];
                $media['url'] = Aliyun::absolutePath($media['oss_key']);
                $media['downlod_name'] = urlencode($media['name']);
                $media['file_name'] = urlencode($media['file_name']);
                $media['file_id'] = base64_encode(Aliyun::absolutePath($media['oss_key']));
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
        $mediaDetail['cover_url'] = $this->getMaterialCoverUrl($mediaDetail);   //重设封面图
        
        return $this->renderAjax('preview', [
            'mediaDetail' => $mediaDetail,
        ]);

    }
    
    /**
     * 查找素材
     * @param array $params         参数
     * @return array
     */
    protected function searchMedia($params)
    {        
        $keyword = ArrayHelper::getValue($params, 'keyword', '');       //关键字
        $recursive = !empty($keyword);    //是否递归
        $user_cat_id = ArrayHelper::getValue($params, 'user_cat_id');   //指定搜索目录，默认为根目录
        $limit = ArrayHelper::getValue($params, 'limit', 20);   //每页显示多个素材
        $page = ArrayHelper::getValue($params, 'page', 1);      //当前分页
        $type_id = ArrayHelper::getValue($params, 'type_id');   //指定要搜索的素材类型
        //参数处理
        $keyword = str_replace(['，', '、', ',', ' '], '|', $keyword);          //转换为 k|k|k 格式
        
        /**
         * 建立查询
         */
        $query = (new Query())
                ->select([ 'Material.id', 'Material.name', 'Material.type AS type_id', 'Uploadfile.thumb_path AS cover_url', 'Material.user_cat_id',
                    'Uploadfile.oss_key', 'Uploadfile.name AS file_name', 'Material.created_at', 'Uploadfile.size'])
                ->from(['Material' => Video::tableName()])
                ->leftJoin(['UserCategory' => UserCategory::tableName()], 'UserCategory.id = Material.user_cat_id')
                ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Material.file_id')
                ->leftJoin(['TagRef' => TagRef::tableName()], 'TagRef.object_id = Material.id')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->where(['Material.is_del' => 0, 'Uploadfile.is_del' => 0])
                ->andFilterWhere(['Material.type' => $type_id])
                ->groupBy(['Material.id']);
        //如果目录类型是共享类型则显示品牌下所有共享文件
        $query->andFilterWhere(['AND', 
            new Expression("IF(UserCategory.type=:type, (Material.customer_id=:customer_id AND UserCategory.type=:type), (Material.created_by=:created_by AND Material.customer_id=:customer_id))", [
                'type' => UserCategory::TYPE_SHARING, 
                'created_by' => Yii::$app->user->id,
                'customer_id' => Yii::$app->user->identity->customer_id
            ])
        ]);
        //-----------------------       
        // 目录过滤
        //-----------------------
        if (!$recursive) {
            //只在当前目录下搜索
            if($user_cat_id != null && !$recursive){
                $query->andFilterWhere(['Material.user_cat_id' => $user_cat_id]);
            }else{
                $query->andFilterWhere(['Material.user_cat_id' => 0]);
            }
        } else {
            //递归搜索所有目录
            $user_cat_ids = UserCategory::getCatChildrenIds($user_cat_id, true);
            $query->andFilterWhere([
                'Material.user_cat_id' => !empty($user_cat_ids) ? 
                    ArrayHelper::merge([$user_cat_id], $user_cat_ids) : $user_cat_id,
            ]);
        }
        //-----------------------
        // 关键字过滤
        //-----------------------
        if (!empty($keyword)) {
            $query->andFilterWhere(['OR', ['REGEXP', 'Material.name', $keyword], ['REGEXP', 'Tags.name', $keyword]]);
        }
        
        $queryClone = clone $query;
        //查询出所有值
        $query->offset(($page - 1) * $limit)
                ->limit($limit)
                ->orderBy(['Uploadfile.download_count' => SORT_DESC, 'Material.name' => SORT_ASC]);
        $medias = $query->all();

        return [
            'page' => $page,
            'total_count' => (int) $queryClone->select(['Material.id'])->count(),
            'list' => $medias,
        ];
    }
    
    /**
     * 根据素材ID查找素材详情
     * @param string $id    素材ID
     * @return array
     */
    protected function getMediaDetail($id)
    {
        $query = (new Query())
                ->select(['Material.id', 'Material.name', 'Material.type AS type_id', 'Uploadfile.thumb_path AS cover_url', 'Material.des',
                    'Uploadfile.oss_key', 'Uploadfile.duration', 'Uploadfile.size', 'Uploadfile.download_count', 'Material.created_at', 
                    'Material.updated_at', "GROUP_CONCAT(Tags.`name` ORDER BY TagRef.id ASC SEPARATOR ',') AS tags"])
                ->from(['Material' => Video::tableName()])
                ->leftJoin(['Uploadfile' => Uploadfile::tableName()], 'Uploadfile.id = Material.file_id')
                ->leftJoin(['TagRef' => TagRef::tableName()], 'TagRef.object_id = Material.id')
                ->leftJoin(['Tags' => Tags::tableName()], 'Tags.id = TagRef.tag_id')
                ->where(['Material.id' => $id, 'Uploadfile.is_del' => 0])
                ->groupBy(['Material.id'])
                ->one();
        
        return $query;
    }
    
    /**
     * 根据素材类型处理素材的封面图
     * @param array $media      素材信息
     * @return string
     */
    protected function getMaterialCoverUrl($media)
    {
        switch ($media['type_id']){
            case Video::TYPE_VIDEO :
                $media['cover_url'] = Aliyun::absolutePath(!empty($media['cover_url']) ? $media['cover_url'] : 'static/imgs/notfound.png');
                break;
            case Video::TYPE_AUDIO :
                $media['cover_url'] = StringUtil::completeFilePath('/imgs/build_course/images/audio.png');
                break;
            case Video::TYPE_IMAGE :
                $media['cover_url'] = Aliyun::absolutePath(!empty($media['cover_url']) ? $media['cover_url'] : 'static/imgs/notfound.png');
                break;
            case Video::TYPE_DOCUMENT :
                $media['cover_url'] = StringUtil::completeFilePath('/imgs/build_course/images/' . StringUtil::getFileExtensionName(Aliyun::absolutePath($media['oss_key'])) . '.png');
                break;
            default :
                $media['cover_url'] = Aliyun::absolutePath('static/imgs/notfound.png');
                break;
        }
        
        return $media['cover_url'];
    }
    
    /**
     * 根据用户分类ID得到当前分类ID的所在路径
     * @param integer $user_cat_id  用户分类ID
     * @return string
     */
    protected function getCatLocationPath($user_cat_id)
    {
        $pathMap = '根目录' . ' <i class="arrow">&gt;</i> ';
        $locationPathMap = UserCategory::getUserCatLocationPath($user_cat_id);
        if(isset($locationPathMap[$user_cat_id]) && count($locationPathMap[$user_cat_id]) > 0){
            $endPath = end($locationPathMap[$user_cat_id]);
            foreach ($locationPathMap[$user_cat_id] as $path) {
                if($path['id'] != $endPath['id']){
                    $pathMap .= $path['name'] . ' <i class="arrow">&gt;</i> ';
                }else{
                    $pathMap .= $path['name'];
                }
            }
            return $pathMap;
        }else{
            return $pathMap;
        }
    }
}
