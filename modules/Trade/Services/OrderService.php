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
use Illuminate\Support\Facades\Log;
use Kuteshop\Core\Service\BaseService;
use Modules\Account\Repositories\Contracts\UserDeliveryAddressRepository;
use Modules\Account\Repositories\Contracts\UserInfoRepository;
use Modules\Account\Repositories\Contracts\UserInvoiceRepository;
use Modules\Invoicing\Services\StockBillService;
use Modules\Pay\Repositories\Contracts\ConsumeRecordRepository;
use Modules\Pay\Repositories\Contracts\ConsumeTradeRepository;
use Modules\Pt\Repositories\Contracts\ProductIndexRepository;
use Modules\Pt\Repositories\Contracts\ProductItemRepository;
use Modules\Shop\Repositories\Contracts\StoreExpressLogisticsRepository;
use Modules\Shop\Repositories\Contracts\UserVoucherRepository;
use Modules\Sys\Repositories\Contracts\ConfigBaseRepository;
use Modules\Sys\Services\NumberSeqService;
use Modules\Trade\Jobs\ProcessOrderJob;
use Modules\Trade\Repositories\Contracts\OrderBaseRepository;
use Modules\Trade\Repositories\Contracts\OrderDataRepository;
use Modules\Trade\Repositories\Contracts\OrderDeliveryAddressRepository;
use Modules\Trade\Repositories\Contracts\OrderInfoRepository;

use App\Exceptions\ErrorException;
use Modules\Trade\Repositories\Contracts\OrderInvoiceRepository;
use Modules\Trade\Repositories\Contracts\OrderItemRepository;
use Modules\Trade\Repositories\Contracts\OrderLogisticsRepository;
use Modules\Trade\Repositories\Contracts\OrderReturnItemRepository;
use Modules\Trade\Repositories\Contracts\OrderReturnRepository;
use Modules\Trade\Repositories\Contracts\OrderStateLogRepository;
use Modules\Trade\Repositories\Contracts\UserCartRepository;
use Modules\Account\Repositories\Models\User;

/**
 * Class OrderService.
 *
 * @package Modules\Trade\Services
 */
class OrderService extends BaseService
{

    private $orderBaseRepository;
    private $orderItemRepository;
    private $orderDataRepository;
    private $orderDeliveryAddressRepository;
    private $orderLogisticsRepository;
    private $productItemRepository;
    private $consumeRecordRepository;
    private $consumeTradeRepository;
    private $configBaseRepository;
    private $orderStateLogRepository;
    private $orderReturnRepository;
    private $orderReturnItemRepository;
    private $userCartRepository;
    private $userDeliveryAddressRepository;
    private $userVoucherRepository;
    private $userInfoRepository;
    private $storeExpressLogisticsRepository;
    private $orderInvoiceRepository;
    private $userInvoiceRepository;
    private $productIndexRepository;

    private $stockBillService;
    private $numberSeqService;

    public function __construct(
        OrderInfoRepository             $orderInfoRepository,
        OrderBaseRepository             $orderBaseRepository,
        OrderItemRepository             $orderItemRepository,
        OrderDataRepository             $orderDataRepository,
        OrderDeliveryAddressRepository  $orderDeliveryAddressRepository,
        OrderLogisticsRepository        $orderLogisticsRepository,
        ProductItemRepository           $productItemRepository,
        ConsumeRecordRepository         $consumeRecordRepository,
        ConsumeTradeRepository          $consumeTradeRepository,
        ConfigBaseRepository            $configBaseRepository,
        OrderStateLogRepository         $orderStateLogRepository,
        OrderReturnRepository           $orderReturnRepository,
        OrderReturnItemRepository       $orderReturnItemRepository,
        UserCartRepository              $userCartRepository,
        UserDeliveryAddressRepository   $userDeliveryAddressRepository,
        UserVoucherRepository           $userVoucherRepository,
        UserInfoRepository              $userInfoRepository,
        StoreExpressLogisticsRepository $storeExpressLogisticsRepository,
        OrderInvoiceRepository          $orderInvoiceRepository,
        UserInvoiceRepository           $userInvoiceRepository,
        ProductIndexRepository          $productIndexRepository,

        StockBillService                $stockBillService,
        NumberSeqService                $numberSeqService
    )
    {
        $this->repository = $orderInfoRepository;
        $this->orderBaseRepository = $orderBaseRepository;
        $this->orderItemRepository = $orderItemRepository;
        $this->orderDataRepository = $orderDataRepository;
        $this->orderDeliveryAddressRepository = $orderDeliveryAddressRepository;
        $this->orderLogisticsRepository = $orderLogisticsRepository;
        $this->productItemRepository = $productItemRepository;
        $this->consumeRecordRepository = $consumeRecordRepository;
        $this->consumeTradeRepository = $consumeTradeRepository;
        $this->configBaseRepository = $configBaseRepository;
        $this->orderStateLogRepository = $orderStateLogRepository;
        $this->orderReturnRepository = $orderReturnRepository;
        $this->orderReturnItemRepository = $orderReturnItemRepository;
        $this->userCartRepository = $userCartRepository;
        $this->userDeliveryAddressRepository = $userDeliveryAddressRepository;
        $this->userVoucherRepository = $userVoucherRepository;
        $this->userInfoRepository = $userInfoRepository;
        $this->storeExpressLogisticsRepository = $storeExpressLogisticsRepository;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->userInvoiceRepository = $userInvoiceRepository;
        $this->productIndexRepository = $productIndexRepository;

        $this->stockBillService = $stockBillService;
        $this->numberSeqService = $numberSeqService;
    }


    /**
     * 获取列表
     */
    public function list($request, $criteria)
    {
        $limit = $request->get('size') ?? 10;
        $data = $this->repository->list($criteria, $limit);

        $order_info_list = $data['data'];
        if (!empty($order_info_list)) {
            $order_ids = array_column($order_info_list, 'order_id');

            //todo 获取 OrderBase 信息
            $order_base_list = $this->orderBaseRepository->gets($order_ids);

            //todo 获取 订单商品 信息
            $order_item_list = $this->orderItemRepository->find([['order_id', 'IN', $order_ids]]);

            //todo 订单商品数据 处理为map
            $order_items = [];
            foreach ($order_item_list as $order_item) {
                if (!array_key_exists($order_item['order_id'], $order_items)) {
                    $order_items[$order_item['order_id']] = [];
                }
                $order_items[$order_item['order_id']][] = $order_item;
            }

            //todo 获取 支付交易订单
            $consume_trades = $this->consumeTradeRepository->find([['order_id', 'IN', $order_ids]]);
            $consume_trades = array_column($consume_trades, null, 'order_id');

            //todo 获取订单发票信息
            $order_invoice_list = $this->orderInvoiceRepository->find([['order_id', 'IN', $order_ids]]);
            $order_invoice_rows = arrayMap($order_invoice_list, 'order_id');

            //todo 增加订单列表数据
            foreach ($order_info_list as $order_key => $order_info) {
                $order_id = $order_info['order_id'];
                $order_info_list[$order_key]['order_number'] = $order_base_list[$order_id]['order_number'];
                $order_info_list[$order_key]['order_time'] = $order_base_list[$order_id]['order_time'];
                $order_info_list[$order_key]['order_product_amount'] = $order_base_list[$order_id]['order_product_amount'];
                $order_info_list[$order_key]['order_payment_amount'] = $order_base_list[$order_id]['order_payment_amount'];
                $order_info_list[$order_key]['currency_id'] = $order_base_list[$order_id]['currency_id'];
                $order_info_list[$order_key]['currency_symbol_left'] = $order_base_list[$order_id]['currency_symbol_left'];
                $order_info_list[$order_key]['store_name'] = $order_base_list[$order_id]['store_name'];
                $order_info_list[$order_key]['user_nickname'] = $order_base_list[$order_id]['user_nickname'];

                //订单商品
                $order_info_list[$order_key]['items'] = [];
                if (isset($order_items[$order_id])) {
                    $order_info_list[$order_key]['items'] = $order_items[$order_id];
                }
                $order_info_list[$order_key]['trade_payment_amount'] = isset($consume_trades[$order_id]) ? $consume_trades[$order_id]['trade_payment_amount'] : 0; //需要支付金额

                //是否申请开票
                $order_info_list[$order_key]['invoice_is_apply'] = 0;
                if (!empty($order_invoice_rows) && isset($order_invoice_rows[$order_id])) {
                    $order_info_list[$order_key]['invoice_is_apply'] = 1;
                }
            }

            $data['data'] = $order_info_list;
        }

        $data['limit'] = $limit;

        return $data;
    }


