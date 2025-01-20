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


namespace Modules\Account\Http\Controllers;

use App\Exceptions\ErrorException;
use App\Support\BindConnectCode;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Services\LoginService;
use Modules\Account\Services\UserService;
use App\Support\Respond;
use Modules\Sys\Services\ConfigBaseService;
use Modules\Sys\Services\VerifyCodeService;

class LoginController extends BaseController
{
    private $userService;
    private $configBaseService;
    private $verifyCodeService;
    private $loginService;


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserService       $userService,
        ConfigBaseService $configBaseService,
        VerifyCodeService $verifyCodeService,
        LoginService      $loginService
    )
    {
        $this->userService = $userService;
        $this->configBaseService = $configBaseService;
        $this->verifyCodeService = $verifyCodeService;
        $this->loginService = $loginService;
    }


    /**
     * 登录
     */
    public function login(Request $request)
    {
        if ($request->has('verify_code')) {
            checkVerifyCode($request);
        }
        $data = $this->loginService->login($request);

        return Respond::success($data);
    }


    //用户注册
    public function register(Request $request)
    {
        if ($request->has('verify_code')) {
            checkVerifyCode($request);
        }

        $this->loginService->register($request);
        $data = $this->loginService->login($request);

        return Respond::success($data);
    }


    /**
     * 获取协议信息
     */
    public function protocol(Request $request)
    {
        $protocols_key = $request->get('protocols_key', 'reg_protocols_description');
        $document = $this->configBaseService->getConfig($protocols_key);
        $data['document'] = $document;

        return Respond::success($data);
    }


    /**
     * 设置登录密码
     */
    public function setNewPassword(Request $request)
    {

        $user_id = 0;
        $verify_key = $request->input('verify_key', '');
        $verify_code = $request->input('verify_code', '');
        $bind_type = $request->input('bind_type', 1);

        // 验证验证码
        if (!$this->verifyCodeService->checkVerifyCode($verify_key, $verify_code)) {
            throw new ErrorException(__('验证码有误'));
        }

        // 验证新密码是否为空
        $password = $request->input('password', '');
        if ($password == '') {
            throw new ErrorException(__('请输入密码'));
        }

        // 根据绑定类型处理
        if ($bind_type == BindConnectCode::MOBILE) {

            // 验证手机号格式
            $mobile_row = $this->loginService->getMobileCountry($verify_key);
            $bind_id = sprintf("%s%d", $mobile_row['country_code'], $mobile_row['mobile']);
            $bind_row = $this->loginService->checkBindInfo($bind_id);
            $user_id = $bind_row['user_id'];

        } elseif ($bind_type == BindConnectCode::EMAIL) {

            $bind_id = $verify_key;
            $bind_row = $this->loginService->checkBindInfo($bind_id);
            $user_id = $bind_row['user_id'];

        } elseif ($bind_type == BindConnectCode::ACCOUNT) {
            // 检查用户登录状态和旧密码
            $user_id = checkLoginUserId();
            $old_password = $request->input('old_password', '');
            $this->loginService->checkUserPassword($user_id, $old_password);
        }

        // 重置密码
        $this->loginService->doResetPasswd($user_id, $password);

        return Respond::success([]);
    }


    /**
     * 手机验证码登录
     */
    public function doSmsLogin(Request $request)
    {
        $verify_key = $request->input('verify_key', '');
        $verify_code = $request->input('verify_code', '');

        // 验证验证码
        if (!$this->verifyCodeService->checkVerifyCode($verify_key, $verify_code)) {
            throw new ErrorException(__('验证码有误'));
        }

        $data = $this->loginService->doSmsLogin($verify_key);

        return Respond::success($data);
    }

}
