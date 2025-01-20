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

$router->group(['prefix' => 'manage/pay', 'namespace' => 'Manage', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/consumeRecord/list', 'ConsumeRecordController@list'); //收支明细
    $router->get('/consumeTrade/list', 'ConsumeTradeController@list'); //交易订单

    $router->get('/consumeDeposit/list', 'ConsumeDepositController@list'); //充值记录
    $router->post('/consumeDeposit/offline', 'ConsumeDepositController@offlinePay'); //添加收款记录

    $router->get('/userResource/list', 'UserResourceController@list'); //支付会员
    $router->post('/userResource/updateUserMoney', 'UserResourceController@updateUserMoney'); //修改余额
    $router->post('/userResource/updatePoints', 'UserResourceController@updatePoints'); //修改积分

    $router->get('/userPointsHistory/list', 'UserPointsHistoryController@list'); //积分明细

    $router->get('/baseBank/list', 'BaseBankController@list'); //银行管理列表
    $router->post('/baseBank/edit', 'BaseBankController@edit'); //银行管理
    $router->post('/baseBank/remove', 'BaseBankController@remove'); //银行管理
    $router->post('/baseBank/add', 'BaseBankController@add'); //银行管理


    //提现列表
    $router->get('/consumeWithdraw/list', 'ConsumeWithdrawController@list');
    $router->post('/consumeWithdraw/add', 'ConsumeWithdrawController@add');
    $router->post('/consumeWithdraw/edit', 'ConsumeWithdrawController@edit');
    $router->post('/consumeWithdraw/remove', 'ConsumeWithdrawController@remove');

});


/**
 * Front 请求路径 No Auth
 */
$router->group(['prefix' => '/front/pay', 'namespace' => 'Front'], function () use ($router) {

    $router->post('/consumeDeposit/alipayPay', 'AlipayController@pay'); //支付宝支付
    $router->post('/consumeDeposit/alipayPcPay', 'AlipayController@pcPay'); //支付宝支付

    $router->get('/callback/alipayReturn', 'AlipayController@alipayReturn'); //支付宝同步回调
    $router->post('/callback/alipayNotify', 'AlipayController@alipayNotify'); //支付宝异步回调

    $router->post('/consumeDeposit/wechatNativePay', 'WechatController@wechatNativePay'); //微信支付-PC
    $router->post('/consumeDeposit/wechatH5Pay', 'WechatController@h5Pay'); //微信支付-H5

    $router->post('/callback/wechatNotify', 'WechatController@wechatNotify'); //微信支付回调
    $router->get('/callback/wechatCheck', 'WechatController@wechatCheck'); //微信支付状态判断
});


/**
 * Front 请求路径
 */
$router->group(['prefix' => '/front/pay', 'namespace' => 'Front', 'middleware' => 'auth'], function () use ($router) {

    $router->get('/userResource/signState', 'ResourceController@signState');
    $router->post('/userResource/signIn', 'ResourceController@signIn');
    $router->get('/userResource/getSignInfo', 'ResourceController@getSignInfo');
    $router->get('/points/list', 'PointsController@list');


    $router->post('/consumeDeposit/moneyPay', 'PaymentIndexController@moneyPay'); //余额支付

    $router->get('/consumeRecord/list', 'ConsumeController@list');

    //提现账户管理
    $router->get('/userBank/list', 'UserBankController@list');
    $router->get('/userBank/get', 'UserBankController@get');
    $router->post('/userBank/addOrEditUserBank', 'UserBankController@addOrEditUserBank');
    $router->post('/userBank/remove', 'UserBankController@remove');

    $router->get('/index/getPayPasswd', 'IndexController@getPayPasswd'); //用户支付密码
    $router->post('/index/changePayPassword', 'IndexController@changePayPassword'); //设置支付密码

    $router->get('/userResource/listsExp', 'ResourceController@listsExp'); //经验值列表
});