    /**
     * 添加订单
     */
    public function addOrder($cart_data = [], $user_id)
    {

        $chain_id = (int)$cart_data['chain_id'];
        $subsite_id = (int)$cart_data['site_id'];
        $user_voucher_ids = $cart_data['user_voucher_ids'];
        $redemption_ids = $cart_data['redemption_ids'];
        $ud_id = (int)$cart_data['ud_id'];
        $delivery_type_id = $cart_data['delivery_type_id'];
        $is_delivery = $cart_data['is_delivery'];
        $payment_type_id = $cart_data['payment_type_id'];
        $delivery_time_id = $cart_data['delivery_time_id'];
        $invoice_type_id = $cart_data['invoice_type_id'];
        $order_invoice_title = $cart_data['order_invoice_title'];
        $order_message = $cart_data['order_message'];
        $virtual_service_date = $cart_data['virtual_service_date'];
        $virtual_service_time = $cart_data['virtual_service_time'];
        $salesperson_id = (int)$cart_data['salesperson_id'];
        $user_invoice_id = $cart_data['user_invoice_id'];
        $user_nickname = $cart_data['user_nickname'];
        $currency_id = $cart_data['currency_id'] != '' ?? 86;
        $order_ids = [];
        $cart_ids = [];
        //$user_voucher_ids = explode(',', $user_voucher_ids);

        $store_rows = $cart_data['items'];

        DB::beginTransaction();
        $cart_data['order_money_amount'] = 0;
        foreach ($store_rows as $store_row) {
            $store_id = $store_row['store_id'];
            $chain_id = 0;
            $voucher_price = 0;
            $user_voucher_id = 0;
            $voucher_item_amount = $store_row['money_item_amount'];
            $voucher_item_ids = []; //优惠券可用商品ID
            $voucher_item_num = count($store_row['items']); //参与均分优惠券的商品数量

            $order_title_rows = array_column($store_row['items'], 'product_name');
            $order_title = implode('|', $order_title_rows);
            $order_id = $this->numberSeqService->createNextSeq('JD');
            $activity_type_ids = array_column_unique($store_row['items'], 'activity_type_id');
            $activity_ids = array_column_unique($store_row['items'], 'activity_id');

            //优惠券使用判断
            if (!empty($store_row['voucher_items'])) {
                $voucher_items = $this->userVoucherRepository->filterUserVouchers($store_row, $store_row['voucher_items']);

                //用户优惠券金额信息
                if (!empty($user_voucher_ids) && !empty($voucher_items)) {
                    $user_voucher_info = $this->userVoucherRepository->getVoucherInfo($user_voucher_ids, $voucher_items);
                    if ($user_voucher_info['voucher_price'] && !empty($user_voucher_info['voucher_row'])) {
                        $user_voucher_id = $user_voucher_info['user_voucher_id'];
                        $voucher_price = $user_voucher_info['voucher_price'];
                        $voucher_item_ids = $user_voucher_info['voucher_row']['voucher_item_ids'];
                        $voucher_item_num = count($voucher_item_ids);
                        $voucher_item_amount = $user_voucher_info['voucher_row']['voucher_item_amount'];
                    }
                }
            }

            $order_payment_amount = $store_row['money_amount']; //订单应付金额（包含运费已减去优惠券金额）
            $order_payment_amount = max($order_payment_amount, 0);
            $cart_data['order_money_amount'] += $order_payment_amount;

            //todo 1、订单基础数据
            $order_base = [
                'order_id' => $order_id,
                'order_number' => '',
                'order_time' => getDateTime(),
                'order_product_amount' => $store_row['product_amount'], //商品原价总和:商品发布原价
                'order_payment_amount' => $order_payment_amount,
                'currency_id' => $currency_id,
                'currency_symbol_left' => '￥',
                'store_id' => $store_id,
                'store_name' => $store_row['store_name'],
                'user_id' => $user_id,
                'user_nickname' => $user_nickname,
                'order_state_id' => StateCode::ORDER_STATE_WAIT_PAY
            ];
            $order_base_result = $this->orderBaseRepository->add($order_base);
            if (!$order_base_result) {
                DB::rollBack();
                throw new ErrorException(__("订单基础信息保存失败"));
            }

            //todo 2、订单信息数据
            $order_info = [
                'order_id' => $order_id,
                'store_id' => $store_id,
                'order_title' => substr($order_title, 0, 190),
                'subsite_id' => $subsite_id,
                'user_id' => $user_id,
                'kind_id' => $store_row['kind_id'],
                'payment_type_id' => $payment_type_id,
                'order_state_id' => StateCode::ORDER_STATE_WAIT_PAY,
                'order_is_paid' => StateCode::ORDER_PAID_STATE_NO,
                'order_is_out' => StateCode::ORDER_PICKING_STATE_NO,
                'order_is_shipped' => StateCode::ORDER_SHIPPED_STATE_NO,
                'chain_id' => $chain_id,
                'delivery_type_id' => $delivery_type_id,
                'activity_id' => !empty($activity_ids) ? implode(',', $activity_ids) : '',
                'activity_type_id' => !empty($activity_type_ids) ? implode(',', $activity_type_ids) : '',
                'salesperson_id' => $salesperson_id,
                'store_type' => 1,
                'create_time' => getTime(),
                'update_time' => getTime(),
            ];
            $order_info_result = $this->repository->add($order_info);
            if (!$order_info_result) {
                DB::rollBack();
                throw new ErrorException(__("订单信息保存失败"));
            }

            //todo 3、订单数据
            $order_message = '';
            $order_data = [
                'order_id' => $order_id,
                'order_desc' => '',
                'order_delay_time' => 0,
                'delivery_type_id' => $delivery_type_id,
                'delivery_time_id' => 1,
                'delivery_time' => 0,
                'delivery_istimer' => 0,
                'order_message' => $order_message,
                'order_item_amount' => $store_row['money_item_amount'],
                'order_discount_amount' => $store_row['discount_amount'],
                'order_points_fee' => 0,
                'order_shipping_fee_amount' => $store_row['freight_amount'],
                'order_shipping_fee' => $store_row['freight_amount'],
                'voucher_id' => $user_voucher_id,
                'voucher_number' => '',
                'voucher_price' => $voucher_price,
                'redpacket_id' => 0,
                'redpacket_number' => '',
                'redpacket_price' => 0,
                'order_redpacket_price' => 0,
                'order_resource_ext1' => 0,
                'order_resource_ext2' => 0,
                'order_resource_ext3' => 0,
                'trade_payment_money' => 0,
                'trade_payment_recharge_card' => 0,
                'trade_payment_credit' => 0,
                'order_commission_fee' => 0,
                'order_points_add' => 0,
                'order_activity_data' => ''
            ];
            $order_data_result = $this->orderDataRepository->add($order_data);
            if (!$order_data_result) {
                DB::rollBack();
                throw new ErrorException(__("订单数据保存失败"));
            }

            //todo 4、订单商品数据
            $divided_num = 0;
            $divided_amount = 0;
            foreach ($store_row['items'] as $item_row) {

                //需要清除的购物车ID
                if ($item_row['cart_id']) {
                    $cart_ids[] = $item_row['cart_id'];
                }

                //均分优惠券
                $order_item_voucher = 0;
                if ($voucher_price > 0) {
                    if ((!empty($voucher_item_ids) && in_array($item_row['item_id'], $voucher_item_ids)) || empty($voucher_item_ids)) {
                        if (($divided_num + 1) == $voucher_item_num) {
                            $order_item_voucher = $voucher_price - $divided_amount;
                        } else {
                            $order_item_voucher = round($item_row['item_subtotal'] / $voucher_item_amount * $voucher_price, 2);
                            $divided_amount += $order_item_voucher;
                        }

                        $divided_num++;
                    }
                }
                $item_row['order_item_voucher'] = $order_item_voucher;

                $item_row['order_id'] = $order_id;
                $item_row['user_id'] = $user_id;
                $item_rows[] = $this->formatOrderItem($item_row);

                // todo 冻结商品库存
                if ($item_row['product_inventory_lock'] == 1001) {
                    $product_item_result = $this->productItemRepository->incrementFieldByIds([$item_row['item_id']], 'item_quantity_frozen', $item_row['cart_quantity']);
                    if (!$product_item_result) {
                        DB::rollBack();
                        throw new ErrorException(sprintf(__("更改: %s 冻结库存失败!"), $item_row['item_id']));
                    }
                }
            }
            if (!empty($item_rows)) {
                $order_item_result = $this->orderItemRepository->addBatch($item_rows);
                if (!$order_item_result) {
                    DB::rollBack();
                    throw new ErrorException(__("订单商品保存失败"));
                }
            }

            //todo 5、订单收货地址
            if ($ud_id) {
                $user_oder_delivery = $this->formatOrderDeliveryAddress($ud_id, $order_id);
                if (empty($user_oder_delivery)) {
                    DB::rollBack();
                    throw new ErrorException(__("用户收货地址有误"));
                }
                $order_delivery_result = $this->orderDeliveryAddressRepository->add($user_oder_delivery);
                if (!$order_delivery_result) {
                    DB::rollBack();
                    throw new ErrorException(__("订单收货地址保存失败"));
                }
            }

            //todo 6、创建交易订单
            $order_row = array_merge($order_base, $order_info, $order_data);
            $consume_trade_result = $this->consumeTradeRepository->createConsumeTrade($user_id, $order_row);
            if (!$consume_trade_result) {
                DB::rollBack();
                throw new ErrorException(__("交易订单创建失败"));
            }

            //todo 7、更新优惠券使用
            if ($user_voucher_id) {
                $this->userVoucherRepository->edit($user_voucher_id, [
                    'order_id' => $order_id,
                    'voucher_state_id' => StateCode::VOUCHER_STATE_USED,
                    'user_voucher_activetime' => getDateTime()
                ]);
            }

            $order_ids[] = $order_id;
        }

        //清空购物车数据
        if (!empty($cart_ids)) {
            $cart_remove_result = $this->userCartRepository->remove($cart_ids);
            if (!$cart_remove_result) {
                DB::rollBack();
                throw new ErrorException(__("购物车数据删除失败"));
            }
        }

        DB::commit();
        $cart_data['order_ids'] = $order_ids;

        return $cart_data;
    }


