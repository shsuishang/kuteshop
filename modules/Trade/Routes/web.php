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
$router->group(['prefix' => 'manage/trade', 'namespace' => 'Manage', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/orderBase/list', 'OrderBaseController@list');     //订单列表
    $router->get('/orderBase/detail', 'OrderBaseController@detail'); //订单详情
    $router->get('/orderBase/listStateLog', 'OrderBaseController@listStateLog'); //订单状态日志
    $router->post('/orderBase/review', 'OrderBaseController@review');      //订单审核
    $router->post('/orderBase/finance', 'OrderBaseController@finance');    //财务审核
    $router->post('/orderBase/picking', 'OrderBaseController@picking');    //出库操作
    $router->post('/orderBase/shipping', 'OrderBaseController@shipping');  //发货操作
    $router->post('/orderBase/receive', 'OrderBaseController@receive');    //收货操作
    $router->post('/orderBase/cancel', 'OrderBaseController@cancel');      //取消订单

    $router->post('/orderLogistics/add', 'OrderLogisticsController@add');   //订单物流
    $router->post('/orderLogistics/edit', 'OrderLogisticsController@edit'); //修改订单物流

    $router->get('/orderReturn/list', 'OrderReturnController@list'); //售后订单
    $router->get('/orderReturn/getByReturnId', 'OrderReturnController@getByReturnId'); //售后订单详情
    $router->post('/orderReturn/review', 'OrderReturnController@review'); //售后订单审核
    $router->post('/orderReturn/refused', 'OrderReturnController@refused'); //拒绝退单
    $router->post('/orderReturn/receive', 'OrderReturnController@receive'); //确认收货
    $router->post('/orderReturn/refund', 'OrderReturnController@refund'); //确认付款

    //退款原因
    $router->get('/orderReturnReason/list', 'OrderReturnReasonController@list');
    $router->post('/orderReturnReason/add', 'OrderReturnReasonController@add');
    $router->post('/orderReturnReason/edit', 'OrderReturnReasonController@edit');

    //订单发票管理
    $router->get('/orderInvoice/list', 'OrderInvoiceController@list');
    $router->post('/orderInvoice/editStatus', 'OrderInvoiceController@editStatus');


    //推广订单列表
    $router->get('/distributionOrder/list', 'DistributionOrderController@list');

});

/**
 * Front 请求路径
 */
$router->group(['prefix' => '/front/trade', 'namespace' => 'Front', 'middleware' => 'auth'], function () use ($router) {

    //订单管理
    $router->post('/order/add', 'OrderController@add');     //添加
    $router->get('/order/list', 'OrderController@list');     //订单列表
    $router->get('/order/detail', 'OrderController@detail'); //订单详情
    $router->get('/order/getOrderNum', 'OrderController@getOrderNum'); //订单数量
    $router->post('/order/cancel', 'OrderController@cancel');     //取消
    $router->get('/orderLogistics/trace', 'LogisticsController@trace'); //订单物流
    $router->post('/order/receive', 'OrderController@receive'); //订单物流
    $router->get('/order/listInvoice', 'OrderController@listInvoice'); //订单发票
    $router->post('/order/addOrderInvoice', 'OrderController@addOrderInvoice'); //订单申请开票

    //购物车相关
    $router->post('/cart/add', 'CartController@add');
    $router->get('/cart/list', 'CartController@list');
    $router->post('/cart/sel', 'CartController@sel');
    $router->post('/cart/editQuantity', 'CartController@editQuantity');
    $router->post('/cart/remove', 'CartController@remove');
    $router->get('/cart/checkout', 'CartController@checkout');
    $router->post('/cart/removeBatch', 'CartController@removeBatch');

    //订单评价相关
    $router->get('/order/storeEvaluationWithContent', 'OrderController@storeEvaluationWithContent');
    $router->post('/order/addOrderComment', 'OrderController@addOrderComment');

    //订单退款相关
    $router->get('/orderReturn/returnItem', 'ReturnController@returnItem');
    $router->post('/orderReturn/add', 'ReturnController@add');
    $router->get('/orderReturn/list', 'ReturnController@list');
    $router->get('/orderReturn/get', 'ReturnController@get');
    $router->post('/orderReturn/cancel', 'ReturnController@cancel');
    $router->post('/orderReturn/edit', 'ReturnController@edit');

    //订单佣金
    $router->get('/distribution/listsOrder', 'DistributionController@listsOrder');


});
