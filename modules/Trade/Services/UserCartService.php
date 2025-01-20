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


namespace Modules\Trade\Services;

use App\Support\StateCode;
use Illuminate\Support\Facades\DB;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserDeliveryAddressRepository;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserLevelRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Pt\Services\ProductIndexService;
use Modules\Shop\Repositories\Contracts\UserVoucherRepository;
use Modules\Shop\Services\StoreTransportTypeService;
use Modules\Trade\Repositories\Contracts\UserCartRepository;
use App\Exceptions\ErrorException;

/**
 * Class UserCartService.
 *
 * @package Modules\Trade\Services
 */
class UserCartService extends BaseService
{

    private $productItemRepository;
    private $userInfoRepository;
    private $productIndexService;
    private $userLevelRepository;
    private $userVoucherRepository;
    private $userDeliveryAddressRepository;
    private $storeTransportTypeService;


    public function __construct(
        UserCartRepository            $userCartRepository,
        ProductItemRepository         $productItemRepository,
        UserInfoRepository            $userInfoRepository,
        UserLevelRepository           $userLevelRepository,
        UserVoucherRepository         $userVoucherRepository,
        UserDeliveryAddressRepository $userDeliveryAddressRepository,
        ProductIndexService           $productIndexService,
        StoreTransportTypeService     $storeTransportTypeService
    )
    {
        $this->repository = $userCartRepository;
        $this->productItemRepository = $productItemRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->userLevelRepository = $userLevelRepository;
        $this->userVoucherRepository = $userVoucherRepository;
        $this->userDeliveryAddressRepository = $userDeliveryAddressRepository;

        $this->productIndexService = $productIndexService;
        $this->storeTransportTypeService = $storeTransportTypeService;
    }


    /**
     * 加入购物车
     * @param $in
     * @return mixed
     * @throws ErrorException
     */
    public function addCart($in)
    {
        // todo 判断数量，提示加入购物车
        if (!isset($in['cart_quantity']) || $in['cart_quantity'] <= 0) {
            throw new ErrorException(__('最低数量 1 件，请确认！'));
        }

        $product_item = $this->productItemRepository->getOne($in['item_id']);
        if (!$product_item || $product_item['item_enable'] !== StateCode::PRODUCT_STATE_NORMAL) {
            throw new ErrorException(__('商品未上架，不可加入购物车！'));
        }

        // todo 判断可用库存
        $available_quantity = max(0, $product_item['available_quantity']);
        if ($in['cart_quantity'] > $available_quantity) {
            throw new ErrorException(sprintf(__('库存可用数量 %d 件，请确认！'), $available_quantity));
        }

        $activity_id = 0;
        $activity_item_id = 0;
        if (isset($in['activity_id'])) {
            $activity_id = $in['activity_id'];
        }
        if (isset($in['activity_item_id'])) {
            $activity_item_id = $in['activity_item_id'];
        }

        //todo 查询购物车是否存在该商品
        $cart_row = $this->repository->findOne([
            'user_id' => $in['user_id'],
            'item_id' => $in['item_id'],
            'activity_id' => $activity_id,
            'activity_item_id' => $activity_item_id
        ]);

        if (!empty($cart_row)) {
            $cur_quantity = $in['cart_quantity'] + $cart_row['cart_quantity'];
            if ($cur_quantity > $available_quantity) {
                throw new ErrorException(sprintf(__('库存可用数量 %d 件，请确认！'), $available_quantity));
            }

            //todo 修改购物车数据
            return $this->repository->edit($cart_row['cart_id'], [
                'cart_quantity' => $cur_quantity,
                'cart_select' => true
            ]);

        }

        //todo 新增
        $cart_data = [
            'product_id' => $product_item['product_id'],
            'item_id' => $in['item_id'],
            'user_id' => $in['user_id'],
            'cart_quantity' => $in['cart_quantity'],
            'activity_id' => $activity_id,
            'activity_item_id' => $activity_item_id
        ];

        return $this->repository->add($cart_data);
    }


