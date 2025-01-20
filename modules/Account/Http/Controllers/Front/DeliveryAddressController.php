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

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Criteria\UserDeliveryAddressCriteria;
use Modules\Account\Repositories\Validators\UserDeliveryAddressValidator;
use Modules\Account\Services\UserDeliveryAddressService;

class DeliveryAddressController extends BaseController
{

    private $userId;
    private $userDeliveryAddressService;
    private $userDeliveryAddressValidator;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserDeliveryAddressService $userDeliveryAddressService, UserDeliveryAddressValidator $userDeliveryAddressValidator)
    {
        $this->userId = checkLoginUserId();

        $this->userDeliveryAddressService = $userDeliveryAddressService;
        $this->userDeliveryAddressValidator = $userDeliveryAddressValidator;
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request['user_id'] = $this->userId;
        $data = $this->userDeliveryAddressService->list($request, new UserDeliveryAddressCriteria($request));

        return Respond::success($data);
    }


    /**
     * get
     */
    public function get(Request $request)
    {
        $ud_id = $request->input('ud_id', 0);
        $data = $this->userDeliveryAddressService->get($ud_id);

        return Respond::success($data);
    }


    /**
     * 新增
     */
    public function add(Request $request)
    {
        $this->userDeliveryAddressValidator->with($request->all())->passesOrFail('create');

        $request['user_id'] = $this->userId;
        $data = $this->userDeliveryAddressService->saveAddress($request);

        return Respond::success($data);
    }


    /**
     * 修改
     */
    public function save(Request $request)
    {
        $ud_id = $request['ud_id'];
        $row = $this->userDeliveryAddressService->get($ud_id);
        checkDataRights($this->userId, $row);

        $this->userDeliveryAddressValidator->with($request->all())->passesOrFail('update');

        $request['user_id'] = $this->userId;
        $data = $this->userDeliveryAddressService->saveAddress($request, $ud_id);

        return Respond::success($data);
    }


    /**
     * 删除
     */
    public function remove(Request $request)
    {
        $row = $this->userDeliveryAddressService->get($request['ud_id']);
        checkDataRights($this->userId, $row);

        $data = $this->userDeliveryAddressService->remove($request['ud_id']);

        return Respond::success($data);
    }


}
