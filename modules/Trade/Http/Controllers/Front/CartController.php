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


namespace Modules\Trade\Http\Controllers\Front;

use App\Support\Respond;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Modules\Account\Repositories\Models\User;
use Modules\Trade\Services\UserCartService;
use Modules\Account\Services\UserDeliveryAddressService;

class CartController extends BaseController
{
    private $userCartService;
    private $userDeliveryAddressService;
    private $userId;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserCartService $userCartService, UserDeliveryAddressService $userDeliveryAddressService)
    {
        $this->userCartService = $userCartService;
        $this->userDeliveryAddressService = $userDeliveryAddressService;

        $this->userId = User::getUserId();
    }


    /**
     * 列表
     */
    public function list(Request $request)
    {
        $request = $request->all();
        $request['user_id'] = $this->userId;
        $data = $this->userCartService->getCartList($request);

        return Respond::success($data);
    }


    /**
     * 添加购物车
     */
    public function add(Request $request)
    {
        $user_cart = $request->all();
        $user_cart['user_id'] = $this->userId;

        // 调用用户购物车服务添加购物车
        $flag = $this->userCartService->addCart($user_cart);
        if ($flag) {
            return Respond::success($user_cart);
        } else {
            return Respond::error();
        }
    }


    /**
     * 修改购物车数量
     */
    public function editQuantity(Request $request)
    {
        $cart_id = $request->input('cart_id', 0);
        $cart_quantity = $request->input('cart_quantity', 1);

        try {
            $result = $this->userCartService->editQuantity($cart_id, $this->userId, $cart_quantity);
            return Respond::success($result);
        } catch (\Exception $e) {
            return Respond::error($e->getMessage());
        }
    }


    /**
     * 修改购物车选中状态
     */
    public function sel(Request $request)
    {
        $input = $request->all();
        $input['cart_select'] = $request->boolean('cart_select');

        try {
            $result = $this->userCartService->selCart($input, $this->userId);
            return Respond::success($result);
        } catch (\Exception $e) {
            return Respond::error($e->getMessage());
        }
    }


    /**
     * 购物车结算页面
     */
    public function checkout(Request $request)
    {
        $ud_id = $request->input('ud_id', 0);
        //todo 获取用户的收货地址
        $user_delivery_address = $this->userDeliveryAddressService->getOneAddress($ud_id, $this->userId);
        $request['user_delivery_address'] = $user_delivery_address;
        $data = $this->userCartService->checkout($request, $this->userId);

        return Respond::success($data);
    }


    /**
     * 删除购物车
     */
    public function remove(Request $request)
    {
        $data = $this->userCartService->remove($request->get('cart_id'));

        return Respond::success($data);
    }


    /**
     * 批量删除购物车
     */
    public function removeBatch(Request $request)
    {
        $cart_id = $request->get('cart_id', '');
        $cart_ids = explode(',', $cart_id);
        if (!empty($cart_ids)) {
            $this->userCartService->remove($cart_ids);
        }

        return Respond::success($cart_ids);
    }

}