    /**
     * 修改用户购物车数量
     * @param $cart_id
     * @param $user_id
     * @param $cart_quantity
     * @return int|mixed
     * @throws ErrorException
     */
    public function editQuantity($cart_id, $user_id, $cart_quantity)
    {
        DB::beginTransaction();

        try {
            $cart = $this->repository->getOne($cart_id);
            if ($cart && $cart['user_id'] == $user_id) {
                if ($cart_quantity == 0) {
                    $result = $this->repository->remove($cart_id);
                } else {
                    $product_item = $this->productItemRepository->getOne($cart['item_id']);
                    if (!$product_item) {
                        throw new ErrorException(__("该商品不存在！"));
                    }

                    $available_quantity = max(0, $product_item['available_quantity']);
                    if ($cart_quantity > $available_quantity) {
                        throw new ErrorException(__('库存可用数量') . $available_quantity);
                    }

                    $result = $this->repository->edit($cart_id, ['cart_quantity' => $cart_quantity]);
                }

                DB::commit();

                return $result;
            } else {
                throw new ErrorException(__("购物车不存在或用户不匹配"));
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw new ErrorException($e->getMessage());
        }
    }


    /**
     * 修改购物车状态
     * @param array $input
     * @param int $user_id
     * @return bool
     * @throws ErrorException
     */
    public function selCart(array $input, int $user_id): bool
    {
        $cart_select = $input['cart_select'];
        $action = $input['action'] ?? '';
        $cart_ids = [];

        // 根据操作类型获取购物车 ID 列表
        switch ($action) {
            case 'all':
                $cart_ids = $this->repository->findKey(['user_id' => $user_id]);
                break;

            case 'store':
                $store_id = $input['store_id'] ?? 0;
                $cart_ids = $this->repository->findKey(['user_id' => $user_id, 'store_id' => $store_id]);
                break;

            default:
                if (!isset($input['cart_id'])) {
                    throw new ErrorException(__('缺少 cart_id 参数'));
                }
                $cart_ids = [$input['cart_id']];
                break;
        }

        // 检查购物车 ID 列表是否为空
        if (empty($cart_ids)) {
            throw new ErrorException(__('未找到需要修改的购物车条目'));
        }

        // 更新购物车选中状态
        $result = $this->repository->edits($cart_ids, ['cart_select' => $cart_select]);
        if ($result === false) {
            throw new ErrorException(__('更改购物车选中状态失败'));
        }

        return true;
    }


    /**
     * 获取用户购物车列表
     * @param $request
     * @return array|mixed
     * @throws ErrorException
     */
    public function getCartList($request)
    {
        $cart_data = [
            'items' => [],
            'user_id' => 0
        ];

        $cond = [];
        if (isset($request['user_id'])) {
            $cond['user_id'] = $request['user_id'];
            $cart_data['user_id'] = $request['user_id'];
        }

        if (isset($request['cart_select'])) {
            $cond['cart_select'] = $request['cart_select'];
        }

        $user_carts = $this->repository->find($cond);
        if (!empty($user_carts)) {
            $cart_data['items'] = $user_carts;
            $cart_data = $this->formatCartRows($cart_data);;
        }

        return $cart_data;
    }


    /**
     * 订单结算数据
     * @param $request
     * @param $user_id
     * @return array|mixed
     * @throws ErrorException
     */
    public function checkout($request, $user_id)
    {
        $cart_data = [
            'items' => [],
            'user_id' => $user_id
        ];

        $from_cart = 1;
        $cart_id_str = $request->get('cart_id', '');
        $cart_items = explode(',', $cart_id_str);
        $checkout_items = [];
        $cart_ids = [];

        if (!empty($cart_items)) {
            foreach ($cart_items as $item) {
                $cart_item = explode("|", $item);
                $item_id = (int)$cart_item[0];
                $cart_quantity = (int)$cart_item[1];
                $cart_id = (int)$cart_item[2];

                if ($cart_quantity <= 0) {
                    throw new ErrorException(__("购买数量最低为 1 哦~"));
                }

                $checkout_items[] = [
                    'cart_id' => $cart_id,
                    'item_id' => $item_id,
                    'cart_quantity' => $cart_quantity,
                    'cart_select' => 1
                ];
                $cart_ids[] = $cart_id;

                if ($cart_id == 0) {
                    $from_cart = 0;
                    break;
                }

            }
        } else {
            throw new ErrorException(__('请选择商品'));
        }

        if ($from_cart == 1) {
            $user_carts = $this->repository->find([
                'user_id' => $user_id,
                'cart_select' => 1,
                ['cart_id', 'IN', $cart_ids]
            ]);
        } else {
            $user_carts = $checkout_items;
        }

        if (!empty($user_carts)) {
            $cart_data['items'] = $user_carts;
            if (isset($request['user_voucher_ids'])) {
                $cart_data['user_voucher_ids'] = explode(',', $request['user_voucher_ids']);
            }
            $cart_data['is_delivery'] = $request->input('is_delivery', false);
            $cart_data['delivery_type_id'] = $request->input('delivery_type_id', 0);
            $cart_data['ud_id'] = $request->input('ud_id', 0);
            if (isset($request['user_delivery_address'])) {
                $cart_data['user_delivery_address'] = $request['user_delivery_address'];
            }
            $cart_data = $this->formatCartRows($cart_data);
        }

        return $cart_data;
    }


    /**
     * 格式化购物车数据
     * @param $cart_rows
     * @return array|mixed
     * @throws ErrorException
     */
    public function formatCartRows($cart_rows = [])
    {
        $cart_data = $cart_rows;

        //todo 用户有效性判断
        $user_id = $cart_data['user_id'];
        $user_info = $this->userInfoRepository->getOne($user_id);
        if (empty($user_info)) {
            throw new ErrorException(__("用户信息不存在！"));
        }
        $cart_rows['user_nickname'] = $user_info['user_nickname'];

        // todo 获取用户等级折扣
        $user_level_rate = 100;
        if ($user_id && !empty($user_info)) {
            $user_level_row = $this->userLevelRepository->getOne($user_info['user_level_id']);
            if (!empty($user_level_row)) {
                $user_level_rate = $user_level_row['user_level_rate'] ?? 100;
            }
        }

        $order_product_amount = 0; // 商品订单原价
        $order_item_amount = 0; // 单品优惠后价格累加
        $order_freight_amount = 0; // 订单运费
        $order_money_amount = 0; // 订单总价
        $order_discount_amount = 0; // 订单折扣价格
        $order_points_amount = 0; // 积分总价
        $order_sp_amount = 0;

        $item_ids = array_column_unique($cart_data['items'], 'item_id');
        $cart_product_items = $this->productIndexService->getItems($item_ids, $user_id);

        //根据店铺分组
        $store_rows[] = [
            "store_id" => 0,
            "store_name" => '平台自营',
            "activitys" => [
                'gift' => [],
                'reduction' => [],
                'multple' => [],
                'bargains' => []
            ],
            "activity_base" => [],
            "redemption_items" => [],
            "voucher_items" => [],
            "freight_free_balance" => ''
        ];

        //todo 获取用户店铺未使用优惠券
        $time = getTime();
        $user_store_voucher_rows[0] = $this->userVoucherRepository->find([
            'user_id' => $user_id,
            'voucher_state_id' => StateCode::VOUCHER_STATE_UNUSED,
            'store_id' => 0,
            ['voucher_start_date', '<=', $time],
            ['voucher_end_date', '>=', $time]
        ]);

        foreach ($store_rows as $k => $store_row) {
            $product_amount = 0; //商品原价
            $freight_amount = 0; //运费总额
            $discount_amount = 0; //折扣总额
            $money_item_amount = 0; //商品金额
            $money_amount = 0;
            $points_amount = 0; //积分总额
            $sp_amount = 0;
            $user_voucher_id = 0; //用户优惠券ID
            $voucher_amount = 0; //优惠总额
            $kind_id = 1201;
            $is_virtual = false;

            foreach ($cart_data['items'] as $item) {
                if (isset($cart_product_items[$item['item_id']])) {
                    $item = array_merge($item, $cart_product_items[$item['item_id']]);
                } else {
                    throw new ErrorException(__('商品不存在') . $item['item_id']);
                }

                //前端所需数据
                $item['pulse_gift_cart'] = [];
                $item['pulse_reduction'] = [];
                $item['pulse_multple'] = [];
                $item['pulse_bargains_cart'] = [];
                $item['pulse_bargains'] = [];

                $item['item_discount_amount'] = 0;
                $item['item_save_price'] = 0;

                if (isset($item['activity_id']) && isset($item['activity_item_price'])) {
                    $item['item_save_price'] = bcsub($item['item_unit_price'], $item['activity_item_price'], 2);
                    $item['item_discount_amount'] = bcmul($item['item_save_price'], $item['cart_quantity'], 2);
                } else {
                    //todo 商品会员价
                    if ($user_level_rate && $user_level_rate != 100) {
                        $item['item_sale_price'] = round($item['item_unit_price'] * $user_level_rate / 100, 2);
                        $item['item_save_price'] = bcsub($item['item_unit_price'], $item['item_sale_price'], 2);
                        $item['item_discount_amount'] = bcmul($item['item_save_price'], $item['cart_quantity'], 2);
                    }
                }

                if ($item['store_id'] == $store_row['store_id']) {
                    $item['item_subtotal'] = round($item['item_sale_price'] * $item['cart_quantity'], 2);
                    $item['item_unit_subtotal'] = round($item['item_unit_price'] * $item['cart_quantity'], 2);
                    $item['item_points_subtotal'] = round($item['item_unit_points'] * $item['cart_quantity'], 2);

                    $store_rows[$k]['items'][] = $item;

                    if ($item['cart_select']) {
                        $product_amount += $item['item_unit_subtotal'];
                        $discount_amount += $item['item_discount_amount'];
                        $money_item_amount += $item['item_subtotal'];
                        $points_amount += $item['item_points_subtotal'];
                    }
                }
            }

            $store_rows[$k]['kind_id'] = $kind_id;
            $store_rows[$k]['product_amount'] = $product_amount;
            $store_rows[$k]['money_item_amount'] = $money_item_amount;
            $store_rows[$k]['freight_amount'] = $freight_amount;
            $store_rows[$k]['discount_amount'] = $discount_amount;
            $store_rows[$k]['points_amount'] = $points_amount;
            $store_rows[$k]['money_amount'] = $money_item_amount;

            //todo 用户当前店铺可用优惠券
            $store_rows[$k]['voucher_items'] = [];
            if (isset($user_store_voucher_rows[$store_row['store_id']])) {
                $voucher_items = $user_store_voucher_rows[$store_row['store_id']];
                $voucher_items = $this->userVoucherRepository->filterUserVouchers($store_rows[$k], $voucher_items);
                $store_rows[$k]['voucher_items'] = array_values($voucher_items);

                //用户优惠券金额信息
                if (!empty($cart_data['user_voucher_ids']) && !empty($voucher_items)) {
                    $user_voucher_info = $this->userVoucherRepository->getVoucherInfo($cart_data['user_voucher_ids'], $voucher_items);
                    $user_voucher_id = $user_voucher_info['user_voucher_id'];
                    $voucher_amount = $user_voucher_info['voucher_price'];
                }
            }
            $store_rows[$k]['user_voucher_id'] = $user_voucher_id;
            $store_rows[$k]['voucher_amount'] = $voucher_amount;

            if ($voucher_amount > 0) {
                $store_rows[$k]['money_amount'] = round($store_rows[$k]['money_amount'] - $voucher_amount, 2);
            }

            if (isset($cart_data['is_delivery'])) {
                $is_delivery = $cart_data['is_delivery'];
                if ($is_virtual) {
                    $is_delivery = false;
                }
                if ($is_delivery && isset($cart_data['ud_id'])) {
                    if (isset($cart_data['user_delivery_address']) && !empty($cart_data['user_delivery_address'])) {
                        $user_delivery_address = $cart_data['user_delivery_address'];
                    } else {
                        $user_delivery_address = $this->userDeliveryAddressRepository->getOne($cart_data['ud_id']);
                    }
                    if (!empty($user_delivery_address)) {
                        $freight_data = $this->storeTransportTypeService->calTransportFreight($store_rows[$k], $user_delivery_address['ud_city_id']);
                        $freight_amount = $freight_data['freight'];
                    }
                    $store_rows[$k]['freight_amount'] = $freight_amount;
                }
                if ($freight_amount > 0) {
                    $store_rows[$k]['money_amount'] = round($store_rows[$k]['money_amount'] + $freight_amount, 2);
                }
            }

            $order_product_amount += $store_rows[$k]['product_amount'];
            $order_item_amount += $store_rows[$k]['money_item_amount'];
            $order_freight_amount += $store_rows[$k]['freight_amount'];
            $order_money_amount += $store_rows[$k]['money_amount'];
            $order_discount_amount += $store_rows[$k]['discount_amount'];
            $order_points_amount += $store_rows[$k]['points_amount'];
            $order_sp_amount += $sp_amount;

        }

        $cart_rows['items'] = $store_rows;
        $cart_rows['order_product_amount'] = $order_product_amount;
        $cart_rows['order_item_amount'] = $order_item_amount;
        $cart_rows['order_freight_amount'] = $order_freight_amount;
        $cart_rows['order_money_amount'] = $order_money_amount;
        $cart_rows['order_discount_amount'] = $order_discount_amount;
        $cart_rows['order_points_amount'] = $order_points_amount;
        $cart_rows['order_sp_amount'] = $order_sp_amount;

        return $cart_rows;
    }

}
