<?php

namespace frontend\modules\help_center\controllers;

use common\models\helpcenter\Post;
use common\models\helpcenter\PostAppraise;
use common\models\helpcenter\PostCategory;
use common\models\helpcenter\PostComment;
use common\models\helpcenter\searchs\PostCommentSearch;
use Yii;
use yii\db\Exception;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\widgets\Menu;

/**
 * Default controller for the `helpcenter` module
 */
class DefaultController extends Controller 
{
    public $layout = "main";

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
    
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex() 
    {
        //传递参数
        $view = Yii::$app->view;
        $view->params['app_id'] = 'app-frontend';
        $categoryId = Yii::$app->request->get('id');
        if(empty($categoryId)){
            $parCatName = PostCategory::find()->where(['is_show' => 1, 'level' => 1])->orderBy('sort_order')->one();
            $catName = PostCategory::findOne(['parent_id' => $parCatName->id]);
        } else {
            $catName = PostCategory::findOne($categoryId);
        }

        return $this->render('index', [
            'posts' => $this->getPostContents($catName->id),     //文章内容
            'categoryName' => $catName,  //文章分类名
        ]);
    }
    
    /**
     * Renders the index view for the module
     * @return mixed
     */
    public function actionView($id) 
    {
        $app_id = ArrayHelper::getValue(Yii::$app->request->queryParams, 'app_id');                 //应用ID
        $number = (new Query())->from(PostComment::tableName())->where(['post_id'=>$id])->count();  //文章评论数量
        $model = $this->getPostContents($id);
        $uer_id = Yii::$app->user->id;
        $isLike = PostAppraise::findOne(['post_id' => $id,'user_id' => $uer_id,'result' => '1',]);  //是否点赞
        $isUnlike = PostAppraise::findOne(['post_id' => $id,'user_id' => $uer_id,'result' => '2',]);//是否踩
        //传递参数
        $view = Yii::$app->view;
        $view->params['app_id'] = $app_id;
        //获取文章
        $view_count = Post::findOne($id);
        $view_count->view_count += 1;           //打开文章时查看次数+1
        $view_count->save(FALSE,['view_count']);
        return $this->render('view', [
            'model' => $model,
            'number' => $number,                //评论数量
            'isLike' => $isLike != null,
            'isUnlike' => $isUnlike != null,
            'page' => $this->getPage($app_id, $id),
        ]);
    }

    /**
     * 获取留言内容
     * Lists all PostComment models.
     * @return mixed
     */
    public function actionMesIndex($post_id)
    {
        $searchModel = new PostCommentSearch();
        
        return $this->renderAjax('mes-index', [
            'dataProvider' => $searchModel->search(['post_id'=>$post_id])
        ]);
    }
    
    /**
     * 创建评论
     * Creates a new PostComment model.
     * @return mixed
     */
    public function actionCreateMessage($post_id)
    {
        $model = new PostComment(['post_id'=>$post_id,'created_by'=> Yii::$app->user->id]);
        $model->loadDefaultValues();
        $num = 0;
        $comment_count = Post::findOne($post_id);
        if(Yii::$app->request->isPost){
            Yii::$app->getResponse()->format = 'json';
            $result = $this->CreateMessage($model,Yii::$app->request->post());
            $comment_count->comment_count ++;
            $comment_count->update();
            return [
               'code'=> $result ? 200 : 404,
               'num' => $result ? $num + 1: $num,
               'message' => ''
            ];
        } else {
            return $this->goBack(['view', 'id' => $post_id]);
        }
    }
    
    /**
     * 添加评论操作
     * @param type $model
     * @param type $post
     * @return boolean
     * @throws Exception
     */
    public function CreateMessage($model,$post)
    {
        $model->content = ArrayHelper::getValue($post, 'content');
        /** 开启事务 */
        $trans = Yii::$app->db->beginTransaction();
        try
        {  
            if ($model->save()) {
                
            } else {
                throw new Exception($model->getErrors());
            }
            $trans->commit();  //提交事务
            return true;
        }catch (Exception $ex) {
            $trans ->rollBack(); //回滚事务
            return false;
        }
    }
    
