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
 * Front 请求路径 No Auth
 */
$router->group(['prefix' => 'front/pt', 'namespace' => 'Front'], function () use ($router) {

    $router->get('/product/listCategory', 'ProductController@listCategory'); //商品分类列表
    $router->get('/product/treeCategory', 'ProductController@treeCategory'); //商品树形列表
    $router->get('/product/list', 'ProductController@list');     //商品列表
    $router->get('/product/detail', 'ProductController@detail'); //商品详情
    $router->get('/product/listAllCategory', 'ProductController@listAllCategory'); //商品全部分类
    $router->get('/product/brand', 'ProductController@brand'); //推荐品牌
    $router->get('/product/getComment', 'ProductController@getComment'); //商品评论

    $router->get('/product/listItem', 'ProductController@listItem');
    $router->get('/product/getSearchFilter', 'ProductController@getSearchFilter'); //商品筛选属性

});

$router->group(['prefix' => 'manage/pt', 'namespace' => 'Manage', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/productCategory/tree', 'ProductCategoryController@tree');
    $router->get('/productCategory/list', 'ProductCategoryController@list');
    $router->post('/productCategory/add', 'ProductCategoryController@add');
    $router->post('/productCategory/edit', 'ProductCategoryController@edit');
    $router->post('/productCategory/remove', 'ProductCategoryController@remove');
    $router->post('/productCategory/editState', 'ProductCategoryController@editState');

    //商品类型
    $router->get('/productType/list', 'ProductTypeController@list');
    $router->get('/productType/info', 'ProductTypeController@info');
    $router->post('/productType/add', 'ProductTypeController@add');
    $router->post('/productType/edit', 'ProductTypeController@edit');
    $router->post('/productType/remove', 'ProductTypeController@remove');

    //商品属性
    $router->get('/productAssist/list', 'ProductAssistController@list');
    $router->post('/productAssist/add', 'ProductAssistController@add');
    $router->post('/productAssist/edit', 'ProductAssistController@edit');
    $router->post('/productAssist/remove', 'ProductAssistController@remove');
    $router->get('/productAssist/tree', 'ProductAssistController@tree');
    $router->get('/productAssistItem/list', 'ProductAssistItemController@list');
    $router->post('/productAssistItem/add', 'ProductAssistItemController@add');
    $router->post('/productAssistItem/edit', 'ProductAssistItemController@edit');
    $router->post('/productAssistItem/remove', 'ProductAssistItemController@remove');

    //商品品牌
    $router->get('/productBrand/list', 'ProductBrandController@list');
    $router->get('productBrand/tree', 'ProductBrandController@tree');
    $router->post('/productBrand/add', 'ProductBrandController@add');
    $router->post('/productBrand/edit', 'ProductBrandController@edit');
    $router->post('/productBrand/remove', 'ProductBrandController@remove');
    $router->post('/productBrand/editState', 'ProductBrandController@editState');

    //商品规格
    $router->get('/productSpec/list', 'ProductSpecController@list');
    $router->post('/productSpec/add', 'ProductSpecController@add');
    $router->post('/productSpec/edit', 'ProductSpecController@edit');
    $router->post('/productSpec/remove', 'ProductSpecController@remove');
    $router->get('/productSpec/tree', 'ProductSpecController@tree');
    $router->get('/productSpecItem/list', 'ProductSpecItemController@list');
    $router->post('/productSpecItem/add', 'ProductSpecItemController@add');
    $router->post('/productSpecItem/edit', 'ProductSpecItemController@edit');
    $router->post('/productSpecItem/remove', 'ProductSpecItemController@remove');
    $router->post('/productSpecItem/editState', 'ProductSpecItemController@editState');

    //商品标签
    $router->get('/productTag/list', 'ProductTagController@list');
    $router->post('/productTag/add', 'ProductTagController@add');
    $router->post('/productTag/edit', 'ProductTagController@edit');
    $router->post('/productTag/remove', 'ProductTagController@remove');

    //商品评论
    $router->get('/productComment/list', 'ProductCommentController@list');
    $router->post('/productComment/add', 'ProductCommentController@add');
    $router->post('/productComment/edit', 'ProductCommentController@edit');
    $router->post('/productComment/remove', 'ProductCommentController@remove');
    $router->post('/productComment/editState', 'ProductCommentController@editState');
    //评论回复
    $router->get('/productCommentReply/list', 'ProductCommentReplyController@list');
    $router->post('/productCommentReply/editState', 'ProductCommentReplyController@editState');
    $router->post('/productCommentReply/add', 'ProductCommentReplyController@add');


    //商品列表
    $router->get('/productBase/list', 'ProductBaseController@list');
    $router->post('/productBase/save', 'ProductBaseController@save');
    $router->post('/productBase/remove', 'ProductBaseController@remove');
    $router->post('/productBase/editState', 'ProductBaseController@editState');
    $router->get('/productBase/getProductDate', 'ProductBaseController@getProduct');
    $router->get('/productBase/listItem', 'ProductBaseController@listItem');
    $router->post('/productBase/editEnable', 'ProductBaseController@editState');
    $router->post('/productBase/editCommissionRate', 'ProductBaseController@editCommissionRate');
    $router->post('/productBase/editSort', 'ProductBaseController@editSort');
    $router->post('/productBase/batchEditState', 'ProductBaseController@batchEditState');

    //商品SKU
    $router->get('/productItem/list', 'ProductItemController@list');
    $router->post('/productItem/editState', 'ProductItemController@editState');
    $router->post('/productItem/editStock', 'ProductItemController@editStock');
    $router->get('/productItem/getStockBillItems', 'ProductItemController@getStockBillItems');
    $router->get('/productItem/getStockWarningItems', 'ProductItemController@getStockWarningItems');

});