    /**
     * 格式化订单商品数据
     * @param $item_row
     * @return array
     */
    public function formatOrderItem($item_row)
    {
        $order_item_payment_amount = $item_row['item_subtotal'];
        $order_item_payment_amount = $order_item_payment_amount - $item_row['order_item_voucher'];
        $order_item_payment_amount = max(0, $order_item_payment_amount);

        $item = [
            'order_id' => $item_row['order_id'],
            'user_id' => $item_row['user_id'],
            'store_id' => $item_row['store_id'],
            'product_id' => $item_row['product_id'],
            'product_name' => $item_row['product_name'],
            'item_id' => $item_row['item_id'],
            'item_name' => $item_row['item_name'],
            'category_id' => $item_row['category_id'],
            'item_cost_price' => $item_row['item_cost_price'],
            'item_unit_price' => $item_row['item_unit_price'],
            'item_unit_points' => $item_row['item_unit_points'],
            'item_unit_sp' => 0,
            'order_item_sale_price' => $item_row['item_sale_price'],
            'order_item_quantity' => $item_row['cart_quantity'],
            'order_item_inventory_lock' => $item_row['product_inventory_lock'],
            'order_item_image' => $item_row['product_image'],
            'order_item_amount' => $item_row['item_subtotal'],
            'order_item_discount_amount' => $item_row['item_discount_amount'],
            'order_item_points_fee' => 0,
            'order_item_points_add' => 0,
            'order_item_payment_amount' => $order_item_payment_amount,
            'activity_type_id' => $item_row['activity_type_id'] ?? 0,
            'activity_id' => $item_row['activity_id'] ?? 0,
            'activity_code' => '',
            'order_item_commission_rate' => 0,
            'order_item_commission_fee' => 0,
            'policy_discountrate' => 100,
            'order_item_voucher' => $item_row['order_item_voucher'],
            'order_item_reduce' => 0,
            'order_item_note' => '',
            'order_give_id' => 0
        ];

        return $item;
    }


    /**
     * 获取用户收货地址
     * @param $ud_id
     * @param $order_id
     * @return array
     */
    public function formatOrderDeliveryAddress($ud_id = -1, $order_id = '')
    {
        $data = [];
        $row = $this->userDeliveryAddressRepository->getOne($ud_id);
        if (!empty($row)) {
            $data = [
                'order_id' => $order_id,
                'da_name' => $row['ud_name'],
                'da_intl' => $row['ud_intl'],
                'da_mobile' => $row['ud_mobile'],
                'da_telephone' => $row['ud_telephone'],
                'da_province_id' => $row['ud_province_id'],
                'da_province' => $row['ud_province'],
                'da_city_id' => $row['ud_city_id'],
                'da_city' => $row['ud_city'],
                'da_county_id' => $row['ud_county_id'],
                'da_county' => $row['ud_county'],
                'da_address' => $row['ud_address'],
                'da_postalcode' => $row['ud_postalcode'] ?? '',
                'da_tag_name' => $row['ud_tag_name'] ?? '',
                'da_time' => getDateTime(),
                'da_longitude' => $row['ud_longitude'],
                'da_latitude' => $row['ud_latitude']
            ];
        }

        return $data;
    }


