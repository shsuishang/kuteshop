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
$router->group(['prefix' => 'front/shop', 'namespace' => 'Front'], function () use ($router) {

    $router->get('/mobile/getSearchInfo', 'MobileController@getSearchInfo');

});


/**
 * Front 请求路径 Auth
 */
$router->group(['prefix' => 'front/shop', 'namespace' => 'Front', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/userFavoritesItem/lists', 'FavoritesItemController@list'); //收藏商品列表
    $router->post('/userFavoritesItem/add', 'FavoritesItemController@add'); //收藏商品
    $router->post('/userFavoritesItem/remove', 'FavoritesItemController@remove'); //取消收藏

    $router->get('/userVoucher/list', 'VoucherController@list'); //用户优惠券列表
    $router->get('/userVoucher/getEachVoucherNum', 'VoucherController@getEachVoucherNum'); //优惠券数量列表
    $router->post('/userVoucher/add', 'VoucherController@add'); //领取优惠券

    $router->get('/userProductBrowse/list', 'ProductBrowseController@list'); //浏览记录
    $router->post('/userProductBrowse/removeBrowser', 'ProductBrowseController@removeBrowser'); //删除浏览记录

});

/**
 * Manage 请求路径
 */
$router->group(['prefix' => 'manage/shop', 'namespace' => 'Manage', 'middleware' => 'auth'], function () use ($router) {

    //物流公司
    $router->get('/storeExpressLogistics/list', 'StoreExpressLogisticsController@list');
    $router->post('/storeExpressLogistics/add', 'StoreExpressLogisticsController@add');
    $router->post('/storeExpressLogistics/edit', 'StoreExpressLogisticsController@edit');
    $router->post('/storeExpressLogistics/remove', 'StoreExpressLogisticsController@remove');
    $router->post('/storeExpressLogistics/editState', 'StoreExpressLogisticsController@editState');

    //发货地址
    $router->get('/storeShippingAddress/list', 'StoreShippingAddressController@list');
    $router->post('/storeShippingAddress/add', 'StoreShippingAddressController@add');
    $router->post('/storeShippingAddress/edit', 'StoreShippingAddressController@edit');
    $router->post('/storeShippingAddress/remove', 'StoreShippingAddressController@remove');

    //物流工具
    $router->get('/storeTransportType/list', 'StoreTransportTypeController@list');
    $router->post('/storeTransportType/add', 'StoreTransportTypeController@add');
    $router->post('/storeTransportType/edit', 'StoreTransportTypeController@edit');
    $router->post('/storeTransportType/remove', 'StoreTransportTypeController@remove');

    //物流工具ITEM
    $router->get('/storeTransportItem/list', 'StoreTransportItemController@list');
    $router->post('/storeTransportItem/add', 'StoreTransportItemController@add');
    $router->post('/storeTransportItem/edit', 'StoreTransportItemController@edit');
    $router->post('/storeTransportItem/remove', 'StoreTransportItemController@remove');


    $router->get('/userVoucher/list', 'UserVoucherController@list'); //用户优惠券列表
});
