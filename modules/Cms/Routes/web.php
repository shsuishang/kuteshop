<?php
// +----------------------------------------------------------------------
// | ShopSuite商城系统 [ 赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | 版权所有 随商信息技术（上海）有限公司
// +----------------------------------------------------------------------
// | 未获商业授权前，不得将本软件用于商业用途。禁止整体或任何部分基础上以发展任何派生版本、
// | 修改版本或第三方版本用于重新分发。
// +----------------------------------------------------------------------
// | 官方网站: https://www.kuteshop.cn  https://www.kuteshop.cn
// +----------------------------------------------------------------------
// | 版权和免责声明:
// | 本公司对该软件产品拥有知识产权（包括但不限于商标权、专利权、著作权、商业秘密等）
// | 均受到相关法律法规的保护，任何个人、组织和单位不得在未经本团队书面授权的情况下对所授权
// | 软件框架产品本身申请相关的知识产权，禁止用于任何违法、侵害他人合法权益等恶意的行为，禁
// | 止用于任何违反我国法律法规的一切项目研发，任何个人、组织和单位用于项目研发而产生的任何
// | 意外、疏忽、合约毁坏、诽谤、版权或知识产权侵犯及其造成的损失 (包括但不限于直接、间接、
// | 附带或衍生的损失等)，本团队不承担任何法律责任，本软件框架只能用于公司和个人内部的
// | 法律所允许的合法合规的软件产品研发，详细见https://www.modulithshop.cn/policy
// +----------------------------------------------------------------------


/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/


/**
 * Manage 请求路径
 */
$router->group(['prefix' => 'manage/cms', 'namespace' => 'Manage', 'middleware' => 'auth'], function () use ($router) {

    //文章分类
    $router->get('/articleCategory/tree', 'ArticleCategoryController@tree');      //列表
    $router->post('/articleCategory/add', 'ArticleCategoryController@add');       //新增
    $router->post('/articleCategory/edit', 'ArticleCategoryController@edit');     //修改
    $router->post('/articleCategory/remove', 'ArticleCategoryController@remove'); //删除


    //文章
    $router->get('/articleBase/list', 'ArticleBaseController@list');      //列表
    $router->post('/articleBase/add', 'ArticleBaseController@add');       //新增
    $router->post('/articleBase/edit', 'ArticleBaseController@edit');     //修改
    $router->post('/articleBase/editState', 'ArticleBaseController@editState'); //修改
    $router->post('/articleBase/remove', 'ArticleBaseController@remove'); //删除
    $router->post('/articleBase/removeBatch', 'ArticleBaseController@removeBatch'); //批量删除

    //文章标签 Tag
    $router->get('/articleTag/list', 'ArticleTagController@list');      //列表
    $router->post('/articleTag/add', 'ArticleTagController@add');       //新增
    $router->post('/articleTag/edit', 'ArticleTagController@edit');     //修改
    $router->post('/articleTag/remove', 'ArticleTagController@remove'); //删除
    $router->post('/articleTag/removeBatch', 'ArticleTagController@removeBatch'); //批量删除

    //文章评论 Tag
    $router->get('/articleComment/list', 'ArticleCommentController@list');      //列表
    $router->post('/articleComment/add', 'ArticleCommentController@add');       //新增
    $router->post('/articleComment/edit', 'ArticleCommentController@edit');     //修改
    $router->post('/articleComment/editState', 'ArticleCommentController@editState'); //修改
    $router->post('/articleComment/remove', 'ArticleCommentController@remove'); //删除
    $router->post('/articleComment/removeBatch', 'ArticleCommentController@removeBatch'); //批量删除

});

/**
 * Front 请求路径
 */
$router->group(['prefix' => 'front/cms', 'namespace' => 'Front'], function () use ($router) {

    $router->get('/articleBase/listCategory', 'ArticleController@listCategory');  //分类
    $router->get('/articleBase/get', 'ArticleController@get');       //新增
    $router->get('/articleBase/list', 'ArticleController@list');     //修改

});
