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


namespace Modules\Account\Http\Controllers\Front;

use App\Exceptions\ErrorException;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserLevelCriteria;
use Modules\Account\Services\LoginService;
use Modules\Account\Services\UserInfoService;
use App\Support\Respond;
use Modules\Account\Services\UserLevelService;
use Modules\Sys\Services\VerifyCodeService;

class UserController extends BaseController
{
    private $userInfoService;
    private $verifyCodeService;
    private $loginService;
    private $userLevelService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserInfoService   $userInfoService,
        VerifyCodeService $verifyCodeService,
        LoginService      $loginService,
        UserLevelService  $userLevelService
    )
    {
        $this->userInfoService = $userInfoService;
        $this->verifyCodeService = $verifyCodeService;
        $this->loginService = $loginService;
        $this->userLevelService = $userLevelService;
    }


    /**
     * 获取用户基本信息
     */
    public function info()
    {
        $data = $this->userInfoService->getUserInfo();

        return Respond::success($data);
    }


    /**
     * 修改用户基本信息
     */
    public function edit(Request $request)
    {
        $user_id = checkLoginUserId();
        $this->userInfoService->editUserInfo($user_id, $request);

        return Respond::success($request->all());
    }


    /**
     * 绑定手机号
     */
    public function bindMobile(Request $request)
    {

        $user_id = checkLoginUserId();
        $verify_key = $request->input('verify_key', '');
        $verify_code = $request->input('verify_code', '');

        // 验证验证码
        if (!$this->verifyCodeService->checkVerifyCode($verify_key, $verify_code)) {
            throw new ErrorException(__('验证码有误'));
        }

        $row = $this->loginService->getMobileCountry($verify_key);
        $result = $this->loginService->bindMobile($user_id, $row['country_code'], $row['mobile']);

        return Respond::success($result);
    }


    /**
     * 解绑手机
     */
    public function unBindMobile(Request $request)
    {
        $user_id = checkLoginUserId();
        $verify_key = $request->input('verify_key', '');
        $verify_code = $request->input('verify_code', '');

        // 验证验证码
        if (!$this->verifyCodeService->checkVerifyCode($verify_key, $verify_code)) {
            throw new ErrorException(__('验证码有误'));
        }

        $row = $this->loginService->getMobileCountry($verify_key);
        $this->loginService->unBindMobile($user_id, $row['country_code'], $row['mobile']);

        return Respond::success($row);
    }


    /**
     * 提交实名认证信息
     */
    public function saveCertificate(Request $request)
    {
        $user_id = checkLoginUserId();
        $row = $request->all();
        $data = $this->loginService->saveCertificate($user_id, $row);

        return Respond::success($data);
    }

    public function getCompanyByUserId(Request $request)
    {
        $data = [];

        return Respond::success($data);
    }


    /**
     * 会员等级
     */
    function listBaseUserLevel(Request $request)
    {
        $data = $this->userLevelService->list($request, new UserLevelCriteria($request));

        return Respond::success($data);
    }


    public function listsExpRule()
    {
        $data = $this->loginService->listsExpRule();

        return Respond::success($data);
    }

}
