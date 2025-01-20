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


//后台接口
$router->group(['prefix' => 'manage/o2o', 'namespace' => 'Manage', 'middleware' => ['auth']], function () use ($router) {

    //门店管理
    $router->get('/chainBase/list', 'ChainBaseController@list');
    $router->post('/chainBase/add', 'ChainBaseController@add');
    $router->post('/chainBase/edit', 'ChainBaseController@edit');
    $router->post('/chainBase/remove', 'ChainBaseController@remove');

    //门店用户管理
    $router->get('/chainUser/list', 'ChainUserController@list');
    $router->post('/chainUser/add', 'ChainUserController@add');
    $router->post('/chainUser/edit', 'ChainUserController@edit');
    $router->post('/chainUser/remove', 'ChainUserController@remove');

});


//前端接口
$router->group(['prefix' => 'front/o2o', 'namespace' => 'Front', 'middleware' => ['auth']], function () use ($router) {

    //门店
    $router->get('/chain/list', 'ChainController@list');

});
