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

$router->group(['prefix' => 'front/account', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/login/logout', 'AuthController@logout');  //用户退出
    $router->get('/user/info', 'Front\UserController@info');       //获取用户信息
    $router->get('/userMessage/getNotice', 'Front\MessageController@getNotice'); //获取用户信息

});

$router->post('/front/account/login/login', 'LoginController@login');    //用户登录-账号密码登录
$router->get('/front/account/login/doSmsLogin', 'LoginController@doSmsLogin');    //用户登录-手机验证码登录
$router->post('/front/account/login/register', 'LoginController@register'); //用户注册
$router->get('/front/account/login/protocol', 'LoginController@protocol'); //协议
$router->post('/front/account/login/setNewPassword', 'LoginController@setNewPassword'); //设置登录密码

/**
 * Manage 请求路径
 */
$router->group(['prefix' => 'manage/account', 'namespace' => 'Manage', 'middleware' => 'auth'], function () use ($router) {

    //会员管理
    $router->get('/userInfo/list', 'UserInfoController@list');
    $router->get('/userInfo/getUserData', 'UserInfoController@getUserData');
    $router->post('/userInfo/add', 'UserInfoController@add');
    $router->post('/userInfo/edit', 'UserInfoController@edit');
    $router->post('/userInfo/passWordEdit', 'UserInfoController@passWordEdit');
    $router->post('/userInfo/remove', 'UserInfoController@remove');

    $router->get('/userBindConnect/list', 'UserBindConnectController@list'); //用户绑定信息

    //会员等级
    $router->get('/userLevel/list', 'UserLevelController@list');
    $router->post('/userLevel/add', 'UserLevelController@add');
    $router->post('/userLevel/edit', 'UserLevelController@edit');
    $router->post('/userLevel/remove', 'UserLevelController@remove');

    //会员标签组管理
    $router->get('/userTagGroup/tree', 'UserTagGroupController@tree');
    $router->get('/userTagGroup/list', 'UserTagGroupController@list');      //列表
    $router->post('/userTagGroup/add', 'UserTagGroupController@add');       //新增
    $router->post('/userTagGroup/edit', 'UserTagGroupController@edit');     //修改
    $router->post('/userTagGroup/remove', 'UserTagGroupController@remove'); //删除

    //会员标签管理
    $router->get('/userTagBase/list', 'UserTagBaseController@list');      //列表
    $router->post('/userTagBase/add', 'UserTagBaseController@add');       //新增
    $router->post('/userTagBase/edit', 'UserTagBaseController@edit');     //修改
    $router->post('/userTagBase/remove', 'UserTagBaseController@remove'); //删除
    $router->post('/userTagBase/editState', 'UserTagBaseController@editState'); //状态变更

    //会员消息管理
    $router->get('/userMessage/list', 'UserMessageController@list');      //列表
    $router->post('/userMessage/add', 'UserMessageController@add');       //新增
    $router->post('/userMessage/edit', 'UserMessageController@edit');     //修改
    $router->post('/userMessage/remove', 'UserMessageController@remove'); //删除
    $router->get('/userMessage/getNotice', 'UserMessageController@getNotice'); //获取通知
    $router->post('/userMessage/editState', 'UserMessageController@editState'); //状态变更

    //推广员
    $router->get('/userDistribution/list', 'UserDistributionController@list');      //列表
    $router->post('/userDistribution/add', 'UserDistributionController@add');       //新增
    $router->post('/userDistribution/edit', 'UserDistributionController@edit');     //修改

});


/**
 * Front 请求路径
 */
$router->group(['prefix' => '/front/account', 'namespace' => 'Front', 'middleware' => 'auth'], function () use ($router) {

    $router->post('/user/edit', 'UserController@edit'); //会员信息修改
    $router->post('/user/bindMobile', 'UserController@bindMobile'); //会员绑定手机号
    $router->post('/user/unBindMobile', 'UserController@unBindMobile'); //会员解绑手机号
    $router->post('/user/saveCertificate', 'UserController@saveCertificate'); //实名认证
    $router->get('/user/getCompanyByUserId', 'UserController@getCompanyByUserId');

    //会员地址管理
    $router->get('/userDeliveryAddress/list', 'DeliveryAddressController@list');  //列表
    $router->get('/userDeliveryAddress/get', 'DeliveryAddressController@get');    //详情
    $router->post('/userDeliveryAddress/add', 'DeliveryAddressController@add');   //新增
    $router->post('/userDeliveryAddress/save', 'DeliveryAddressController@save'); //修改
    $router->post('/userDeliveryAddress/remove', 'DeliveryAddressController@remove'); //删除

    //用户发票管理
    $router->get('/userInvoice/list', 'InvoiceController@list');   //列表
    $router->get('/userInvoice/get', 'InvoiceController@get');    //获取
    $router->post('/userInvoice/add', 'InvoiceController@add');    //新增
    $router->post('/userInvoice/edit', 'InvoiceController@edit');  //修改
    $router->post('/userInvoice/remove', 'InvoiceController@remove');  //删除
    $router->get('/userInvoice/getInvoiceTips', 'InvoiceController@getInvoiceTips');

    //用户消息
    $router->get('/userMessage/getMsgCount', 'MessageController@getMsgCount');
    $router->get('/userMessage/list', 'MessageController@list');
    $router->get('/userMessage/get', 'MessageController@get');
    $router->get('/userMessage/getImConfig', 'MessageController@getImConfig');
    $router->get('/userMessage/getMessageNum', 'MessageController@getMessageNum');
    $router->post('/userMessage/add', 'MessageController@add');
    $router->post('/userMessage/setRead', 'MessageController@setRead');


    $router->get('/user/listBaseUserLevel', 'UserController@listBaseUserLevel');   //会员等级列表
    $router->get('/user/listsExpRule', 'UserController@listsExpRule');   //经验值规则

});
