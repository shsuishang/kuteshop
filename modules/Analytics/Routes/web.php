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

$router->group(['prefix' => 'manage/analytics', 'namespace' => 'Manage'], function () use ($router) {

    $router->get('/user/getVisitor', 'AnalyticsController@getVisitor');
    $router->get('/user/getRegUser', 'AnalyticsController@getRegUser');
    $router->get('/user/getUserTimeLine', 'AnalyticsController@getUserTimeLine');
    $router->get('/user/getUserNum', 'AnalyticsController@getUserNum');

    $router->get('/history/getAccessNum', 'AnalyticsController@getAccessNum');
    $router->get('/history/getAccessVisitorTimeLine', 'AnalyticsController@getAccessVisitorTimeLine');
    $router->get('/history/getAccessVisitorNum', 'AnalyticsController@getAccessVisitorNum');
    $router->get('/history/getAccessItemUserTimeLine', 'AnalyticsController@getAccessItemUserTimeLine');
    $router->get('/history/getAccessItemNum', 'AnalyticsController@getAccessItemNum');
    $router->get('/history/getAccessItemUserNum', 'AnalyticsController@getAccessItemUserNum');
    $router->get('/history/listAccessItem', 'AnalyticsController@listAccessItem');
    $router->get('/history/getAccessItemTimeLine', 'AnalyticsController@getAccessItemTimeLine');

    $router->get('/order/getOrderNum', 'AnalyticsController@getOrderNum');
    $router->get('/order/getOrderAmount', 'AnalyticsController@getOrderAmount');
    $router->get('/order/getSaleOrderAmount', 'AnalyticsController@getSaleOrderAmount');
    $router->get('/order/getOrderNumTimeline', 'AnalyticsController@getOrderNumTimeline');
    $router->get('/order/getOrderItemNum', 'AnalyticsController@getOrderItemNum');
    $router->get('/order/listOrderItemNum', 'AnalyticsController@listOrderItemNum');
    $router->get('/order/getOrderItemNumTimeLine', 'AnalyticsController@getOrderItemNumTimeLine');

    $router->get('/return/getReturnNum', 'AnalyticsReturnController@getReturnNum');
    $router->get('/return/getReturnAmount', 'AnalyticsReturnController@getReturnAmount');
    $router->get('/return/getReturnAmountTimeline', 'AnalyticsReturnController@getReturnAmountTimeline');
    $router->get('/return/getReturnNumTimeline', 'AnalyticsReturnController@getReturnNumTimeline');

    $router->get('/product/getProductNum', 'AnalyticsController@getProductNum');


    $router->get('/trade/getSalesAmount', 'AnalyticsController@getSalesAmount');
    $router->get('/order/getDashboardTimeLine', 'AnalyticsController@getDashboardTimeLine');
    $router->get('/order/getCustomerTimeline', 'AnalyticsController@getCustomerTimeline');
    $router->get('/order/getOrderNumToday', 'AnalyticsController@getOrderNumToday');
    $router->get('/order/getOrderCustomerNumTimeline', 'AnalyticsController@getOrderCustomerNumTimeline');


});