    /**
     * 组装菜单
     * @param string $app_id    应用ID
     * @return array
     */
    public static function getMenu($app_id) {
        $menus = self::getCategories($app_id, null)->all();
        $menuItems = [];
        foreach ($menus as $_menu) {
            if ($_menu->parent_id == 0) {
                $children = self::getChildrenMenu($menus, $_menu->id);
                $item = [
                    'label' => $_menu->name,
                ];
                $item['url'] = '#';
                $item['icon'] = '<i class="fa fa-' . $_menu->icon . '">&nbsp;</i>';
                $item['chevron'] = '<i class="fa fa-chevron-down"></i>';
                $item['options'] = ['class' => 'link'];
                if (count($children) > 0) {
                    $item['items'] = $children;
                }
                
                $menuItems[] = $item;
            }
        }
        
        return $menuItems;
    }

    /**
     * 获取所有菜单
     * @param string $app_id    应用id
     * @param integer $level    等级
     * @return array
     */
    public static function getCategories($app_id, $level = 1) {
        $parentCats = PostCategory::find()
                ->from(['PostCategory' => PostCategory::tableName()]);
//        $parentCats->leftJoin(['Post' => Post::tableName()], 'Post.category_id = PostCategory.id');
        $parentCats->where(['PostCategory.is_show' => true,'app_id' => $app_id])
                ->andFilterWhere(['level' => $level])
                ->orderBy('sort_order');
        
        return $parentCats;
    }

    /**
     * 获取二级菜单
     * @param Menu $menu
     * @param array $allMenus   获取所有菜单
     * @param type $parnet_id   父级ID
     * @return array
     */
    private static function getChildrenMenu($allMenus, $parent_id) {
        $items = [];
        foreach ($allMenus as $menu) {
            if($menu->parent_id == $parent_id){
                $items[] = [
                    'label' => $menu->name,
                    'url' => '?id=' . $menu->id,
                    'icon' => '<i class="fa fa-' . $menu->icon . '">&nbsp;</i>',
                    'chevron' => '',
                    'options' => ['class' => 'links'],
                ];
            }
        }
       
        return $items;
    }

    /**
     * 获取文章内容
     * @param integer $categoryId       文章分类ID
     * @return array
     */
    public function getPostContents($categoryId) 
    {
        $postContents = (new Query())
            ->from(['Post' => Post::tableName()])
            ->where([
                'is_show' => true,
                'category_id' => $categoryId,
            ])
            ->orderBy(['sort_order' => SORT_ASC])->all();
        
        return $postContents;
    }
   
    /**
     * 查询上/下篇文章
     * @param string $app_id    应用ID
     * @param integer $id       文章ID
     * @return array
     */
    public function getPage($app_id, $id) {
        //查询相关数据
        $query = (new Query())
                ->select(['Post.id', 'Post.title', 'Post.sort_order', 'category_id'])
                ->from(['Post' => Post::tableName()])
                ->leftJoin(['PostCategory' => PostCategory::tableName()], 'PostCategory.id = Post.category_id')
                ->where(['PostCategory.app_id' => $app_id,'Post.is_show' => true,]);
        $categoryQuery = clone $query;
        //获取当前文章所在的分类ID
        $category_id = $categoryQuery->where(['Post.id' => $id])->one()['category_id'];
        $query->andWhere(['Post.category_id' => $category_id]);
        
        $sortQuery = clone $query;
        //获取当前文章的排序索引
        $sort_order = $sortQuery->where(['Post.id' => $id])->one()['sort_order'];
        
        $nextQuery = clone $query;
        //查询上一篇文章
        $prev_article = $query->andfilterWhere(['<', 'Post.sort_order', $sort_order])
                ->orderBy(['Post.sort_order' => SORT_DESC])
                ->one();
        //查询下一篇文章
        $next_article = $nextQuery->andfilterWhere(['>', 'Post.sort_order', $sort_order])
                ->orderBy(['Post.sort_order' => SORT_ASC])
                ->one();

        $model['prev_article'] = [
            'url' => !empty($prev_article) ? Url::current(['id'=>$prev_article['id']]) : 'javascript:;',
            'title' => !empty($prev_article) ? $prev_article['title'] : '没有了',
        ];
        $model['next_article'] = [
            'url' => !empty($next_article) ? Url::current(['id'=>$next_article['id']]) : 'javascript:;',
            'title' => !empty($next_article) ? $next_article['title'] : '没有了',
        ];

        return $model;
    }
}