    /**
     * 获取订单详情
     * @param $order_id
     * @return array
     */
    public function detail($order_id = null)
    {
        $detail = [];

        //todo 订单信息表
        $order_info = $this->repository->getOne($order_id);
        if (!empty($order_info)) {
            $detail = $order_info;
        }

        //todo 获取订单Data数据
        $order_data = $this->orderDataRepository->getOne($order_id);
        if (!empty($order_data)) {
            $detail = array_merge($detail, $order_data);
        }

        //todo 获取订单基础表数据
        $order_base = $this->orderBaseRepository->getOne($order_id);
        if (!empty($order_base)) {
            $detail = array_merge($detail, $order_base);
        }

        //退单信息
        $return_items = $this->orderReturnItemRepository->find(['order_id' => $order_id, ['return_state_id', '!=', StateCode::RETURN_PROCESS_CANCEL]]);
        $return_item_rows = arrayMap($return_items, 'order_item_id');

        //todo 读取订单商品
        $order_items = $this->orderItemRepository->find(['order_id' => $order_id]);
        foreach ($order_items as $k => $order_item) {
            //是否可以退货
            if ($order_item['order_item_payment_amount'] > $order_item['order_item_return_subtotal'] &&
                $this->ifReturn($order_info['order_state_id'], $order_info['order_is_paid'])) {
                $order_items[$k]['if_return'] = 1;
            } else {
                $order_items[$k]['if_return'] = 0;
            }

            //退单ID
            if (!empty($return_item_rows) && isset($return_item_rows[$order_item['order_item_id']])) {
                $order_items[$k]['return_ids'] = array_column($return_item_rows[$order_item['order_item_id']], 'return_id');
            }

            $order_items[$k]['product_item_name'] = $order_item['product_name'] . ' ' . $order_item['item_name'];
        }
        $detail['items'] = array_values($order_items);

        //todo 获取当前商品库存
        $item_ids = array_column($order_items, 'item_id');
        $product_items = $this->productItemRepository->find([['item_id', 'IN', $item_ids]]);
        $detail['warehouse_items'] = [];

        foreach ($product_items as $item) {
            $i = [];
            $i['warehouse_id'] = 0;
            $i['item_id'] = $item['item_id'];
            $i['warehouse_item_quantity'] = $item['item_quantity'];

            $detail['warehouse_items'][] = $i;
        }

        //todo 获取用户 配送地址
        $order_delivery_address = $this->orderDeliveryAddressRepository->getOne($order_id);
        $detail['delivery'] = $order_delivery_address;

        //todo 获取 物流记录
        $order_logistics = $this->orderLogisticsRepository->find(['order_id' => $order_id]);
        $detail['logistics'] = array_values($order_logistics);

        //todo 获取 出库StockBill
        $stock_bill = $this->stockBillService->findDetail([$order_id]);
        $detail['stock_bill'] = array_values($stock_bill);

        //todo 获取交易记录 consumeRecord
        $consume_record = $this->consumeRecordRepository->find([
            ['order_id', '=', $order_id],
            ['trade_type_id', '=', StateCode::TRADE_TYPE_SALES]
        ]);
        $detail['consume_record'] = array_values($consume_record);

        //todo 获取交易订单 ConsumeTrade
        $consume_trades = $this->consumeTradeRepository->find(['order_id' => $order_id]);
        if (!empty($consume_trades)) {
            $detail['consume_trade'] = current($consume_trades);
        }

        return $detail;
    }


