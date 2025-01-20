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


namespace Modules\Account\Http\Controllers\Manage;

use App\Support\BindConnectCode;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserInfoCriteria;
use Modules\Account\Services\LoginService;
use Modules\Account\Services\UserInfoService;

class UserInfoController extends BaseController
{
    private $userInfoService;
    private $loginService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserInfoService $userInfoService, LoginService $loginService)
    {
        $this->userInfoService = $userInfoService;
        $this->loginService = $loginService;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userInfoService->list($request, new UserInfoCriteria($request));

        return Respond::success($data);
    }


    /**
     * 会员信息
     */
    public function getUserData(Request $request)
    {
        $data = $this->userInfoService->getUserData($request->get('user_id'));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        // 设置新的参数
        $request->merge(['bind_type' => BindConnectCode::ACCOUNT]);
        $data = $this->loginService->register($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $user_id = $request->get('user_id', -1);
        $user_info_row = [
            'user_nickname' => $request['user_nickname'], //昵称
            'user_avatar' => $request->input('user_avatar', ''), //用户头像
            'user_state' => $request->input('user_state', 1), //状态(ENUM):0-锁定;1-已激活;2-未激活;
            'user_mobile' => $request->input('user_mobile', ''), //手机号码
            'user_intl' => $request->input('user_intl', '+86'), //国家编码
            'user_gender' => $request->input('user_gender', 1), //性别(ENUM):0-保密;1-男;  2-女;
            'user_birthday' => $request->input('user_birthday', ''), //生日(DATE)
            'user_email' => $request->input('user_email', ''), //用户邮箱(email)
            'user_level_id' => $request->input('user_level_id', 0), //等级编号
            'user_is_authentication' => $request->input('user_is_authentication', 0), //认证状态(ENUM):0-未认证;1-待审核;2-认证通过;3-认证失败
            'tag_ids' => $request->input('tag_ids', ''), //用户标签(DOT)
            'user_from' => $request->input('user_from', 2310), //用户来源(ENUM):2310-其它;2311-pc;2312-H5;2313-APP;2314-小程序;2315-公众号
            'user_new' => $request->input('user_new', 1) //新人标识(BOOL):0-不是;1-是
        ];
        $data = $this->userInfoService->edit($user_id, $user_info_row);

        return Respond::success($data);
    }


    /*
     * 修改密码
     */
    public function passWordEdit(Request $request)
    {
        $user_id = $request->input('user_id');
        $user_password = $request->input('user_password');

        $success = $this->userInfoService->passWordEdit($user_id, $user_password);
        if ($success) {
            return Respond::success(['user_id' => $user_id], __('密码修改成功'));
        }

        return Respond::error(__('密码修改失败'));
    }


    public function remove(Request $request)
    {
        $user_id = $request->input('user_id', 0);
        $data = $this->userInfoService->removeUser($user_id);

        return Respond::success($data);
    }


}
