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


namespace Modules\Admin\Http\Controllers\Manage;

use App\Exceptions\ErrorException;
use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Services\UserService;
use Modules\Admin\Repositories\Criteria\UserAdminCriteria;
use Modules\Admin\Repositories\Validators\UserAdminValidator;
use Modules\Admin\Services\UserAdminService;

class UserAdminController extends BaseController
{
    private $userService;
    private $userAdminService;
    private $userAdminValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        UserService        $userService,
        UserAdminService   $userAdminService,
        UserAdminValidator $userAdminValidator
    )
    {
        $this->userService = $userService;
        $this->userAdminService = $userAdminService;
        $this->userAdminValidator = $userAdminValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $data = $this->userAdminService->list($request, new UserAdminCriteria($request));

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userAdminValidator->with($request->all())->passesOrFail('create');
        $user_id = $request->input('user_id', -1);
        $user_base = $this->userService->get($user_id);
        if (empty($user_base)) {
            throw new ErrorException(__('当前用户编号不存在'));
        }

        $user_admin = $this->userAdminService->get($user_id);
        if (!empty($user_admin)) {
            throw new ErrorException(__('系统用户已存在'));
        }

        $request = [
            'user_id' => $user_id,
            'user_role_id' => $request->input('user_role_id', 0),
            'role_id' => $request->input('role_id', 0),
            'chain_id' => $request->input('chain_id', 0)
        ];

        $data = $this->userAdminService->add($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function edit(Request $request)
    {
        $user_id = $request->input('user_id', -1);
        $this->userAdminValidator->setId($user_id);
        $this->userAdminValidator->with($request->all())->passesOrFail('update');

        $user_admin = $this->userAdminService->get($user_id);
        if (empty($user_admin)) {
            throw new ErrorException(__('系统用户不存在'));
        }

        $formatted_request = [
            'user_role_id' => $request->input('user_role_id', 0),
            'role_id' => $request->input('role_id', 0),
            'chain_id' => $request->input('chain_id', 0)
        ];

        $data = $this->userAdminService->edit($user_id, $formatted_request);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $user_id = $request->input('user_id', -1);
        $user_admin = $this->userAdminService->get($user_id);
        if ($user_admin['user_is_superadmin']) {
            throw new ErrorException(__('系统内置，不可删除'));
        }
        $data = $this->userAdminService->remove($user_id);

        return Respond::success($data);
    }

}