    /**
     * 更新订单支付状态
     * @param $order_id
     * @return bool
     * @throws ErrorException
     */
    public function setPaidYes($order_id = null)
    {
        $order_info = $this->repository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__('订单信息不存在'));
        }
        $order_base = $this->orderBaseRepository->getOne($order_id);
        if (empty($order_base)) {
            throw new ErrorException(__('订单基础信息不存在'));
        }

        if ($order_info['order_state_id'] == StateCode::ORDER_STATE_WAIT_PAY && $order_info['order_is_paid'] != StateCode::ORDER_PAID_STATE_YES) {
            // todo 获取订单的下一条状态
            $next_order_state_id = $this->configBaseRepository->getNextOrderStateId($order_info['order_state_id']);

            //todo 1、更新订单状态
            $flag = $this->editNextState($order_id, $order_info['order_state_id'], $next_order_state_id, "");
            if (!$flag) {
                return false;
            }

            //todo 2、更新订单支付状态
            $result = $this->repository->edit($order_id, ['order_is_paid' => StateCode::ORDER_PAID_STATE_YES]);
            if (!$result) {
                throw new ErrorException(__("更新支付状态失败！"));
            }
        } else {
            throw new ErrorException(__("未更改到符合条件的订单！"));
        }

        // todo 订单商品 更新库存和销量
        $order_items = $this->orderItemRepository->find(['order_id' => $order_id]);
        if (!empty($order_items)) {
            $item_ids = array_column($order_items, 'item_id');
            $item_rows = $this->productItemRepository->gets($item_ids);
            $product_ids = array_column($order_items, 'product_id');
            $product_rows = $this->productIndexRepository->gets($product_ids);

            foreach ($order_items as $order_item) {
                // 1. 更新商品库存数量
                if (isset($item_rows[$order_item['item_id']])) {
                    if ($order_item['order_item_inventory_lock'] == 1002) {
                        try {
                            $item_row = $item_rows[$order_item['item_id']];
                            if ($item_row['available_quantity'] >= $order_item['order_item_quantity']) {
                                $this->productItemRepository->incrementFieldByIds([$order_item['item_id']], 'item_quantity_frozen', $order_item['order_item_quantity']);
                                Log::info("更新商品库存", [
                                    'item_id' => $order_item['item_id'],
                                    'order_item_quantity' => $order_item['order_item_quantity']
                                ]);
                            } else {
                                throw new ErrorException(__("商品SKU库存不足") . $order_item['item_id']);
                            }
                        } catch (\Exception $e) {
                            throw new ErrorException(__('更新商品库存: ') . $e->getMessage());
                        }
                    }
                } else {
                    throw new ErrorException(__("商品SKU不存在") . $order_item['item_id']);
                }


                // 2. 更新商品销量
                if ($order_item['product_id'] && isset($product_rows[$order_item['product_id']])) {
                    $this->productIndexRepository->incrementFieldByIds([$order_item['product_id']], 'product_sale_num', $order_item['order_item_quantity']);
                    Log::info("更新商品销量", [
                        'product_id' => $order_item['product_id'],
                        'order_item_quantity' => $order_item['order_item_quantity']
                    ]);
                } else {
                    Log::error("商品SPU信息不存在", ['product_id' => $order_item['product_id']]);
                }
            }
        }

        //todo 推送任务到队列
        $order_info['order_payment_amount'] = $order_base['order_payment_amount'];
        $queue_data = [
            'action' => 'update_order_state',
            'timestamp' => getTime(),
            'order_id' => $order_id,
            'order_info' => $order_info,
            'order_items' => array_map(function ($item) {
                return [
                    'product_id' => $item['product_id'],
                    'item_id' => $item['item_id'],
                    'order_item_quantity' => $item['order_item_quantity'],
                    'order_item_inventory_lock' => $item['order_item_inventory_lock']
                ];
            }, $order_items)
        ];
        dispatch(new ProcessOrderJob($queue_data));
        Log::info("订单已推送到队列", ['order_id' => $order_id, 'queue' => 'high-priority']);

        return true;
    }


    /**
     * 更新订单状态
     * @param $order_id
     * @param $order_state_id
     * @param $next_order_state_id
     * @param $order_state_note
     * @return array|mixed
     * @throws ErrorException
     */
    public function editNextState($order_id, $order_state_id, $next_order_state_id, $order_state_note = '')
    {
        $order_info = $this->repository->getOne($order_id);

        //下一个状态存在
        if (StateCode::ORDER_STATE_CANCEL != $next_order_state_id) {
            $ss = $this->configBaseRepository->initOrderProcess();
            if (!in_array($next_order_state_id, $ss)) {
                throw new ErrorException(__("订单下个状态不符合配置要求！"));
            }
        }

        //todo 更新订单状态
        $flag = $this->orderBaseRepository->update(['order_state_id' => $next_order_state_id], $order_id);
        if (!$flag) {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        //订单信息更改
        $old_info = ['order_id' => $order_id, 'order_state_id' => $order_state_id];
        $new_info = ['order_state_id' => $next_order_state_id];

        switch ($order_state_id) {
            case StateCode::ORDER_STATE_WAIT_PAY:
                //new_info.OrderIsPaid = true //放入支付回调更改
                break;
            case StateCode::ORDER_STATE_WAIT_REVIEW:
                $old_info['order_is_review'] = false;
                $new_info['order_is_review'] = true;
                break;
            case StateCode::ORDER_STATE_WAIT_FINANCE_REVIEW:
                $old_info['order_finance_review'] = false;
                $new_info['order_finance_review'] = true;
                break;
            case StateCode::ORDER_STATE_PICKING:
                $new_info['order_is_out'] = StateCode::ORDER_PICKING_STATE_YES;
                break;
            case StateCode::ORDER_STATE_WAIT_SHIPPING:
                //发货完成状态已经修改，
                $new_info['order_is_shipped'] = StateCode::ORDER_SHIPPED_STATE_YES;
                break;
            case StateCode::ORDER_STATE_SHIPPED:
                $new_info['order_is_received'] = true;
                $new_info['order_received_time'] = getDateTime();
                break;
            default:
                break;
        }

        $flag = $this->repository->editWhere($old_info, $new_info);
        if (!$flag) {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        //todo 添加订单状态变更日志
        $user = User::getUser();
        if ($user) {
            $user_id = $user->user_id;
            $user_account = $user->user_account;
        } else {
            $user_id = $order_info['user_id'];
            $user_info = $this->userInfoRepository->getOne($user_id);
            if (!empty($user_info)) {
                $user_account = $user_info['user_account'];
            }
        }

        $order_state_log = [
            'order_id' => $order_id,
            'order_state_id' => $next_order_state_id,
            'order_state_pre_id' => $order_state_id,
            'user_id' => $user_id,
            'user_account' => $user_account ?? '',
            'order_state_note' => $order_state_note,
            'order_state_time' => getDateTime(),
        ];

        $flag = $this->orderStateLogRepository->add($order_state_log);

        return $flag;
    }


    /**
     * 判断是否有待处理的退货单
     * @param $order_id
     * @return bool
     * @throws ErrorException
     */
    public function checkOrderReturnWaiting($order_id = null)
    {
        $order_return = $this->orderReturnRepository->find(['order_id' => $order_id, 'return_state_id' => StateCode::RETURN_PROCESS_CHECK]);
        if (!empty($order_return)) {
            throw new ErrorException(__('有待处理的退款或者退货单，请先处理！'));
        }

        return true;
    }


    /**
     * 出库操作
     * @param $request
     * @return bool
     * @throws ErrorException
     */
    public function picking($request)
    {
        $order_id = $request['order_id'];
        if (!$order_id) {
            throw new ErrorException(__('没有符合条件的订单！'));
        }

        $this->checkOrderReturnWaiting($order_id); //售后订单检测

        $items = $request['items'];
        if ($items) {
            $items = json_decode($items, true);
            if (!empty($items) > 0) {
                $request['picking_flag'] = false;
            }
            $request['items'] = $items;
        }

        $order_info = $this->repository->getOne($order_id);
        if ($order_info['order_state_id'] == StateCode::ORDER_STATE_PICKING || $order_info['order_state_id'] == StateCode::ORDER_STATE_WAIT_SHIPPING) {
            DB::beginTransaction();

            //todo 1、出库生成出库单
            $state = $this->doReviewPicking($request);
            if ($state == StateCode::ORDER_PICKING_STATE_YES && $order_info['order_state_id'] == StateCode::ORDER_STATE_PICKING) {
                //todo 2、修改订单状态
                $next_order_state_id = $this->configBaseRepository->getNextOrderStateId($order_info['order_state_id']);
                $flag = $this->editNextState($order_id, $order_info['order_state_id'], $next_order_state_id, "");
                if (!$flag) {
                    throw new ErrorException(__('订单出库失败'));
                }
            }

            DB::commit();
        } else {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        return true;
    }


    /**
     * billItemQuantityAll
     * @param $order_id
     * @return array
     * @throws ErrorException
     */
    public function billItemQuantityAll($order_id = null)
    {
        $bill_item_quantity_all = [];

        //todo 获取订单商品
        $order_items = $this->orderItemRepository->find(['order_id' => $order_id]);
        if (empty($order_items)) {
            throw new ErrorException(__('订单商品获取失败！'));
        }

        //全部商品
        foreach ($order_items as $order_item) {
            $order_item_id = $order_item['order_item_id'];
            $bill_item_quantity_all[$order_item_id] = [
                'order_item_id' => $order_item_id,
                'item_id' => $order_item['item_id'],
                'bill_item_quantity' => $order_item['order_item_quantity'],
                'bill_item_price' => $order_item['order_item_sale_price'],
                'product_id' => $order_item['product_id'],
                'product_name' => $order_item['product_name'],
                'item_name' => $order_item['item_name']
            ];
        }

        //todo 获取已出库商品
        $bill_items = $this->stockBillService->getOutItems($order_id);

        //todo 扣除已经成功出库的数量
        foreach ($bill_items as $bill_item) {
            if (!empty($bill_item['order_item_id'])) {
                $order_item_id = $bill_item['order_item_id'];
                if (isset($bill_item_quantity_all[$order_item_id])) {
                    $bill_item_quantity_all[$order_item_id]['bill_item_quantity'] -= $bill_item['bill_item_quantity'];
                } else {
                    throw new ErrorException(__("出库数据有误") . $order_item_id);
                }
            }
        }

        return $bill_item_quantity_all;

    }


    /**
     * 执行出库操作
     * @param $request
     * @return int
     * @throws ErrorException
     */
    public function doReviewPicking($request)
    {
        $order_id = $request['order_id'];

        //todo 获取当前全部待出库商品
        $bill_item_quantity_all = $this->billItemQuantityAll($order_id);

        //todo 要出库商品
        $bill_item_waiting = [];

        //全部出库
        if ($request['picking_flag']) {
            $bill_item_waiting = $bill_item_quantity_all;
        } else {
            //todo 指定的出库商品及数量，需要判断是否符合bill_item_quantity_all中的要求。
            $items = $request['items'];
            foreach ($items as $item) {
                $order_item_id = $item['order_item_id'];
                if ($item['bill_item_quantity'] > $bill_item_quantity_all[$order_item_id]['bill_item_quantity']) {
                    $item['bill_item_quantity'] = $bill_item_quantity_all[$order_item_id]['bill_item_quantity'];
                }

                $bill_item_waiting[$order_item_id] = [
                    'order_item_id' => $order_item_id,
                    'item_id' => $item['item_id'],
                    'bill_item_quantity' => $item['bill_item_quantity'],
                    'bill_item_price' => $item['bill_item_price'],
                    'product_id' => $item['product_id'],
                    'product_name' => $bill_item_quantity_all[$order_item_id]['product_name'],
                    'item_name' => $bill_item_quantity_all[$order_item_id]['item_name']
                ];
            }
        }

        if (empty($bill_item_waiting)) {
            throw new ErrorException(__("无待出库出库数据") . $order_id);
        }

        //todo 1、生成出库单
        $stock_bill_data = $request;
        $stock_bill_data['bill_item_waiting'] = $bill_item_waiting;
        $stock_bill_data['bill_item_quantity_all'] = $bill_item_quantity_all;
        $bill_item_quantity_all = $this->stockBillService->addStockBill($stock_bill_data);

        //todo 2、判断是否已经全部出库， 需要修改订单状态
        $state = StateCode::ORDER_PICKING_STATE_YES;
        foreach ($bill_item_quantity_all as $picking_item) {
            if ($picking_item['bill_item_quantity'] > 0) {
                $state = StateCode::ORDER_PICKING_STATE_PART;
                break;
            }
        }

        //todo 3、修改订单出库状态
        $result = $this->repository->edit($order_id, ['order_is_out' => $state]);
        if (!$result) {
            throw new ErrorException(__('订单出库状态修改失败'));
        }

        return $state;
    }


    /**
     * todo 判断订单是否发货完成
     * @param $order_id
     * @return bool|int
     * @throws ErrorException
     */
    public function checkShippingComplete($order_id = null)
    {
        $order_info = $this->repository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__("订单不存在") . $order_id);
        }

        //检测是否发货完成
        $is_complete = true;

        //todo 获取物流记录
        $order_logistics = $this->orderLogisticsRepository->find(['order_id' => $order_id]);

        //todo 获取出库单
        $stock_bills = $this->stockBillService->findDetail([$order_id]);

        //检测是否有未发货的出库单
        $ids = array_column($order_logistics, 'stock_bill_id');
        foreach ($stock_bills as $bill) {
            if (!in_array($bill['stock_bill_id'], $ids)) {
                $is_complete = false;
                break;
            }
        }

        if ($is_complete) {
            //todo 订单全部出库商品
            $bill_item_quantity_all = $this->billItemQuantityAll($order_id);
            if (!empty($bill_item_quantity_all)) {
                foreach ($bill_item_quantity_all as $picking_item) {
                    if ($picking_item['bill_item_quantity'] > 0) {
                        $is_complete = false;
                        break;
                    }
                }

                //商品已经全部发货了
                if ($is_complete) {
                    if ($order_info['order_is_shipped'] !== StateCode::ORDER_SHIPPED_STATE_YES) {
                        $state = StateCode::ORDER_SHIPPED_STATE_YES;
                        //todo 1、修改订单发货状态
                        $order_info_result = $this->repository->edit($order_id, ['order_is_shipped' => $state]);
                        if (!$order_info_result) {
                            throw new ErrorException(__('发货状态修改失败！'));
                        }

                        //todo 修改订单状态
                        $next_order_state_id = $this->configBaseRepository->getNextOrderStateId($order_info['order_state_id']);
                        $order_state_result = $this->editNextState($order_id, $order_info['order_state_id'], $next_order_state_id);
                        if (!$order_state_result) {
                            throw new ErrorException(__('订单发货状态修改失败！'));
                        }

                        return $state;
                    }
                }
            }
        }

        return true;
    }


    /**
     * 订单确认收货
     * @param $order_id
     * @param $order_state_note
     * @return true
     * @throws ErrorException
     */
    public function receive($order_id = null, $order_state_note = '')
    {
        $order_info = $this->repository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__("订单不存在") . $order_id);
        }

        if ($order_info['order_state_id'] == StateCode::ORDER_STATE_SHIPPED) {
            //todo 修改订单状态
            $next_order_state_id = $this->configBaseRepository->getNextOrderStateId($order_info['order_state_id']);
            $order_state_result = $this->editNextState($order_id, $order_info['order_state_id'], $next_order_state_id, $order_state_note);
            if (!$order_state_result) {
                throw new ErrorException(__('订单状态修改失败！'));
            }
        } else {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        return true;
    }


    /**
     * 取消订单
     * @return bool
     * @throws ErrorException
     */
    public function cancel($order_id = null, $order_state_note = '')
    {
        $order_info = $this->repository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__("订单不存在") . $order_id);
        }

        if ($order_info['order_is_shipped'] != StateCode::ORDER_SHIPPED_STATE_NO) {
            throw new ErrorException(__("订单部分发货，不可取消，请联系商家！"));
        }

        $order_items = $this->orderItemRepository->find(['order_id' => $order_id]);
        if (empty($order_items)) {
            throw new ErrorException(__('订单商品为空'));
        }

        DB::beginTransaction();

        //当前订单是否可取消判断
        if ($this->ifCancel($order_info['order_state_id'])) {

            $order_data = $this->orderDataRepository->getOne($order_id);
            //todo 当前订单已经支付 生成退款单
            $return_row = [
                'order_id' => $order_id,
                'store_id' => $order_info['store_id'],
                'buyer_user_id' => $order_info['user_id'],
                'buyer_store_id' => 0,
                'return_add_time' => getTime(),
                'return_reason_id' => 0,
                'return_buyer_message' => '',
                'return_tel' => '',
                'return_state_id' => StateCode::RETURN_PROCESS_CHECK,
                'return_is_paid' => 0,
                'return_is_shipping_fee' => 0,
                'return_shipping_fee' => $order_data['order_shipping_fee'],
                'return_flag' => StateCode::ORDER_NOT_NEED_RETURN_GOODS,
                'return_type' => 1,
                'return_commision_fee' => 0
            ];
            if ($order_info['order_is_paid'] == StateCode::ORDER_PAID_STATE_YES) {
                $orderReturnService = app(OrderReturnService::class);
                $return_item_rows = [];
                foreach ($order_items as $order_item) {
                    $return_item_row = $order_item;
                    $order_item_can_refund_amount = $order_item['order_item_payment_amount'];
                    $return_item_row['return_item_subtotal'] = $order_item_can_refund_amount - $order_item['order_item_return_subtotal'];
                    $return_item_row['return_item_num'] = $order_item['order_item_quantity'] - $order_item['order_item_return_num'];
                    $return_item_row['return_reason_id'] = 0;
                    $return_item_row['return_item_note'] = '';
                    $return_item_row['return_item_image'] = '';
                    $return_item_row['return_state_id'] = StateCode::RETURN_PROCESS_CHECK;
                    $return_item_rows[] = $return_item_row;
                }
                $return_data = $orderReturnService->addReturnByItem($return_row, $return_item_rows);
                $orderReturnService->review($return_data['return_id']);
            }

            //todo 修改订单状态
            $next_order_state_id = StateCode::ORDER_STATE_CANCEL;
            $order_state_result = $this->editNextState($order_id, $order_info['order_state_id'], $next_order_state_id, $order_state_note);
            if (!$order_state_result) {
                throw new ErrorException(__('订单状态修改失败！'));
            }
        } else {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        //todo 释放商品库存
        $flag = $this->releaseItemsQuantity($order_items, $order_info);
        if (!$flag) {
            DB::rollBack();
            throw new ErrorException(__('更新商品库存失败！'));
        }

        DB::commit();

        return true;
    }


    /**
     * 订单是否可以取消
     *
     * @param int $order_state_id
     * @return bool
     */
    private function ifCancel(int $order_state_id)
    {
        $orderStates = [
            StateCode::ORDER_STATE_WAIT_PAY,
            StateCode::ORDER_STATE_WAIT_REVIEW,
            StateCode::ORDER_STATE_WAIT_FINANCE_REVIEW,
            StateCode::ORDER_STATE_PICKING,
            StateCode::ORDER_STATE_WAIT_SHIPPING
        ];

        return in_array($order_state_id, $orderStates);
    }


    /**
     * 订单审核
     * @return bool
     * @throws ErrorException
     */
    public function review($order_id = null)
    {
        $order_info = $this->repository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__("订单不存在") . $order_id);
        }

        if ($order_info['order_state_id'] == StateCode::ORDER_STATE_WAIT_REVIEW) {
            // todo 获取订单的下一条状态
            $next_order_state_id = $this->configBaseRepository->getNextOrderStateId($order_info['order_state_id']);
            $order_state_result = $this->editNextState($order_id, $order_info['order_state_id'], $next_order_state_id);
            if (!$order_state_result) {
                throw new ErrorException(__('订单状态修改失败！'));
            }
        } else {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        return true;
    }


    /**
     * 财务审核
     * @return bool
     * @throws ErrorException
     */
    public function finance($order_id = null)
    {
        $order_info = $this->repository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__("订单不存在") . $order_id);
        }

        if ($order_info['order_state_id'] == StateCode::ORDER_STATE_WAIT_FINANCE_REVIEW) {
            // todo 获取订单的下一条状态
            $next_order_state_id = $this->configBaseRepository->getNextOrderStateId($order_info['order_state_id']);
            $order_state_result = $this->editNextState($order_id, $order_info['order_state_id'], $next_order_state_id);
            if (!$order_state_result) {
                throw new ErrorException(__('订单状态修改失败！'));
            }
        } else {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        return true;
    }


    /**
     * 是否可以发货
     * @param $order_state_id
     * @return bool
     */
    private function ifShipping($order_state_id)
    {
        return $order_state_id === StateCode::ORDER_STATE_WAIT_SHIPPING || $order_state_id === StateCode::ORDER_STATE_PICKING;
    }


    /**
     * 直接执行发货操作
     * @return bool
     * @throws ErrorException
     */
    public function shipping($request)
    {
        $order_id = $request->get('order_id');
        $order_info = $this->repository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__("订单不存在") . $order_id);
        }

        if ($this->ifShipping($order_info['order_state_id'])) {

            $state = $this->doReviewShipping($request);
            if ($state == StateCode::ORDER_SHIPPED_STATE_YES) {
                // todo 获取订单的下一条状态
                $next_order_state_id = $this->configBaseRepository->getNextOrderStateId(StateCode::ORDER_STATE_WAIT_SHIPPING);

                $order_state_result = $this->editNextState($order_id, $order_info['order_state_id'], $next_order_state_id);
                if (!$order_state_result) {
                    throw new ErrorException(__('订单状态修改失败！'));
                }
            }
        } else {
            throw new ErrorException(__('未更改到符合条件的订单！'));
        }

        return true;
    }


    public function doReviewShipping($request)
    {
        $order_id = $request->get('order_id');
        $order_info = $this->repository->getOne($order_id);

        //todo 如果订单不是出库状态，执行出库操作
        if ($order_info['order_is_out'] != StateCode::ORDER_PICKING_STATE_YES) {
            $request['picking_flag'] = true;
            $this->doReviewPicking($request);
        }

        $stock_bills = $this->stockBillService->findDetail([$order_id]);
        if (empty($stock_bills)) {
            throw new ErrorException(__('订单出库有误！'));
        }

        //发货物流
        $order_logistics = $this->orderLogisticsRepository->find(['order_id' => $order_id]);
        $logistics_ids = []; //已经发货的出库单据
        if (!empty($order_logistics)) {
            $logistics_ids = array_column($order_logistics, 'stock_bill_id');
        }

        foreach ($stock_bills as $bill) {
            if (!in_array($bill['stock_bill_id'], $logistics_ids) || empty($logistics_ids)) {
                // 创建发货信息
                $store_logistics_id = $request['logistics_id'];
                $store_logistics_row = $this->storeExpressLogisticsRepository->getOne($store_logistics_id);
                $logistics_row = [
                    'order_id' => $order_id,   //订单编号
                    'stock_bill_id' => $bill['stock_bill_id'],   //出库单号
                    'order_tracking_number' => $request->input('order_tracking_number', ''), //订单物流单号
                    'logistics_id' => $store_logistics_id,   //对应快递公司
                    'ss_id' => $request['ss_id'],     //发货地址编号
                    'logistics_explain' => $request->input('logistics_explain', ''), //发货备注
                    'logistics_time' => $request->input('logistics_time'),
                    'express_name' => $store_logistics_row['express_name'],
                    'express_id' => $store_logistics_row['express_id'],
                    'logistics_phone' => $store_logistics_row['logistics_intl'] . $store_logistics_row['logistics_mobile'],
                    'logistics_mobile' => $store_logistics_row['logistics_intl'] . $store_logistics_row['logistics_mobile'],
                    'logistics_contacter' => $store_logistics_row['logistics_contacter'],
                    'logistics_address' => $store_logistics_row['logistics_address']
                ];

                $flag = $this->orderLogisticsRepository->add($logistics_row);
                if (!$flag) {
                    throw new ErrorException(__('发货信息错误'));
                }
            }
        }
        $this->checkShippingComplete($order_id);

        $state = StateCode::ORDER_SHIPPED_STATE_YES;

        return $state;
    }


    /**
     * 根据用户id获取用户订单统计信息
     */
    public function getOrderStatisticsInfo($user_id)
    {
        $order_num_res = [];

        // 完成订单数 取消订单数
        $order_num_cond = [
            'user_id' => $user_id,
            'order_state_id' => StateCode::ORDER_STATE_FINISH,
        ];

        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_ENTITY;
        $order_num_res['fin_num_entity'] = $this->getOrderNum($order_num_cond);

        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_FUWU;
        $order_num_res['fin_num_v'] = $this->getOrderNum($order_num_cond);

        // 取消订单数
        $order_num_cond['order_state_id'] = StateCode::ORDER_STATE_CANCEL;
        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_ENTITY;
        $order_num_res['cancel_num_entity'] = $this->getOrderNum($order_num_cond);

        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_FUWU;
        $order_num_res['cancel_num_v'] = $this->getOrderNum($order_num_cond);

        // 等待发货订单数 和 已发货订单数
        $order_num_cond = [
            'user_id' => $user_id,
        ];

        // 待发货订单数
        $order_num_cond['order_state_id'] = StateCode::ORDER_STATE_PICKING;
        $order_picking_num = $this->getOrderNum($order_num_cond);

        $order_num_cond['order_state_id'] = StateCode::ORDER_STATE_WAIT_SHIPPING;
        $order_shipping_num = $this->getOrderNum($order_num_cond);
        $order_num_res['wait_shipping_num_entity'] = $order_picking_num + $order_shipping_num;

        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_FUWU;
        $order_picking_num = $this->getOrderNum($order_num_cond);

        $order_num_cond['order_state_id'] = StateCode::ORDER_STATE_WAIT_SHIPPING;
        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_FUWU;
        $order_shipping_num = $this->getOrderNum($order_num_cond);
        $order_num_res['wait_shipping_num_v'] = $order_picking_num + $order_shipping_num;

        // 已发货订单数
        $order_num_cond['order_state_id'] = StateCode::ORDER_STATE_SHIPPED;
        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_ENTITY;
        $order_num_res['ship_num_entity'] = $this->getOrderNum($order_num_cond);

        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_FUWU;
        $order_num_res['ship_num_v'] = $this->getOrderNum($order_num_cond);

        // 等待支付订单数 和 正在退货订单数
        $order_num_cond = [
            'user_id' => $user_id,
            'order_state_id' => StateCode::ORDER_STATE_WAIT_PAY,
        ];

        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_ENTITY;
        $order_num_res['wait_pay_num_entity'] = $this->getOrderNum($order_num_cond);

        $order_num_cond['kind_id'] = StateCode::PRODUCT_KIND_FUWU;
        $order_num_res['wait_pay_num_v'] = $this->getOrderNum($order_num_cond);

        $returning_num = $this->calculateReturningNum($user_id);
        $order_num_res['returning_num'] = $returning_num;

        return $order_num_res;
    }


    /**
     * 根据条件查询数量
     * @param $in
     * @return mixed
     */
    public function getOrderNum($in)
    {
        $criteria = [];

        if (isset($in['order_state_id']) && $in['order_state_id']) {
            $criteria['order_state_id'] = $in['order_state_id'];
        }

        if (isset($in['user_id']) && $in['user_id']) {
            $criteria['user_id'] = $in['user_id'];
        }

        if (isset($in['kind_id']) && $in['kind_id']) {
            $criteria['kind_id'] = $in['kind_id'];
        }

        if (isset($in['order_stime']) && $in['order_stime']) {
            $criteria[] = ['create_time', '>=', $in['order_stime']];
        }

        if (isset($in['order_etime']) && $in['order_etime']) {
            $criteria[] = ['create_time', '<=', $in['order_etime']];
        }

        return $this->repository->getNum($criteria);
    }


    /**
     * 用户未完成的退单数
     */
    protected function calculateReturningNum($user_id)
    {
        return $this->orderReturnRepository->getNum([
            ['buyer_user_id', '=', $user_id],
            ['return_state_id', 'IN', [
                StateCode::RETURN_PROCESS_SUBMIT,
                StateCode::RETURN_PROCESS_CHECK,
                StateCode::RETURN_PROCESS_RECEIVED,
                StateCode::RETURN_PROCESS_REFUND
            ]]
        ]);
    }


    /**
     * 检验用户操作订单权限
     * @param $user_id
     * @param $order_id
     * @return true
     * @throws ErrorException
     */
    public function checkUserOrder($user_id = null, $order_id = null)
    {
        $order_info = $this->repository->getOne($order_id);
        if (!empty($order_info)) {
            if ($user_id != $order_info['user_id']) {
                throw new ErrorException(__('无操作该订单权限'));
            }

            return true;
        } else {
            throw new ErrorException(__('订单不存在'));
        }
    }


    /**
     * 释放订单商品库存
     * @param $order_items
     * @param $order_info
     * @return bool
     */
    public function releaseItemsQuantity($order_items, $order_info = [])
    {
        $flag = true;
        $order_id = $order_info['order_id'];
        $return_bill_items = [];

        if ($order_info['order_is_out'] == StateCode::ORDER_PICKING_STATE_NO) {
            //todo 情况 1、订单商品均未出库
            foreach ($order_items as $order_item) {

                // 如果订单未支付且未锁定库存，跳过处理
                if ($order_info['order_is_paid'] != StateCode::ORDER_PAID_STATE_YES && $order_item['order_item_inventory_lock'] != 1001) {
                    continue;
                }

                // 减少冻结库存
                $item_id = $order_item['item_id'];
                $quantity_to_restore = $order_item['order_item_quantity'];
                $product_item_result = $this->productItemRepository->decrementFieldByIds(
                    [$item_id],
                    'item_quantity_frozen',
                    $quantity_to_restore
                );
                if (!$product_item_result) {
                    return false;
                }
            }
        } elseif ($order_info['order_is_out'] == StateCode::ORDER_PICKING_STATE_PART) {
            //todo 情况2、订单商品部分出库库存处理

            //订单商品出库信息
            $stock_bill_items = $this->stockBillService->getOutItems($order_id);
            $order_out_items = [];
            if (!empty($stock_bill_items)) {
                foreach ($stock_bill_items as $stock_bill_item) {
                    $order_item_id = $stock_bill_item['order_item_id'];
                    if (isset($order_out_items[$order_item_id])) {
                        $order_out_items[$order_item_id] += $stock_bill_item['bill_item_quantity'];
                    } else {
                        $order_out_items[$order_item_id] = $stock_bill_item['bill_item_quantity'];
                    }
                }
            }

            //处理商品库存
            foreach ($order_items as $k => $order_item) {
                $order_item_id = $order_item['order_item_id'];
                $remaining_quantity = $order_item['order_item_quantity'];
                if (isset($order_out_items[$order_item_id])) {
                    //返回商品库存
                    $out_quantity = $order_out_items[$order_item_id];
                    $product_item_result = $this->productItemRepository->incrementFieldByIds([$order_item['item_id']], 'item_quantity', $out_quantity);
                    if (!$product_item_result) {
                        return false;
                    }
                    $remaining_quantity -= $out_quantity;

                    $return_bill_items[] = [
                        'bill_type_id' => StateCode::BILL_TYPE_IN,
                        'stock_transport_type_id' => StateCode::STOCK_IN_RETURN,
                        'product_id' => $order_item['product_id'],
                        'item_id' => $order_item['item_id'],
                        'product_name' => $order_item['product_name'],
                        'item_name' => $order_item['item_name'],
                        'bill_item_quantity' => $out_quantity,
                        'warehouse_item_quantity' => 0,
                        'bill_item_unit_price' => $order_item['item_unit_price'],
                        'bill_item_subtotal' => $order_item['item_unit_price'] * $out_quantity
                    ];
                }

                //返回冻结库存
                if ($remaining_quantity > 0) {
                    $product_item_result = $this->productItemRepository->decrementFieldByIds([$order_item['item_id']], 'item_quantity_frozen', $remaining_quantity);
                    if (!$product_item_result) {
                        return false;
                    }
                }
            }
        } elseif ($order_info['order_is_out'] == StateCode::ORDER_PICKING_STATE_YES) {
            //todo 订单商品全部出库 增加商品库存
            foreach ($order_items as $order_item) {
                $product_item_result = $this->productItemRepository->incrementFieldByIds([$order_item['item_id']], 'item_quantity', $order_item['order_item_quantity']);
                if (!$product_item_result) {
                    return false;
                }

                $out_quantity = $order_item['order_item_quantity'];
                $return_bill_items[] = [
                    'bill_type_id' => StateCode::BILL_TYPE_IN,
                    'stock_transport_type_id' => StateCode::STOCK_IN_RETURN,
                    'order_id' => $order_id,
                    'product_id' => $order_item['product_id'],
                    'item_id' => $order_item['item_id'],
                    'product_name' => $order_item['product_name'],
                    'item_name' => $order_item['item_name'],
                    'bill_item_quantity' => $out_quantity,
                    'warehouse_item_quantity' => 0,
                    'bill_item_unit_price' => $order_item['item_unit_price'],
                    'bill_item_subtotal' => $order_item['item_unit_price'] * $out_quantity
                ];
            }
        }

        if (!empty($return_bill_items)) {
            $flag = $this->stockBillService->addBillItems($return_bill_items);
        }

        return $flag;
    }


    /**
     * 是否可以退货
     * @param $order_state_id
     * @param $order_is_paid
     * @return bool
     */
    private function ifReturn($order_state_id, $order_is_paid)
    {
        $order_state_row = [
            StateCode::ORDER_STATE_SHIPPED,
            StateCode::ORDER_STATE_RECEIVED,
            StateCode::ORDER_STATE_FINISH
        ];

        return in_array($order_state_id, $order_state_row) && $order_is_paid === StateCode::ORDER_PAID_STATE_YES;
    }


    /**
     * 申请开票
     * @param $request
     * @return array
     * @throws ErrorException
     */
    public function addOrderInvoice($request)
    {
        $order_id = $request->input('order_id', '');
        $order_info = $this->repository->getOne($order_id);
        if (empty($order_info)) {
            throw new ErrorException(__("订单信息不存在！"));
        }

        $order_base = $this->orderBaseRepository->getOne($order_id);
        if (empty($order_base)) {
            throw new ErrorException(__("订单信息不存在！"));
        }

        $user_invoice_id = $request->input('user_invoice_id', -1);
        $user_invoice = $this->userInvoiceRepository->getOne($user_invoice_id);
        if (empty($user_invoice)) {
            throw new ErrorException(__("用户发票不存在！"));
        }

        $invoice_amount = $order_base['order_payment_amount'];
        $order_invoice_data = [
            'order_id' => $order_id,
            'user_id' => $request['user_id'],
            'store_id' => $order_info['store_id'],
            'chain_id' => $order_info['chain_id'],
            'invoice_title' => $user_invoice['invoice_title'],
            'invoice_content' => $order_info['order_title'],
            'invoice_amount' => $invoice_amount,
            'invoice_company_code' => $user_invoice['invoice_company_code'],
            'invoice_is_company' => $user_invoice['invoice_is_company'],
            'invoice_is_electronic' => $user_invoice['invoice_is_electronic'],
            'invoice_type' => $user_invoice['invoice_type'],
            'invoice_status' => false,
            'invoice_address' => $user_invoice['invoice_address'],
            'invoice_phone' => $user_invoice['invoice_phone'],
            'invoice_bankname' => $user_invoice['invoice_bankname'],
            'invoice_bankaccount' => $user_invoice['invoice_bankaccount'],
            'invoice_contact_name' => $user_invoice['invoice_contact_name'],
            'invoice_contact_area' => $user_invoice['invoice_contact_area'],
            'invoice_contact_address' => $user_invoice['invoice_contact_address'],
            'user_intl' => $request->input('user_intl', '+86'),
            'user_mobile' => $user_invoice['invoice_contact_mobile'],
            'user_email' => $user_invoice['invoice_contact_email'],
            'order_is_paid' => true,
            'invoice_time' => getTime(), //创建时间
            'invoice_cancel' => false,
            'subsite_id' => $request->input('subsite_id', 0)
        ];

        try {
            $this->orderInvoiceRepository->add($order_invoice_data);

            return $order_invoice_data;
        } catch (\Exception $e) {
            throw new ErrorException(__('发票保存失败: ') . $e->getMessage());
        }
    }


}
